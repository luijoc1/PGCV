<?php
require_once __DIR__ . '/BaseControllerTestCase.php';

abstract class BaseHttpTestCase extends BaseControllerTestCase
{
    private $server;
    private $baseUrl;
    private $cookie = '';

    protected function setUp(): void
    {
        parent::setUp();
        mkdir($this->sandbox . '/admin');
        mkdir($this->sandbox . '/admin/includes');
        mkdir($this->sandbox . '/sessions');
        mkdir($this->sandbox . '/images');
        mkdir($this->sandbox . '/uploads');
        copy(__DIR__ . '/fixtures/protected_routes_router.php', $this->sandbox . '/router.php');
        copy(__DIR__ . '/../admin/includes/session.php', $this->sandbox . '/admin/includes/session.php');
        copy(__DIR__ . '/../includes/session.php', $this->sandbox . '/includes/session.php');
        copy(__DIR__ . '/../perfil_editar.php', $this->sandbox . '/perfil_editar.php');
        copy(__DIR__ . '/../admin/profile_update.php', $this->sandbox . '/admin/profile_update.php');
        foreach (['authentication', 'csrf', 'output', 'pricing', 'category_operations', 'product_inventory', 'image_upload', 'product_deletion', 'cart_operations', 'user_administration', 'profile_update'] as $helper) {
            copy(__DIR__ . '/../includes/' . $helper . '.php', $this->sandbox . '/includes/' . $helper . '.php');
        }
        copy(__DIR__ . '/../admin/includes/slugify.php', $this->sandbox . '/admin/includes/slugify.php');
        foreach (array_keys($this->routes()) as $route) {
            copy(__DIR__ . '/../' . $route, $this->sandbox . '/' . $route);
        }
        file_put_contents($this->sandbox . '/includes/conn.php', <<<'PHP'
<?php
$pdo = new class {
    public function open() {
        return new PDO('sqlite:' . __DIR__ . '/../database.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    public function close() {}
};
PHP
        );
        $stmt = $this->connection->prepare('INSERT INTO users (id,email,password,status,type) VALUES (1,?, ?,1,0),(2,?, ?,1,1)');
        $hash = password_hash('test-secret123', PASSWORD_DEFAULT);
        $stmt->execute(['client@example.com', $hash, 'admin@example.com', $hash]);
        $this->connection->exec("CREATE TABLE category (id INTEGER PRIMARY KEY,name TEXT,cat_slug TEXT UNIQUE); INSERT INTO category VALUES (1,'Original category','original'); CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT,category_id INTEGER,name TEXT,description TEXT,stock INTEGER,price NUMERIC,descuento INTEGER,stock_minimo INTEGER DEFAULT 5,slug TEXT UNIQUE,photo TEXT DEFAULT ''); INSERT INTO products (id,category_id,name,description,stock,price,descuento,slug) VALUES (1,1,'Original product','<p>Safe</p>',5,100,20,'original-product'),(2,1,'Other product','',8,200,10,'other-product'); CREATE TABLE cart (id INTEGER PRIMARY KEY,user_id INTEGER,product_id INTEGER,quantity INTEGER); CREATE TABLE details (id INTEGER PRIMARY KEY,sales_id INTEGER,product_id INTEGER,quantity INTEGER); CREATE TABLE logs_productos (id INTEGER PRIMARY KEY AUTOINCREMENT,id_referencia INTEGER,informacion_anterior TEXT,nueva_informacion TEXT,tipo_operacion TEXT,ip TEXT,usuario_created TEXT)");
        $this->connection->exec('CREATE TABLE logs_usuarios (id INTEGER PRIMARY KEY AUTOINCREMENT,id_referencia INTEGER,informacion_anterior TEXT,nueva_informacion TEXT,tipo_operacion TEXT,ip TEXT,usuario_created TEXT)');
        $this->connection->exec('CREATE TABLE sales (id INTEGER PRIMARY KEY,user_id INTEGER); CREATE TABLE checkout_requests (request_key TEXT PRIMARY KEY,user_id INTEGER,sales_id INTEGER)');
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        $this->assertIsResource($socket, $errorMessage);
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $this->baseUrl = 'http://' . $address;
        $this->server = proc_open([PHP_BINARY, '-d', 'session.save_path=' . $this->sandbox . '/sessions', '-d', 'upload_tmp_dir=' . $this->sandbox . '/uploads', '-d', 'error_log=' . $this->sandbox . '/php-errors.log', '-S', $address, $this->sandbox . '/router.php'], [0 => ['pipe', 'r'], 1 => ['file', $this->sandbox . '/server.log', 'a'], 2 => ['file', $this->sandbox . '/server.log', 'a']], $pipes, $this->sandbox);
        $this->assertIsResource($this->server);
        fclose($pipes[0]);
        $deadline = microtime(true) + 5;
        do {
            $probe = curl_init($this->baseUrl . '/__health');
            curl_setopt_array($probe, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT_MS => 200, CURLOPT_PROXY => '']);
            $ready = curl_exec($probe) === 'ready';
            curl_close($probe);
            if ($ready) { break; }
            usleep(20000);
        } while (microtime(true) < $deadline);
        $this->assertTrue($ready, file_get_contents($this->sandbox . '/server.log'));
    }

    protected function tearDown(): void
    {
        if (is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }
        $log = $this->sandbox . '/php-errors.log';
        $errors = is_file($log) ? file_get_contents($log) : '';
        parent::tearDown();
        $this->assertSame('', $errors, 'The isolated HTTP server must not emit PHP errors.');
    }

    protected function http(string $path, array $post = [], string $method = 'POST', bool $multipart = false): array
    {
        $curl = curl_init($this->baseUrl . $path);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 5, CURLOPT_PROXY => '', CURLOPT_COOKIE => $this->cookie, CURLOPT_CUSTOMREQUEST => $method]);
        if ($method === 'POST') {
            if ($multipart) {
                $fields = [];
                $flatten = function ($name, $value) use (&$flatten, &$fields) {
                    if (is_array($value)) {
                        if (!$value) $fields[$name . '[]'] = '';
                        foreach ($value as $key => $item) $flatten($name . '[' . $key . ']', $item);
                    } else {
                        $fields[$name] = $value;
                    }
                };
                foreach ($post as $name => $value) $flatten($name, $value);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
            } else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            }
        }
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);
        $this->assertNotFalse($response, $error);
        $headers = substr($response, 0, $headerSize);
        if (preg_match('/^Set-Cookie: ([^;\r\n]+)/mi', $headers, $match)) { $this->cookie = $match[1]; }
        $this->assertStringNotContainsString('Fatal error', $response);
        $this->assertStringNotContainsString('Warning:', $response);
        return ['status' => $status, 'headers' => $headers, 'body' => substr($response, $headerSize)];
    }

    protected function snapshot(): array
    {
        $result = [];
        foreach (['users', 'products', 'category'] as $table) {
            $result[$table] = $this->connection->query('SELECT * FROM ' . $table . ' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }

    public function routes(): array
    {
        return [
            'admin/products_add.php' => ['admin/products_add.php', ['add' => 1, 'name' => 'New product']],
            'admin/products_edit.php' => ['admin/products_edit.php', ['edit' => 1, 'id' => 1, 'name' => 'Changed']],
            'admin/products_delete.php' => ['admin/products_delete.php', ['delete' => 1, 'id' => 1]],
            'admin/products_photo.php' => ['admin/products_photo.php', ['upload' => 1, 'id' => 1]],
            'admin/users_delete.php' => ['admin/users_delete.php', ['delete' => 1, 'id' => 2]],
            'admin/users_add.php' => ['admin/users_add.php', ['add' => 1]],
            'admin/users_edit.php' => ['admin/users_edit.php', ['edit' => 1, 'id' => 1]],
            'admin/users_activate.php' => ['admin/users_activate.php', ['activate' => 1, 'id' => 1]],
            'admin/users_row.php' => ['admin/users_row.php', ['id' => 1]],
            'admin/users_photo.php' => ['admin/users_photo.php', ['upload' => 1, 'id' => 1]],
            'admin/category_add.php' => ['admin/category_add.php', ['add' => 1, 'name' => 'New category']],
            'admin/category_delete.php' => ['admin/category_delete.php', ['delete' => 1, 'id' => 1]],
            'admin/category_edit.php' => ['admin/category_edit.php', ['edit' => 1, 'id' => 1, 'name' => 'Changed category']],
            'admin/quitar_descuento.php' => ['admin/quitar_descuento.php', ['id' => 1]],
            'admin/actualizar_estado.php' => ['admin/actualizar_estado.php', ['id' => 1, 'estado' => 'enviado']],
            'admin/products_row.php' => ['admin/products_row.php', ['id' => 1]],
        ];
    }

}
