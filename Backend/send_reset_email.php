<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require '../vendor/autoload.php';

// Load SMTP configuration
$config = require '../smtp_config.php';


// --------------------------------------------------
// DATABASE CONNECTION
// --------------------------------------------------

$conn = mysqli_connect("localhost", "root", "", "tasko");

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}


// --------------------------------------------------
// CHECK FORM SUBMISSION
// --------------------------------------------------

if (
    !isset($_POST['sendResetEmail']) ||
    !isset($_POST['email']) ||
    !isset($_POST['type'])
) {
    die("Invalid Request.");
}


$email = trim($_POST['email']);
$type  = trim($_POST['type']);


// --------------------------------------------------
// SELECT TABLE
// --------------------------------------------------

if ($type == "admin") {

    $table = "admins";

} elseif ($type == "user") {

    $table = "users";

} else {

    die("Invalid User Type.");

}


// --------------------------------------------------
// CHECK EMAIL FORMAT
// --------------------------------------------------

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo "
    <script>
        alert('Invalid email address.');
        history.back();
    </script>
    ";

    exit();
}


$email = mysqli_real_escape_string($conn, $email);


// --------------------------------------------------
// CHECK IF EMAIL IS REGISTERED
// --------------------------------------------------

$sql = "
    SELECT *
    FROM $table
    WHERE email='$email'
    LIMIT 1
";

$result = mysqli_query($conn, $sql);


if (!$result) {

    die("Database Error: " . mysqli_error($conn));

}


if (mysqli_num_rows($result) == 0) {

    echo "
    <script>
        alert('Unregistered email. Please try again.');
        window.location='forgot_password.php?type=$type';
    </script>
    ";

    exit();
}


// --------------------------------------------------
// GENERATE SECURE RESET TOKEN
// --------------------------------------------------

$token = bin2hex(random_bytes(32));


// --------------------------------------------------
// TOKEN EXPIRATION
// 15 MINUTES
// --------------------------------------------------

$expires_at = date(
    "Y-m-d H:i:s",
    time() + (15 * 60)
);


// --------------------------------------------------
// DELETE OLD TOKEN
// --------------------------------------------------

$delete = "
    DELETE FROM password_resets
    WHERE email='$email'
    AND user_type='$type'
";

mysqli_query($conn, $delete);


// --------------------------------------------------
// SAVE NEW TOKEN
// --------------------------------------------------

$token_db = mysqli_real_escape_string($conn, $token);

$insert = "
    INSERT INTO password_resets
    (
        email,
        user_type,
        token,
        expires_at
    )
    VALUES
    (
        '$email',
        '$type',
        '$token_db',
        '$expires_at'
    )
";


if (!mysqli_query($conn, $insert)) {

    die(
        "Could not create password reset token: "
        . mysqli_error($conn)
    );

}


// --------------------------------------------------
// CREATE RESET LINK
// --------------------------------------------------

$resetLink =
    "http://localhost/TasKo/auth/reset_password.php"
    . "?token="
    . urlencode($token)
    . "&type="
    . urlencode($type);


// --------------------------------------------------
// CREATE PHPMailer
// --------------------------------------------------

$mail = new PHPMailer(true);


try {

    // --------------------------------------------------
    // SMTP SETTINGS
    // --------------------------------------------------

    $mail->isSMTP();

    $mail->Host = $config['host'];

    $mail->SMTPAuth = true;

    $mail->Username = $config['username'];

    $mail->Password = $config['password'];

    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = $config['port'];


    // --------------------------------------------------
    // SENDER
    // --------------------------------------------------

    $mail->setFrom(
        $config['username'],
        'TasKo Task Management System'
    );


    // --------------------------------------------------
    // RECEIVER
    // --------------------------------------------------

    $mail->addAddress($email);


    // --------------------------------------------------
    // EMAIL FORMAT
    // --------------------------------------------------

    $mail->isHTML(true);


    // --------------------------------------------------
    // EMAIL SUBJECT
    // --------------------------------------------------

    $mail->Subject = "TasKo Password Reset";


    // --------------------------------------------------
    // EMAIL BODY
    // --------------------------------------------------

    $mail->Body = <<<HTML

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>TasKo Password Reset</title>

</head>

<body style="
    margin:0;
    padding:0;
    background:#eef3fb;
    font-family:Arial, sans-serif;
">

<div style="
    max-width:600px;
    margin:40px auto;
    background:white;
    padding:35px;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,0.10);
