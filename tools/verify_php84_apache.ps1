param(
    [string]$ApacheRoot = 'C:\xampp2\apache',
    [string]$PhpRuntime = ''
)
$ErrorActionPreference = 'Stop'
$project = Split-Path -Parent $PSScriptRoot
if ($PhpRuntime -eq '') { $PhpRuntime = Join-Path $project 'storage/backups/php84-apache/runtime' }
$runtime = (Resolve-Path -LiteralPath $PhpRuntime).Path
$apache = Join-Path $ApacheRoot 'bin/httpd.exe'
if (!(Test-Path -LiteralPath (Join-Path $runtime 'php8apache2_4.dll'))) { throw 'Falta el módulo PHP Thread Safe.' }
$suffix = [Guid]::NewGuid().ToString('N')
$work = Join-Path $project ('storage/backups/apache84-probe-' + $suffix)
$public = Join-Path $work 'public/PGCV'
New-Item -ItemType Directory -Path $public,(Join-Path $work 'sessions') -Force | Out-Null
$utf8 = New-Object System.Text.UTF8Encoding($false)
$slash = { param($path) $path.Replace('\','/') }
$write = { param($path,$text) [IO.File]::WriteAllText($path, $text.Replace("`r`n","`n"), $utf8) }
$checks = 0
$check = { param($ok,$label) if (!$ok) { throw ('Falló: ' + $label) }; $script:checks++ }
$ini = Join-Path $work 'php.ini'
$extensions = & $slash (Join-Path $runtime 'ext')
$sessions = & $slash (Join-Path $work 'sessions')
$phpLog = Join-Path $work 'php-errors.log'
& $write $ini (@"
[PHP]
extension_dir="$extensions"
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sqlite3
date.timezone=America/Bogota
error_reporting=E_ALL
display_errors=Off
log_errors=On
error_log="$(& $slash $phpLog)"
session.save_path="$sessions"
memory_limit=256M
"@)
Copy-Item -LiteralPath (Join-Path $project 'tests/fixtures/php84_apache_probe.php') -Destination (Join-Path $public 'probe.php')
Copy-Item -LiteralPath (Join-Path $project '.htaccess') -Destination (Join-Path $public '.htaccess')
foreach ($dir in @('.git','migrations','storage')) { New-Item -ItemType Directory -Path (Join-Path $public $dir) | Out-Null }
& $write (Join-Path $public '.git/HEAD') 'fictitious repository marker'
& $write (Join-Path $public 'migrations/example.md') 'fictitious migration document'
Copy-Item -LiteralPath (Join-Path $project 'migrations/.htaccess') -Destination (Join-Path $public 'migrations/.htaccess')
Copy-Item -LiteralPath (Join-Path $project 'storage/.htaccess') -Destination (Join-Path $public 'storage/.htaccess')
$listener = New-Object Net.Sockets.TcpListener([Net.IPAddress]::Loopback, 0)
$listener.Start(); $port = $listener.LocalEndpoint.Port; $listener.Stop()
$conf = Join-Path $work 'httpd.conf'
$serverRoot = & $slash $ApacheRoot
$runtimePath = & $slash $runtime
$publicRoot = & $slash (Join-Path $work 'public')
$apacheLog = Join-Path $work 'apache-errors.log'
& $write $conf (@"
ServerRoot "$serverRoot"
Listen 127.0.0.1:$port
ServerName 127.0.0.1
PidFile "$(& $slash (Join-Path $work 'httpd.pid'))"
ErrorLog "$(& $slash $apacheLog)"
LogLevel warn
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule access_compat_module modules/mod_access_compat.so
LoadModule dir_module modules/mod_dir.so
LoadModule mime_module modules/mod_mime.so
LoadModule env_module modules/mod_env.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadFile "$runtimePath/php8ts.dll"
LoadFile "$runtimePath/libsqlite3.dll"
LoadFile "$runtimePath/libcrypto-3-x64.dll"
LoadFile "$runtimePath/libssl-3-x64.dll"
LoadFile "$runtimePath/libssh2.dll"
LoadFile "$runtimePath/nghttp2.dll"
LoadModule php_module "$runtimePath/php8apache2_4.dll"
PHPIniDir "$(& $slash $work)"
SetEnv PGCV_TEST_PROJECT "$(& $slash $project)"
DocumentRoot "$publicRoot"
TypesConfig "$serverRoot/conf/mime.types"
<Directory />
    AllowOverride None
    Require all denied
</Directory>
<Directory "$publicRoot">
    AllowOverride All
    Require local
</Directory>
<FilesMatch "\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>
"@)
$mainConfigs = @((Join-Path $ApacheRoot 'conf/httpd.conf'),(Join-Path $ApacheRoot 'conf/extra/httpd-xampp.conf'))
$before = @($mainConfigs | ForEach-Object { (Get-FileHash -LiteralPath $_ -Algorithm SHA256).Hash })
$server = $null
$oldPath = $env:PATH
try {
    $env:PATH = $runtime + ';' + $oldPath
    # Apache writes even "Syntax OK" to stderr; PowerShell 5 must not turn
    # that successful native diagnostic into a terminating exception.
    $savedPreference = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    $syntax = & $apache -t -f $conf 2>&1
    $syntaxExit = $LASTEXITCODE
    $ErrorActionPreference = $savedPreference
    & $check ($syntaxExit -eq 0) ('Apache configuration: ' + ($syntax -join ' '))
    $server = Start-Process -FilePath $apache -ArgumentList @('-X','-f',('"' + $conf + '"')) -WorkingDirectory $runtime -WindowStyle Hidden -RedirectStandardOutput (Join-Path $work 'stdout.log') -RedirectStandardError (Join-Path $work 'stderr.log') -PassThru
    $url = 'http://127.0.0.1:' + $port + '/PGCV/probe.php'
    $deadline = (Get-Date).AddSeconds(15)
    do {
        try { $response = Invoke-WebRequest -Uri $url -UseBasicParsing -SessionVariable probeSession -TimeoutSec 2; break }
        catch { if ($server.HasExited) { throw 'Apache de prueba se cerró; revisa sus logs protegidos.' }; Start-Sleep -Milliseconds 100 }
    } while ((Get-Date) -lt $deadline)
    & $check ($null -ne $response -and $response.StatusCode -eq 200) 'HTTP probe available'
    $data = $response.Content | ConvertFrom-Json
    & $check ($data.php_version -eq '8.4.26' -and $data.sapi -eq 'apache2handler' -and $data.thread_safe) 'PHP 8.4 TS loaded as Apache module'
    & $check ($data.ini.Replace('\','/') -eq $ini.Replace('\','/')) 'isolated php.ini selected'
    & $check (@($data.required_extensions | Where-Object { !$_ }).Count -eq 0) 'required extensions'
    & $check ($data.orphan_count -eq 0 -and $data.missing_constraints -eq 0) 'read-only database integrity'
    & $check ($data.png_ok -and $data.pdf_ok) 'image and fictitious PDF generated in memory'
    & $check ($data.smtp_time_limit -eq 15 -and $data.tls_identity_required) 'mailer configured without connecting or sending'
    $second = (Invoke-WebRequest -Uri $url -UseBasicParsing -WebSession $probeSession -TimeoutSec 10).Content | ConvertFrom-Json
    & $check ($data.session_count -eq 1 -and $second.session_count -eq 2) 'isolated session cookie retained'
    foreach ($path in @('.git/HEAD','migrations/example.md')) {
        $status = 0
        try { $status = [int](Invoke-WebRequest -Uri ('http://127.0.0.1:' + $port + '/PGCV/' + $path) -UseBasicParsing -TimeoutSec 5).StatusCode }
        catch { if ($_.Exception.Response) { $status = [int]$_.Exception.Response.StatusCode } else { throw } }
        & $check ($status -eq 403) ('private path denied: ' + $path)
    }
    & $write (Join-Path $public 'storage/maintenance.lock') 'isolated gate'
    $status = 0
    try { $status = [int](Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 5).StatusCode }
    catch { if ($_.Exception.Response) { $status = [int]$_.Exception.Response.StatusCode } else { throw } }
    & $check ($status -eq 503) 'Apache maintenance gate'
    & $check (!(Test-Path -LiteralPath $phpLog) -or [string]::IsNullOrWhiteSpace([IO.File]::ReadAllText($phpLog))) 'no PHP errors or deprecations'
    $after = @($mainConfigs | ForEach-Object { (Get-FileHash -LiteralPath $_ -Algorithm SHA256).Hash })
    & $check (($before -join ',') -eq ($after -join ',')) 'main Apache configurations unchanged'
    & $write (Join-Path $work 'verification.json') (@{checked_at=(Get-Date).ToString('o');php=$data.php_version;sapi=$data.sapi;checks=$checks;source_writes=$false;real_email=$false} | ConvertTo-Json)
    Write-Output "Apache/PHP 8.4: $checks comprobaciones correctas. Evidencia local protegida."
} finally {
    if ($null -ne $server) {
        $server.Refresh()
        if (!$server.HasExited) { Stop-Process -Id $server.Id; $server.WaitForExit(10000) | Out-Null }
    }
    $env:PATH = $oldPath
}
