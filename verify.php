<?php
include 'includes/session.php';

if (isset($_POST['login'])) {
	// Validar token CSRF
	if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
		$_SESSION['error'] = 'Solicitud inválida. Intenta de nuevo.';
		header('location: login.php');
		exit();
	}
	// Sanitizar inputs
	$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
	$password = trim($_POST['password']);

	// Validar formato de email
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$_SESSION['error'] = 'Formato de correo electrónico inválido';
		header('location: login.php');
		exit();
	}

	// Validar que la contraseña no esté vacía
	if (empty($password)) {
		$_SESSION['error'] = 'Ingresa tu contraseña';
		header('location: login.php');
		exit();
	}

	// Límite de intentos (5 intentos por 15 minutos)
	if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
	if (!isset($_SESSION['login_time'])) $_SESSION['login_time'] = time();

	// Resetear intentos si pasaron 15 minutos
	if (time() - $_SESSION['login_time'] > 900) {
		$_SESSION['login_attempts'] = 0;
		$_SESSION['login_time'] = time();
	}

	if ($_SESSION['login_attempts'] >= 5) {
		$minutos = ceil((900 - (time() - $_SESSION['login_time'])) / 60);
		$_SESSION['error'] = 'Demasiados intentos fallidos. Intenta de nuevo en ' . $minutos . ' minuto(s).';
		header('location: login.php');
		exit();
	}

	$conn = $pdo->open();

	try {
		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email = :email");
		$stmt->execute(['email' => $email]);
		$row = $stmt->fetch();

		if ($row['numrows'] > 0) {
			if ($row['status']) {
				if (password_verify($password, $row['password'])) {
					// Login exitoso - resetear intentos
					$_SESSION['login_attempts'] = 0;
					$_SESSION['login_time'] = time();

					if ($row['type']) {
						$_SESSION['admin'] = $row['id'];
					} else {
						$_SESSION['user'] = $row['id'];
					}
				} else {
					$_SESSION['login_attempts']++;
					$restantes = 5 - $_SESSION['login_attempts'];
					$_SESSION['error'] = 'Contraseña incorrecta. Te quedan ' . $restantes . ' intento(s).';
				}
			} else {
				$_SESSION['error'] = 'Cuenta no activada. Revisa tu correo electrónico.';
			}
		} else {
			$_SESSION['login_attempts']++;
			$_SESSION['error'] = 'Correo electrónico no encontrado';
		}
	} catch (PDOException $e) {
		$_SESSION['error'] = 'Error de conexión. Intenta de nuevo.';
	}

	$pdo->close();
} else {
	$_SESSION['error'] = 'Ingresa tus credenciales primero';
}

header('location: login.php');
exit();