">

    <h2 style="
        color:#2563eb;
        margin-bottom:20px;
    ">
        TasKo Password Reset
    </h2>


    <p style="
        color:#333;
        font-size:15px;
        line-height:1.6;
    ">
        We received a request to reset your
        TasKo account password.
    </p>


    <p style="
        color:#333;
        font-size:15px;
        line-height:1.6;
    ">
        Click the button below to create
        a new password.
    </p>


    <div style="
        text-align:center;
        margin:30px 0;
    ">

        <a
            href="$resetLink"
            style="
                display:inline-block;
                padding:14px 28px;
                background:#2563eb;
                color:white;
                text-decoration:none;
                border-radius:8px;
                font-size:16px;
                font-weight:bold;
            "
        >
            Reset Password
        </a>

    </div>


    <p style="
        color:#555;
        font-size:14px;
        line-height:1.6;
    ">
        This password reset link will expire
        in <strong>15 minutes</strong>.
    </p>


    <p style="
        color:#555;
        font-size:14px;
        line-height:1.6;
    ">
        If you did not request a password reset,
        you can safely ignore this email.
    </p>


    <hr style="
        border:none;
        border-top:1px solid #ddd;
        margin:25px 0;
    ">


    <p style="
        color:#888;
        font-size:13px;
        text-align:center;
    ">
        TasKo Task Management System
    </p>

</div>

</body>

</html>

HTML;


    // --------------------------------------------------
    // PLAIN TEXT VERSION
    // --------------------------------------------------

    $mail->AltBody =
        "TasKo Password Reset\n\n"
        . "We received a request to reset your password.\n\n"
        . "Click the following link to reset your password:\n"
        . $resetLink
        . "\n\n"
        . "This link will expire in 15 minutes.\n\n"
        . "If you did not request this, ignore this email.";


    // --------------------------------------------------
    // SEND EMAIL
    // --------------------------------------------------

    $mail->send();


    // --------------------------------------------------
    // EMAIL SENT SUCCESSFULLY
    // --------------------------------------------------

    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <meta charset="UTF-8">

        <title>Email Sent</title>

        <style>

            * {
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }

            body {

                margin: 0;

                height: 100vh;

                display: flex;

                justify-content: center;

                align-items: center;

                background: #eef3fb;

            }

            .success-box {

                width: 420px;

                background: white;

                padding: 40px;

                text-align: center;

                border-radius: 18px;

                box-shadow:
                    0 10px 30px
                    rgba(0,0,0,0.12);

            }

            .icon {

                width: 70px;

                height: 70px;

                margin: 0 auto 20px;

                display: flex;

                justify-content: center;

                align-items: center;

                border-radius: 50%;

                background: #dcfce7;

                font-size: 35px;

            }

            h2 {

                color: #15803d;

                margin-bottom: 15px;

            }

            p {

                color: #666;

                line-height: 1.6;

            }

            .back {

                display: inline-block;

                margin-top: 20px;

                padding: 12px 25px;

                background: #2563eb;

                color: white;

                text-decoration: none;

                border-radius: 8px;

                font-weight: bold;

            }

            .back:hover {

                background: #1d4ed8;

            }

        </style>

    </head>


    <body>

        <div class="success-box">

            <div class="icon">
                ✅
            </div>

            <h2>
                Email Sent Successfully
            </h2>

            <p>
                A password reset link has been
                sent to your registered email.
            </p>

            <p>
                The link is valid for
                <strong>15 minutes</strong>.
            </p>

            <a
                href="forgot_password.php?type=<?php echo htmlspecialchars($type); ?>"
                class="back"
            >
                Back
            </a>

        </div>

    </body>

    </html>

    <?php


} catch (Exception $e) {


    // --------------------------------------------------
    // EMAIL FAILED
    // DELETE TOKEN
    // --------------------------------------------------

    mysqli_query(
        $conn,
        "
        DELETE FROM password_resets
        WHERE email='$email'
        AND user_type='$type'
        "
    );


    ?>

    <!DOCTYPE html>

    <html>

    <head>

        <meta charset="UTF-8">

        <title>Email Error</title>

        <style>

            * {
                box-sizing: border-box;
                font-family: Arial, sans-serif;
            }

            body {

                margin: 0;

                height: 100vh;

                display: flex;

                justify-content: center;

                align-items: center;

                background: #eef3fb;

            }

            .error-box {

                width: 420px;

                background: white;

                padding: 40px;

                text-align: center;

                border-radius: 18px;

                box-shadow:
                    0 10px 30px
                    rgba(0,0,0,0.12);

            }

            .icon {

                width: 70px;

                height: 70px;

                margin: 0 auto 20px;

                display: flex;

                justify-content: center;

                align-items: center;

                border-radius: 50%;

                background: #fee2e2;

                font-size: 35px;

            }

            h2 {

                color: #dc2626;

                margin-bottom: 15px;

            }

            p {

                color: #666;

                line-height: 1.6;

            }

            .back {

                display: inline-block;

                margin-top: 20px;

                padding: 12px 25px;

                background: #2563eb;

                color: white;

                text-decoration: none;

                border-radius: 8px;

                font-weight: bold;

            }

        </style>

    </head>


    <body>

        <div class="error-box">

            <div class="icon">
                ❌
            </div>

            <h2>
                Email Could Not Be Sent
            </h2>

            <p>
                Something went wrong while
                sending the password reset email.
            </p>

            <p>
                Please try again.
            </p>

            <a
                href="forgot_password.php?type=<?php echo htmlspecialchars($type); ?>"
                class="back"
            >
                Try Again
            </a>

        </div>

    </body>

    </html>

    <?php

}

?>