<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar PHPMailer manualmente
require __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

function sendStockAlertEmail(array $smtp, array $product, float $threshold) {
    $mail = new PHPMailer(true);

    try {
        // Config SMTP
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = 'voceroadso2025@gmail.com';
        $mail->Password = 'kkey pewd xjlj dgxc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp['port'];

        // Remitente y destinatario
        $mail->setFrom($smtp['from'], 'voceroadso2025@gmail.com');
        $mail->addAddress($smtp['to'], 'adaniesbasilio@gmail.com');

        // Logo embebido
        $logoPath = __DIR__ . '/invitrosoft/img/logo.svg';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_invitrosoft', 'logo.svg');
        }

        // Asunto
        $subject = "⚠️ Alerta de stock bajo - {$product['nombre']}";

        // Cuerpo HTML
        $htmlBody = "
        <html>
        <body style='font-family:Arial, sans-serif; background:#f7f9fb; padding:20px;'>
            <table width='600' align='center' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);'>
                <tr style='background:linear-gradient(135deg,#0b6efd,#4da3ff);color:#fff;'>
                    <td style='text-align:center;padding:20px;'>
                        <img src='cid:logo_invitrosoft' alt='InvitroSoft' style='width:90px;margin-bottom:10px;'>
                        <h2 style='margin:0;'>Sistema InvitroSoft</h2>
                        <p style='margin:0;font-size:13px;'>Notificación automática de inventario</p>
                    </td>
                </tr>
                <tr>
                    <td style='padding:25px;'>
                        <p>Hola 👋,</p>
                        <p>El reactivo <strong>{$product['nombre']}</strong> ha alcanzado un nivel de existencia bajo en el sistema <b>InvitroSoft</b>.</p>
                        <table cellpadding='6' cellspacing='0' width='100%' style='border-collapse:collapse;margin:15px 0;'>
                            <tr style='background:#f5f5f5;'><td><b>ID:</b></td><td>{$product['id']}</td></tr>
                            <tr><td><b>Categoría:</b></td><td>{$product['categoria']}</td></tr>
                            <tr style='background:#f5f5f5;'><td><b>Cantidad actual:</b></td><td>{$product['cantidad']} {$product['um']}</td></tr>
                            <tr><td><b>Cantidad mínima:</b></td><td>{$threshold}</td></tr>
                        </table>
                        <p>Por favor revisa el inventario y considera registrar una reposición para evitar interrupciones en los procesos de micropropagación in vitro.</p>
                        <p style='font-size:13px;color:#777;'>Mensaje generado automáticamente por el sistema InvitroSoft.</p>
                    </td>
                </tr>
                <tr style='background:#0b6efd;color:#fff;'>
                    <td style='text-align:center;padding:10px;font-size:12px;'>
                        InvitroSoft © " . date('Y') . " | Sistema de micropropagación in vitro
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        // Alternativo
        $altBody = "InvitroSoft - Alerta de inventario\n".
                   "Reactivo: {$product['nombre']}\n".
                   "Cantidad actual: {$product['cantidad']} {$product['um']}\n".
                   "Cantidad mínima: {$threshold}\n";

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

function sendVencimientoAlertEmail(array $smtp, array $product, int $dias_restantes) {
    $mail = new PHPMailer(true);
    try {
        // Config SMTP
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = 'voceroadso2025@gmail.com';
        $mail->Password = 'kkey pewd xjlj dgxc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp['port'];

        $mail->setFrom($smtp['from'], 'voceroadso2025@gmail.com');
        $mail->addAddress($smtp['to'], 'adaniesbasilio@gmail.com');

        // Logo embebido
        $logoPath = __DIR__ . '/../img/logo.png';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_invitrosoft', 'logo.png');
        }

        $subject = "⏰ Reactivo próximo a vencer - {$product['nombre']}";

        $htmlBody = "
        <html>
        <body style='font-family:Arial, sans-serif; background:#f7f9fb; padding:20px;'>
            <table width='600' align='center' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);'>
                <tr style='background:linear-gradient(135deg,#ff9800,#f44336);color:#fff;'>
                    <td style='text-align:center;padding:20px;'>
                        <img src='cid:logo_invitrosoft' alt='InvitroSoft' style='width:80px;margin-bottom:10px;'>
                        <h2 style='margin:0;'>InvitroSoft - Alerta de Vencimiento</h2>
                        <p style='margin:0;font-size:13px;'>Notificación automática de reactivo próximo a vencer</p>
                    </td>
                </tr>
                <tr>
                    <td style='padding:25px;'>
                        <p>Hola 👋,</p>
                        <p>El reactivo <b>{$product['nombre']}</b> está próximo a vencer en <b>{$dias_restantes} días</b>.</p>

                        <table cellpadding='6' cellspacing='0' width='100%' style='border-collapse:collapse;margin:15px 0;'>
                            <tr><td><b>ID:</b></td><td>{$product['id']}</td></tr>
                            <tr><td><b>Categoría:</b></td><td>{$product['categoria']}</td></tr>
                            <tr><td><b>Fecha de vencimiento:</b></td><td>{$product['fecha_vencimiento']}</td></tr>
                            <tr><td><b>Días restantes:</b></td><td>{$dias_restantes}</td></tr>
                        </table>

                        <p>Por favor verifica el estado del reactivo y considera su reemplazo o uso prioritario antes del vencimiento.</p>
                        <p style='font-size:13px;color:#777;'>Este mensaje fue generado automáticamente por InvitroSoft.</p>
                    </td>
                </tr>
                <tr style='background:#0b6efd;color:#fff;text-align:center;'>
                    <td style='padding:10px;font-size:12px;'>
                        InvitroSoft © " . date('Y') . " | Sistema de micropropagación in vitro
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;

        $mail->send();
        return ['success' => true, 'msg' => 'Correo de vencimiento enviado'];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

