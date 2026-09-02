<?php

session_start();

$conn = mysqli_connect("localhost", "root", "", "tasko");

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}


// --------------------------------------------------
// CHECK TOKEN AND TYPE
// --------------------------------------------------

if (!isset($_GET['token']) || !isset($_GET['type'])) {

    die("Invalid or missing password reset link.");

}

$token = trim($_GET['token']);
$type  = trim($_GET['type']);


// --------------------------------------------------
// CHECK USER TYPE
// --------------------------------------------------

if ($type == "admin") {

    $table = "admins";
    $loginPage = "login.php";

} elseif ($type == "user") {

    $table = "users";
    $loginPage = "user_login.php";

} else {

    die("Invalid user type.");

}


// --------------------------------------------------
// ESCAPE TOKEN
// --------------------------------------------------

$tokenSafe = mysqli_real_escape_string($conn, $token);


// --------------------------------------------------
// FIND TOKEN
// --------------------------------------------------

$sql = "
    SELECT *
    FROM password_resets
    WHERE token='$tokenSafe'
    AND user_type='$type'
    LIMIT 1
";

$result = mysqli_query($conn, $sql);


if (!$result) {

    die("Database Error: " . mysqli_error($conn));

}


// --------------------------------------------------
// TOKEN DOES NOT EXIST
// --------------------------------------------------

if (mysqli_num_rows($result) == 0) {

    $validToken = false;

} else {

    $resetData = mysqli_fetch_assoc($result);

    $validToken = true;

}


// --------------------------------------------------
// CHECK TOKEN EXPIRATION
// --------------------------------------------------

if ($validToken) {

    $currentTime = time();

    $expiryTime = strtotime($resetData['expires_at']);

    if ($currentTime > $expiryTime) {

        $validToken = false;

        // Delete expired token
        mysqli_query(
            $conn,
            "DELETE FROM password_resets
             WHERE token='$tokenSafe'"
        );

    }

}


// --------------------------------------------------
// VARIABLES
// --------------------------------------------------

$message = "";
$success = false;


// --------------------------------------------------
// RESET PASSWORD
// --------------------------------------------------

if ($validToken && isset($_POST['resetPassword'])) {

    $newPassword =
        trim($_POST['new_password']);

    $confirmPassword =
        trim($_POST['confirm_password']);


    // ----------------------------------------------
    // CHECK PASSWORD MATCH
    // ----------------------------------------------

    if ($newPassword != $confirmPassword) {

        $message =
            "Passwords do not match.";

    }


    // ----------------------------------------------
    // CHECK PASSWORD LENGTH
    // ----------------------------------------------

    elseif (strlen($newPassword) < 6) {

        $message =
            "Password must be at least 6 characters.";

    }


    else {

        $email =
            $resetData['email'];


        $emailSafe =
            mysqli_real_escape_string(
                $conn,
                $email
            );


        // ------------------------------------------
        // ADMIN PASSWORD
        // ------------------------------------------

        if ($type == "admin") {

            /*
             Your current admin login.php uses:

             $pass == $admin['password']

             Therefore admin password is stored as
             plain text in your current system.
            */

            $passwordToSave =
                mysqli_real_escape_string(
                    $conn,
                    $newPassword
                );

        }


        // ------------------------------------------
        // USER PASSWORD
        // ------------------------------------------

        else {

            /*
             Your add_user.php already uses:

             password_hash()

             Therefore user passwords should remain
             hashed.
            */

            $passwordToSave =
                password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

            $passwordToSave =
                mysqli_real_escape_string(
                    $conn,
                    $passwordToSave
                );

        }


        // ------------------------------------------
        // UPDATE PASSWORD
        // ------------------------------------------

        $update = "
            UPDATE $table
            SET password='$passwordToSave'
            WHERE email='$emailSafe'
        ";


        if (mysqli_query($conn, $update)) {


            // --------------------------------------
            // DELETE USED TOKEN
            // --------------------------------------

            mysqli_query(
                $conn,
                "DELETE FROM password_resets
                 WHERE token='$tokenSafe'"
            );


            $success = true;

            $message =
                "Password reset successfully.";

        } else {

            $message =
                "Could not update password: "
                . mysqli_error($conn);

        }

    }

}


// --------------------------------------------------
// PAGE
// --------------------------------------------------

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Reset Password - TasKo</title>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>

<style>

* {

    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;

}


body {

    height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    background: #eef3fb;

}


.reset-card {

    width: 420px;

    background: white;

    padding: 35px;

    border-radius: 18px;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,0.12);

}


.icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 20px;

    background: #2563eb;

    border-radius: 50%;

    display: flex;

    justify-content: center;

    align-items: center;

}


.icon i {

    color: white;

    font-size: 28px;

}


h2 {

    text-align: center;

    color: #1f2937;

    margin-bottom: 10px;

}


.description {

    text-align: center;

    color: #666;

    margin-bottom: 25px;

    line-height: 1.5;

}


.input-box {

    position: relative;

    margin-bottom: 20px;

}


.input-box input {

    width: 100%;

    padding: 14px;

    padding-right: 45px;

    border: 1px solid #d1d5db;

    border-radius: 10px;

    outline: none;

    font-size: 15px;

}


.input-box input:focus {

    border-color: #2563eb;

}


