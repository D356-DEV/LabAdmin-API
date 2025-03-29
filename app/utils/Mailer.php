<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/Exception.php';

class Mailer
{
    private function createMailerInstance(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isMail();
        $mail->isHTML(true);
        return $mail;
    }

    public function notifyRegistration($email, $first_name, $last_name): bool
    {
        try {
            $mail = $this->createMailerInstance();
            $mail->setFrom('noreply@labadmin.mx', 'LabAdmin');
            $mail->addAddress($email, "$first_name $last_name");
            $mail->Subject = '';
            $mail->Body = '
            <!DOCTYPE html>
            <html lang="es">
            </html>
            ';
            $mail->AltBody = '';

            return $mail->send();
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public function notifyLogin($email): bool
    {
        try {
            $mail = $this->createMailerInstance();
            $mail->setFrom('noreply@labadmin.mx', 'LabAdmin');
            $mail->addAddress($email);
            $mail->Subject = '';
            $mail->Body = '
            <!DOCTYPE html>
            <html lang="es">
            </html>
            ';
            $mail->AltBody = '';

            return $mail->send();
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
