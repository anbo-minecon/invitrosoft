<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'voceroadso2025@gmail.com'; // cambia esto
    $mail->Password = 'kkey pewd xjlj dgxc'; // cambia esto
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('voceroadso2025@gmail.com', 'InvitroSoft Prueba');
    $mail->addAddress('adaniesbasilio@gmail.com', 'Destinatario');

    $mail->isHTML(true);
    $mail->Subject = 'Correo de prueba InvitroSoft';
    $mail->Body = '<h3>Prueba exitosa de PHPMailer 🚀</h3>';

    $mail->send();
    echo "✅ Correo enviado correctamente.";
} catch (Exception $e) {
    echo "❌ Error al enviar correo: {$mail->ErrorInfo}";
}