.input-box i {

    position: absolute;

    right: 15px;

    top: 16px;

    color: #666;

    cursor: pointer;

}


.btn {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 10px;

    background: #2563eb;

    color: white;

    font-size: 16px;

    cursor: pointer;

}


.btn:hover {

    background: #1d4ed8;

}


.success {

    background: #dcfce7;

    color: #15803d;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

    text-align: center;

}


.error {

    background: #fee2e2;

    color: #dc2626;

    padding: 12px;

    border-radius: 8px;

    margin-bottom: 20px;

    text-align: center;

}


.login-link {

    display: block;

    text-align: center;

    margin-top: 20px;

    color: #2563eb;

    text-decoration: none;

    font-weight: bold;

}


.login-link:hover {

    text-decoration: underline;

}


.expired {

    text-align: center;

}


.expired-icon {

    font-size: 50px;

    margin-bottom: 15px;

}


.expired h2 {

    color: #dc2626;

}


.expired p {

    color: #666;

    margin-top: 12px;

    line-height: 1.6;

}


.forgot-link {

    display: inline-block;

    margin-top: 20px;

    padding: 12px 20px;

    background: #2563eb;

    color: white;

    text-decoration: none;

    border-radius: 8px;

}
.password-error {
    color: #dc2626;
    background: #fee2e2;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
    display: none;
    font-size: 14px;
}

.input-error {
    border-color: #dc2626 !important;
}

</style>

</head>


<body>


<div class="reset-card">


<?php if (!$validToken): ?>


    <!-- INVALID / EXPIRED TOKEN -->

    <div class="expired">

        <div class="expired-icon">
            ❌
        </div>

        <h2>
            Link Expired or Invalid
        </h2>

        <p>
            This password reset link is no longer
            valid.
        </p>

        <p>
            Password reset links are valid for
            <strong>15 minutes</strong>.
        </p>

        <a
            href="forgot_password.php?type=<?php echo htmlspecialchars($type); ?>"
            class="forgot-link"
        >
            Request New Link
        </a>

    </div>


<?php else: ?>


    <!-- RESET PASSWORD -->

    <div class="icon">

        <i class="fa-solid fa-key"></i>

    </div>


    <h2>
        Reset Password
    </h2>


    <p class="description">

        Create a new password for your account.

    </p>


    <?php if ($message != ""): ?>

        <div class="<?php
            echo $success ? 'success' : 'error';
        ?>">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <?php if (!$success): ?>


        <form method="POST" id="resetPasswordForm">

    <!-- NEW PASSWORD -->
    <div class="input-box">

        <input
            type="password"
            id="newPassword"
            name="new_password"
            placeholder="New Password"
            required
        >

        <i
            class="fa-solid fa-eye"
            onclick="togglePassword('newPassword', this)"
        ></i>

    </div>


    <!-- CONFIRM PASSWORD -->
    <div class="input-box">

        <input
            type="password"
            id="confirmPassword"
            name="confirm_password"
            placeholder="Confirm Password"
            required
        >

        <i
            class="fa-solid fa-eye"
            onclick="togglePassword('confirmPassword', this)"
        ></i>

    </div>


    <!-- PASSWORD ERROR -->
    <div id="passwordError" class="password-error"></div>


    <button
        type="submit"
        name="resetPassword"
        class="btn"
    >
        Reset Password
    </button>

</form>

    <?php else: ?>


        <a
            href="<?php echo $loginPage; ?>"
            class="login-link"
        >
            Go to Login
        </a>


    <?php endif; ?>


<?php endif; ?>


</div>


<script>

function togglePassword(
    inputId,
    icon
) {

    const input =
        document.getElementById(inputId);


    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove(
            "fa-eye"
        );

        icon.classList.add(
            "fa-eye-slash"
        );

    } else {

        input.type = "password";

        icon.classList.remove(
            "fa-eye-slash"
        );

        icon.classList.add(
            "fa-eye"
        );

    }

}

</script>
<script>

function togglePassword(inputId, icon) {

    const input = document.getElementById(inputId);

    if (input.type === "password") {

        input.type = "text";

        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");

    } else {

        input.type = "password";

        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");

    }

}


// Password validation

const form = document.getElementById("resetPasswordForm");

if (form) {

    form.addEventListener("submit", function(event) {

        const newPassword =
            document.getElementById("newPassword");

        const confirmPassword =
            document.getElementById("confirmPassword");

        const passwordError =
            document.getElementById("passwordError");


        // Clear previous error

        passwordError.style.display = "none";

        passwordError.innerHTML = "";

        newPassword.classList.remove("input-error");
        confirmPassword.classList.remove("input-error");


        // Check password length

        if (newPassword.value.length < 6) {

            event.preventDefault();

            passwordError.innerHTML =
                "Password must be at least 6 characters.";

            passwordError.style.display = "block";

            newPassword.classList.add("input-error");

            return;

        }


        // Check password match

        if (
            newPassword.value !==
            confirmPassword.value
        ) {

            event.preventDefault();

            passwordError.innerHTML =
                "❌ Passwords do not match.";

            passwordError.style.display = "block";

            newPassword.classList.add("input-error");
            confirmPassword.classList.add("input-error");

            return;

        }

    });

}

</script>

</body>

</html>