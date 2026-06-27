<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	include 'includes/session.php';
    include 'config.php';
	if(isset($_POST['signup'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];

		// Validar que se haya aceptado el manejo de datos
		if(!isset($_POST['aceptar_datos']) || $_POST['aceptar_datos'] != 'on'){
			$_SESSION['error'] = 'Debes aceptar el uso del manejo de datos 1581 para registrarte';
			$_SESSION['firstname'] = $firstname;
			$_SESSION['lastname'] = $lastname;
			$_SESSION['email'] = $email;
			header('location: registrarse.php');
			exit();
		}

		$_SESSION['firstname'] = $firstname;
		$_SESSION['lastname'] = $lastname;
		$_SESSION['email'] = $email;

		/*

		if(!isset($_SESSION['captcha'])){
			require('recaptcha/src/autoload.php');		
			$recaptcha = new \ReCaptcha\ReCaptcha('6LevO1IUAAAAAFCCiOHERRXjh3VrHa5oywciMKcw', new \ReCaptcha\RequestMethod\SocketPost());
			$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

			if (!$resp->isSuccess()){
		  		$_SESSION['error'] = 'Por favor conteste recaptcha correctamente';
		  		header('location: signup.php');	
		  		exit();	
		  	}	
		  	else{
		  		$_SESSION['captcha'] = time() + (10*60);
		  	}

		} */

		if($password != $repassword){
			$_SESSION['error'] = 'Las contraseñas no coinciden';
			header('location: registrarse.php');
		}
		else{
			$conn = $pdo->open();

			$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE email=:email");
			$stmt->execute(['email'=>$email]);
			$row = $stmt->fetch();
			if($row['numrows'] > 0){
				$_SESSION['error'] = 'Correo electrónico ya tomado';
				header('location: registrarse.php');
			}
			else{
				$now = date('Y-m-d');
				$password = password_hash($password, PASSWORD_DEFAULT);

				//generate code
				$set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$code=substr(str_shuffle($set), 0, 12);

				try{
					$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, activate_code, created_on) VALUES (:email, :password, :firstname, :lastname, :code, :now)");
					$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'code'=>$code, 'now'=>$now]);
					$userid = $conn->lastInsertId();

					

$nombre_completo = $firstname . ' ' . $lastname;
$message = '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family: Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 16px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e0e0e0;">
        <tr>
          <td style="background:#1a2e4a; padding:32px 36px 28px; text-align:center;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr><td align="center" style="padding-bottom:20px;">
                <span style="display:inline-block; background:#3a8eff; border-radius:8px; padding:8px 12px;">
                  <span style="font-size:22px;">🛍</span>
                </span>
                <span style="font-size:18px; font-weight:bold; color:#ffffff; vertical-align:middle; margin-left:10px;">Almacén Online</span>
              </td></tr>
              <tr><td align="center" style="padding-bottom:14px;">
                <div style="width:56px; height:56px; background:rgba(58,142,255,0.2); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:28px;">✅</div>
              </td></tr>
              <tr><td align="center">
                <h1 style="color:#ffffff; font-size:22px; font-weight:bold; margin:0 0 6px;">¡Registro exitoso!</h1>
                <p style="color:rgba(255,255,255,0.65); font-size:14px; margin:0;">Tu cuenta está lista para activarse</p>
              </td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 36px 24px;">
            <p style="color:#444; font-size:15px; line-height:1.6; margin:0 0 20px;">
              Hola <strong style="color:#1a2e4a;">' . $nombre_completo . '</strong>, gracias por unirte.
              Para empezar a comprar activa tu cuenta haciendo clic en el botón.
            </p>
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa; border-radius:8px; border:1px solid #e0e0e0; margin-bottom:24px;">
              <tr>
                <td style="padding:14px 16px;">
                  <p style="font-size:12px; color:#999; margin:0 0 2px;">Correo registrado</p>
                  <p style="font-size:14px; color:#1a2e4a; font-weight:bold; margin:0;">' . $email . '</p>
                </td>
              </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
              <tr><td align="center">
                <a href="http://localhost/PGCV/activate.php?code=' . $code . '&user=' . $userid . '"
                   style="display:inline-block; background:#1a2e4a; color:#ffffff; text-decoration:none;
                          padding:13px 32px; border-radius:8px; font-size:15px; font-weight:bold;">
                  🔓 Activar mi cuenta
                </a>
              </td></tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e0e0e0; border-radius:8px;">
              <tr>
                <td style="padding:12px 16px;">
                  <p style="font-size:13px; color:#888; margin:0; line-height:1.5;">
                    ℹ️ Si no solicitaste este registro, ignora este mensaje. El enlace expira en 24 horas.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e0e0e0; padding:16px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td style="font-size:12px; color:#bbb;">© 2026 Almacén Online</td>
                <td align="right" style="font-size:12px; color:#bbb;">Correo automático — no responder</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';

					//Load phpmailer
		    		require 'vendor/autoload.php';

		    		$mail = new PHPMailer(true);                             
				    try {
				        //Server settings
				        $mail->isSMTP();                                     
				        $mail->Host = 'smtp.gmail.com';                      
				        $mail->SMTPAuth = true;                               
				        $mail->Username = MAIL_USER;
                        $mail->Password = MAIL_PASS;                   
				        $mail->SMTPOptions = array(
				            'ssl' => array(
				            'verify_peer' => false,
				            'verify_peer_name' => false,
				            'allow_self_signed' => true
				            )
				        );                         
				        $mail->SMTPSecure = 'ssl';                           
				        $mail->Port = 465;                                   

				        $mail->setFrom(MAIL_USER);
				        
				        //Recipients
				        $mail->addAddress($email);              
				        $mail->addReplyTo(MAIL_USER);
				       
				        //Content
				        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Subject = 'Activa tu cuenta - Almacén Online';
                        $mail->Body    = $message;

				        $mail->send();

				        unset($_SESSION['firstname']);
				        unset($_SESSION['lastname']);
				        unset($_SESSION['email']);

				        $_SESSION['success'] = 'Cuenta creada. Revise su correo electrónico para activar.';
				        header('location: registrarse.php');

				    } 
				    catch (Exception $e) {
				        $_SESSION['error'] = 'El mensaje no pudo ser enviado. Error de correo: '.$mail->ErrorInfo;
				        header('location: registrarse.php');
				    }


				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
					header('location: registro.php');
				}

				$pdo->close();

			}

		}

	}
	else{
		$_SESSION['error'] = 'Rellene el formulario de registro primero';
		header('location: registrarse.php');
	}

?>