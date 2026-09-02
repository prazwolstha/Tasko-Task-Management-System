<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$config = require '../smtp_config.php';

$mail = new PHPMailer(true);

try {

    // Gmail SMTP
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['port'];

    // Sender
    $mail->setFrom(
        $config['username'],
        'TasKo Task Management System'
    );

    // Receiver
    // PUT YOUR PERSONAL EMAIL HERE
    $mail->addAddress('prajwalgaming29@gmail.com');

    // Email settings
    $mail->isHTML(true);

    $mail->Subject = 'TasKo Email Test';

    $mail->Body = '
        <h2>TasKo Email Test</h2>

        <p>Hello!</p>

        <p>
            This is a test email from the TasKo
            Task Management System.
        </p>

        <p>
            <strong>PHPMailer + Gmail SMTP is working successfully.</strong>
        </p>
    ';

    $mail->send();

    echo "Email sent successfully!";

} catch (Exception $e) {

    echo "Email could not be sent.<br><br>";

    echo "Error: " . $mail->ErrorInfo;

}
?>