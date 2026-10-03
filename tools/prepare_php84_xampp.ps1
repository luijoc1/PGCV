param(
    [string]$ApacheRoot = 'C:\xampp2\apache',
    [string]$PhpRuntime = '',
    [string]$PhpMyAdminRuntime = ''
)
# Prepare and exercise a private copy; never install it or stop the main Apache.
$ErrorActionPreference = 'Stop'
$project = Split-Path -Parent $PSScriptRoot
if ($PhpRuntime -eq '') { $PhpRuntime = Join-Path $project 'storage/backups/php84-apache/runtime' }
$runtime = (Resolve-Path -LiteralPath $PhpRuntime).Path
$apacheRootPath = (Resolve-Path -LiteralPath $ApacheRoot).Path
$apache = Join-Path $apacheRootPath 'bin/httpd.exe'
if (!(Test-Path -LiteralPath (Join-Path $runtime 'php8apache2_4.dll'))) { throw 'Falta el módulo PHP Thread Safe.' }
$work = Join-Path $project ('storage/backups/php84-xampp-' + [Guid]::NewGuid().ToString('N'))
$original = Join-Path $work 'original'
$testConf = Join-Path $work 'test-conf'
$candidate = Join-Path $work 'candidate'
$iniDir = Join-Path $candidate 'ini'
$public = Join-Path $work 'probe'
New-Item -ItemType Directory -Path $original,$testConf,$iniDir,$public,(Join-Path $work 'sessions'),(Join-Path $work 'logs') -Force | Out-Null
$utf8 = New-Object System.Text.UTF8Encoding($false)
function Slash($path) { $path.Replace('\','/') }
function Write-Utf8($path, $value) { [IO.File]::WriteAllText($path, $value.Replace("`r`n","`n"), $utf8) }
function Free-Port {
    $listener = New-Object Net.Sockets.TcpListener([Net.IPAddress]::Loopback, 0)
    $listener.Start()
    try { $listener.LocalEndpoint.Port } finally { $listener.Stop() }
}
function Fetch($url, $timeoutSeconds = 10) {
    try {
        $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec $timeoutSeconds
        @{status=[int]$response.StatusCode; content=$response.Content}
    } catch {
        if (!$_.Exception.Response) { throw }
        $reader = New-Object IO.StreamReader($_.Exception.Response.GetResponseStream())
        try { @{status=[int]$_.Exception.Response.StatusCode; content=$reader.ReadToEnd()} } finally { $reader.Dispose() }
    }
}
$confRoot = Join-Path $apacheRootPath 'conf'
$hashes = @{}
# Copy only configuration text, not private TLS keys. Relative certificate and
# module paths still resolve against the original, read-only ServerRoot.
foreach ($file in Get-ChildItem -LiteralPath $confRoot -Recurse -File -Filter '*.conf') {
    $relative = $file.FullName.Substring($confRoot.Length + 1)
    $hashes[$relative] = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash
    foreach ($target in @($original,$testConf)) {
        $destination = Join-Path $target $relative
        New-Item -ItemType Directory -Path (Split-Path -Parent $destination) -Force | Out-Null
        Copy-Item -LiteralPath $file.FullName -Destination $destination
    }
}
$originalIni = Join-Path (Split-Path -Parent $apacheRootPath) 'php/php.ini'
$originalIniHash = (Get-FileHash -LiteralPath $originalIni -Algorithm SHA256).Hash
Copy-Item -LiteralPath $originalIni -Destination (Join-Path $original 'php74.ini')
$phpLog = Join-Path $work 'logs/php-errors.log'
$runtimePath = Slash $runtime
$caFile = Slash (Join-Path $apacheRootPath 'bin/curl-ca-bundle.crt')
Write-Utf8 (Join-Path $iniDir 'php.ini') (@"
[PHP]
extension_dir="$runtimePath/ext"
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sqlite3
extension=zip
date.timezone=America/Bogota
error_reporting=E_ALL
display_errors=Off
log_errors=On
error_log="$(Slash $phpLog)"
session.save_path="$(Slash (Join-Path $work 'sessions'))"
memory_limit=512M
max_execution_time=120
post_max_size=40M
upload_max_filesize=40M
curl.cainfo="$caFile"
openssl.cafile="$caFile"
"@)
$xamppSource = [IO.File]::ReadAllText((Join-Path $original 'extra/httpd-xampp.conf'))
$modulePattern = '(?m)^LoadFile "[^"\r\n]+/php7ts\.dll"\r?\nLoadFile "[^"\r\n]+/libpq\.dll"\r?\nLoadFile "[^"\r\n]+/libsqlite3\.dll"\r?\nLoadModule php7_module "[^"\r\n]+/php7apache2_4\.dll"'
if ([regex]::Matches($xamppSource,$modulePattern).Count -ne 1) { throw 'La configuración del módulo PHP no coincide con la revisada; no se genera un reemplazo.' }
$module = @"
LoadFile "$runtimePath/php8ts.dll"
LoadFile "$runtimePath/libsqlite3.dll"
LoadFile "$runtimePath/libcrypto-3-x64.dll"
LoadFile "$runtimePath/libssl-3-x64.dll"
LoadFile "$runtimePath/libssh2.dll"
LoadFile "$runtimePath/nghttp2.dll"
LoadFile "$runtimePath/brotlicommon.dll"
LoadFile "$runtimePath/brotlidec.dll"
LoadModule php_module "$runtimePath/php8apache2_4.dll"
"@
$replacement = [regex]::Replace($xamppSource,$modulePattern,$module)
$replacement = [regex]::Replace($replacement,'(?m)^\s*SetEnv PHPRC .*$',('    SetEnv PHPRC "' + (Slash $iniDir) + '"'))
$iniPattern = '(?ms)^<IfModule php7_module>\s*PHPINIDir "[^"\r\n]+"\s*</IfModule>'
if ([regex]::Matches($replacement,$iniPattern).Count -ne 1) { throw 'No se encontró un único PHPINIDir.' }
$replacement = [regex]::Replace($replacement,$iniPattern,('PHPIniDir "' + (Slash $iniDir) + '"'))
if ($PhpMyAdminRuntime -ne '') {
    $pmaRuntime = (Resolve-Path -LiteralPath $PhpMyAdminRuntime).Path
    if (!(Test-Path -LiteralPath (Join-Path $pmaRuntime 'index.php'))) { throw 'Falta index.php de phpMyAdmin.' }
    $oldPma = Slash (Join-Path (Split-Path -Parent $apacheRootPath) 'phpMyAdmin')
    if (!$replacement.Contains(('Alias /phpmyadmin "' + $oldPma + '/"'))) { throw 'Alias phpMyAdmin distinto del revisado.' }
    $replacement = $replacement.Replace(('Alias /phpmyadmin "' + $oldPma + '/"'),('Alias /phpmyadmin "' + (Slash $pmaRuntime) + '/"'))
    $replacement = $replacement.Replace(('<Directory "' + $oldPma + '">'),('<Directory "' + (Slash $pmaRuntime) + '">'))
}
Write-Utf8 (Join-Path $candidate 'httpd-xampp.conf') $replacement
Write-Utf8 (Join-Path $testConf 'extra/httpd-xampp.conf') $replacement
$httpPort = Free-Port
do { $httpsPort = Free-Port } while ($httpsPort -eq $httpPort)
$httpConf = Join-Path $testConf 'httpd.conf'
$main = [IO.File]::ReadAllText($httpConf)
if ([regex]::Matches($main,'(?m)^Listen 80\s*$').Count -ne 1) { throw 'Listen HTTP distinto del revisado.' }
$main = [regex]::Replace($main,'(?m)^Listen 80\s*$',('Listen 127.0.0.1:' + $httpPort))
$main = [regex]::Replace($main,'(?m)^ServerName localhost:80\s*$',('ServerName localhost:' + $httpPort))
$main += @"

SetEnv PGCV_TEST_PROJECT "$(Slash $project)"
Alias /__pgcv_php84_probe/ "$(Slash $public)/"
<Directory "$(Slash $public)">
    AllowOverride None
    Require local
</Directory>
"@
Write-Utf8 $httpConf $main
Copy-Item -LiteralPath (Join-Path $project 'tests/fixtures/php84_apache_probe.php') -Destination (Join-Path $public 'probe.php')
foreach ($file in Get-ChildItem -LiteralPath $testConf -Recurse -File -Filter '*.conf') {
    $value = [IO.File]::ReadAllText($file.FullName)
    # Redirect all active relative includes into the copied configuration tree.
    $value = [regex]::Replace($value,'(?m)^(\s*Include(?:Optional)?\s+)"?conf/([^"\r\n]+?)"?\s*$', {
        param($match)
        $match.Groups[1].Value + '"' + (Slash (Join-Path $testConf $match.Groups[2].Value)) + '"'
    })
    $value = $value.Replace((Slash $apacheRootPath) + '/logs/', (Slash (Join-Path $work 'logs')) + '/')
    $value = $value.Replace('"logs/', '"' + (Slash (Join-Path $work 'logs')) + '/')
    if ($file.Name -eq 'httpd-ssl.conf') {
        if ([regex]::Matches($value,'(?m)^Listen 443\s*$').Count -ne 1) { throw 'Listen HTTPS distinto del revisado.' }
        $value = [regex]::Replace($value,'(?m)^Listen 443\s*$',('Listen 127.0.0.1:' + $httpsPort))
        $value = $value.Replace('_default_:443','127.0.0.1:' + $httpsPort).Replace('www.example.com:443','localhost:' + $httpsPort)
    }
    Write-Utf8 $file.FullName $value
}
$report = @{checked_at=(Get-Date).ToString('o');installed=$false;main_server_restarted=$false;real_email=$false;order_confirmed=$false;original_config_hashes=$hashes;original_ini_hash=$originalIniHash;candidate='candidate/httpd-xampp.conf';checks=@{}}
$report.phpmyadmin_alternative = $PhpMyAdminRuntime -ne ''
$server = $null
try {
    $savedPreference = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    $syntax = & $apache -t -f $httpConf 2>&1
    $syntaxExit = $LASTEXITCODE
    $ErrorActionPreference = $savedPreference
    Write-Utf8 (Join-Path $work 'syntax.log') ($syntax -join "`n")
    if ($syntaxExit -ne 0) { throw 'La configuración completa no pasa sintaxis; revisa syntax.log protegido.' }
    $report.checks.apache_syntax = $true
    # Match the normal XAMPP DLL search environment: no temporary PATH prefix
    # and no PHP working directory that could hide missing DLL preloads.
    $server = Start-Process -FilePath $apache -ArgumentList @('-X','-f',('"' + $httpConf + '"')) -WorkingDirectory (Join-Path $apacheRootPath 'bin') -WindowStyle Hidden -RedirectStandardOutput (Join-Path $work 'stdout.log') -RedirectStandardError (Join-Path $work 'stderr.log') -PassThru
    $base = 'http://127.0.0.1:' + $httpPort
    $deadline = (Get-Date).AddSeconds(15)
    do {
        try { $probe = Fetch ($base + '/__pgcv_php84_probe/probe.php'); break }
        catch { if ($server.HasExited) { throw 'Apache de prueba se cerró; revisa los logs protegidos.' }; Start-Sleep -Milliseconds 100 }
    } while ((Get-Date) -lt $deadline)
    if (!$probe -or $probe.status -ne 200) { throw 'No responde la comprobación PHP.' }
    $data = $probe.content | ConvertFrom-Json
    $report.checks.php84_module = $data.php_version -eq '8.4.26' -and $data.sapi -eq 'apache2handler' -and $data.thread_safe
    $report.checks.isolated_ini = $data.ini.Replace('\','/') -eq (Slash (Join-Path $iniDir 'php.ini'))
    $report.checks.pgcv_components = @($data.required_extensions | Where-Object { !$_ }).Count -eq 0 -and $data.png_ok -and $data.pdf_ok -and $data.smtp_time_limit -eq 15 -and $data.tls_identity_required
    $report.checks.database_integrity = $data.orphan_count -eq 0 -and $data.missing_constraints -eq 0
    $pgcvHome = Fetch ($base + '/PGCV/index.php')
    $report.checks.pgcv_home = $pgcvHome.status -eq 200 -and $pgcvHome.content -match 'carousel-example-generic'
    $report.checks.pgcv_no_php_errors = !(Test-Path -LiteralPath $phpLog) -or [string]::IsNullOrWhiteSpace([IO.File]::ReadAllText($phpLog))
    $private = Fetch ($base + '/PGCV/.git/HEAD')
    $report.checks.private_files_denied = $private.status -eq 403
    # A timeout is a compatibility finding, not a reason to lose the report.
    try { $pma = Fetch ($base + '/phpmyadmin/') 30 }
    catch { $pma = @{status=0;content=''}; $report.phpmyadmin_request_error = $_.Exception.GetType().Name }
    Write-Utf8 (Join-Path $work 'phpmyadmin-response.html') $pma.content
    $report.phpmyadmin_status = $pma.status
    $pmaVersion = [regex]::Match($pma.content,'phpMyAdmin ([0-9]+\.[0-9]+\.[0-9]+)')
    if ($pmaVersion.Success) { $report.phpmyadmin_version = $pmaVersion.Groups[1].Value }
    $report.phpmyadmin_diagnostic_markers = [regex]::Matches($pma.content,'(?i)deprecated|fatal error|warning:|uncaught|mysqli extension is missing').Count
    $report.checks.phpmyadmin_page = $pma.status -eq 200 -and $pma.content -match 'phpMyAdmin' -and $report.phpmyadmin_diagnostic_markers -eq 0
    $report.checks.all_no_php_errors = !(Test-Path -LiteralPath $phpLog) -or [string]::IsNullOrWhiteSpace([IO.File]::ReadAllText($phpLog))
    $report.php_log_diagnostics = @{}
    if (Test-Path -LiteralPath $phpLog) {
        foreach ($group in (Get-Content -LiteralPath $phpLog | Group-Object {
            if ($_ -match 'PHP (Deprecated|Warning|Fatal error|Notice):') { $matches[1] } else { 'other' }
        })) { $report.php_log_diagnostics[$group.Name] = $group.Count }
    }
    $report.checks.original_configs_unchanged = $true
    foreach ($relative in $hashes.Keys) {
        if ((Get-FileHash -LiteralPath (Join-Path $confRoot $relative) -Algorithm SHA256).Hash -ne $hashes[$relative]) { $report.checks.original_configs_unchanged = $false }
    }
    $report.checks.original_ini_unchanged = (Get-FileHash -LiteralPath $originalIni -Algorithm SHA256).Hash -eq $originalIniHash
    $report.ready_for_review = @($report.checks.Values | Where-Object { !$_ }).Count -eq 0
    Write-Utf8 (Join-Path $work 'verification.json') ($report | ConvertTo-Json -Depth 5)
    Write-Output ('Candidato protegido: ' + $work)
    Write-Output ($report.checks | ConvertTo-Json)
    Write-Output ('Preparado para revisión: ' + $report.ready_for_review)
} finally {
    if ($null -ne $server) {
        $server.Refresh()
        if (!$server.HasExited) { Stop-Process -Id $server.Id; $server.WaitForExit(10000) | Out-Null }
    }
}
