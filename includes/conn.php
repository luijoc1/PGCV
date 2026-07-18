<?php
include_once __DIR__ . '/config.php';

class Database
{
	private $server = DB_SERVER;
	private $username = DB_USER;
	private $password = DB_PASS;
	private $options = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	);
	protected $conn;

	public function open()
	{
		try {
			$this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
			return $this->conn;
		} catch (PDOException $e) {
			die("Hay algún problema en la conexión: " . $e->getMessage());
		}
	}

	public function close()
	{
		$this->conn = null;
	}
}

$pdo = new Database();
