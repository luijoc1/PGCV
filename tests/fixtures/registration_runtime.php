<?php
// Infrastructure for subprocess tests; no real connection or mail transport.
$input = json_decode(file_get_contents($argv[1]), true);
$_POST = $input['post'];
$_GET = $input['get'];
$_SESSION = ['csrf_token' => str_repeat('a', 64)];
$_SERVER['REQUEST_METHOD'] = $input['method'];
$connection = new PDO('sqlite:' . $input['database']);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo = new class($connection) {
    private $connection;
    public function __construct(PDO $connection) { $this->connection = $connection; }
    public function open() { return $this->connection; }
    public function close() {}
};
$deliveries = [];
define('MAIL_USER', 'sender@example.com');
function configuredMailer() {
    return new class {
        public $CharSet, $Subject, $Body, $ErrorInfo = 'Simulated mail failure';
        private $recipient;
        public function addAddress($address) { $this->recipient = $address; }
        public function addReplyTo($address) {}
        public function isHTML($value) {}
        public function send() {
            global $input, $deliveries;
            if ($input['mail_failure']) {
                throw new \PHPMailer\PHPMailer\Exception('Simulated mail failure');
            }
            $deliveries[] = ['recipient' => $this->recipient, 'body' => $this->Body];
            return true;
        }
    };
}
function applicationUrl($path, $query) {
    return 'https://example.com/' . $path . '?' . http_build_query($query);
}
require_once $input['project'] . '/includes/csrf.php';
require_once $input['project'] . '/includes/output.php';
require_once $input['project'] . '/vendor/autoload.php';
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) { return false; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
ob_start();
register_shutdown_function(function () use ($connection, &$deliveries) {
    $html = ob_get_clean();
    echo json_encode([
        'session' => $_SESSION,
        'users' => $connection->query('SELECT * FROM users ORDER BY id')->fetchAll(),
        'deliveries' => $deliveries,
        'html' => $html,
    ]);
});
chdir($input['sandbox']);
require $input['route'];
