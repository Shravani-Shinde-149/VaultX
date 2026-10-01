<?php

session_start();

include("db/config.php");

$message = "";
$msg_type = "";

// Check whether user is logged in
if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION['user_id'];


if (isset($_POST['masterpass'])) {

    // Get form data
    $password  = $_POST['mpass'] ?? '';
    $cpassword = $_POST['cmpass'] ?? '';


    // Validate password length
    if (strlen($password) < 6) {

        $message = "❌ Password must be at least 6 characters!";
        $msg_type = "error";

    }

    // Check password confirmation
    elseif ($password !== $cpassword) {

        $message = "❌ Passwords do not match!";
        $msg_type = "error";

    }

    else {

        // Hash master password
        $hashed_pass = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        // Insert user ID + master password
        $stmt = $conn->prepare(
            "INSERT INTO masterpassword (user_id, masterpass)
             VALUES (?, ?)"
        );

        $stmt->bind_param(
            "is",
            $user_id,
            $hashed_pass
        );


        if ($stmt->execute()) {

            $message = "✅ Master Password Generated Successfully!";
            $msg_type = "success";

        } else {

            $message = "❌ Database Error: " .
                       htmlspecialchars($stmt->error);

            $msg_type = "error";
        }


        $stmt->close();

    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Master Password</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            background:
                linear-gradient(
                    135deg,
                    #0f2027,
                    #203a43,
                    #2c5364
                );

            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 440px;
        }

        .card {
            background: rgba(255, 255, 255, 0.97);

            padding: 40px 35px;

            border-radius: 24px;

            box-shadow:
                0 25px 60px
                rgba(0, 0, 0, 0.35);

            animation: appear 0.7s ease;
        }

        @keyframes appear {

            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }

        }

        /* Lock Icon */

        .icon {
            width: 85px;
            height: 85px;

            margin: 0 auto 20px;

            display: flex;
            justify-content: center;
            align-items: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #667eea,
                    #764ba2
                );

            font-size: 40px;

            box-shadow:
                0 10px 25px
                rgba(102, 126, 234, 0.4);

            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {

            0%, 100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }

        }

        /* Heading */

        h1 {
            text-align: center;

            color: #1f2937;

            font-size: 28px;

            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;

            color: #6b7280;

            font-size: 14px;

            line-height: 1.6;

            margin-bottom: 30px;
        }

        /* Form */

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;

            color: #374151;

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 9px;
        }

        .input-box {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            font-size: 18px;
        }

        input[type="password"] {
            width: 100%;

            height: 52px;

            padding: 0 15px 0 45px;

            border: 1.5px solid #d1d5db;

            border-radius: 12px;

            background: #f9fafb;

            color: #111827;

            font-size: 15px;

            outline: none;

            transition: 0.3s;
        }

        input[type="password"]::placeholder {
            color: #9ca3af;
        }

        input[type="password"]:focus {
            background: white;

            border-color: #667eea;

            box-shadow:
                0 0 0 4px
                rgba(102, 126, 234, 0.12);
        }

        /* Button */

        button {
            width: 100%;

            height: 54px;

            border: none;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #667eea,
                    #764ba2
                );

            color: white;

            font-size: 16px;

            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 8px 20px
                rgba(102, 126, 234, 0.35);

            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 12px 25px
                rgba(102, 126, 234, 0.45);
        }

        button:active {
            transform: translateY(0);
        }

        /* Security Box */

        .security-box {
            margin-top: 25px;

            padding: 15px;

            display: flex;

            gap: 10px;

            align-items: flex-start;

            background: #f3f4f6;

            border: 1px solid #e5e7eb;

            border-radius: 12px;
        }

        .security-icon {
            font-size: 20px;
        }

        .security-text {
            color: #6b7280;

            font-size: 12px;

            line-height: 1.6;
        }

        .security-text strong {
            color: #374151;
        }

        /* Footer */

        .footer {
            text-align: center;

            margin-top: 20px;

            color: #9ca3af;

            font-size: 12px;
        }

        /* Mobile */

        @media (max-width: 480px) {

            body {
                padding: 15px;
            }

            .card {
                padding: 30px 22px;

                border-radius: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .icon {
                width: 75px;
                height: 75px;

                font-size: 34px;
            }

        }

    </style>

</head>


<body>

    <div class="container">

        <div class="card">

            <!-- Lock Icon -->

            <div class="icon">
                🔐
            </div>


            <!-- Heading -->

            <h1>
                Master Password
            </h1>

            <p class="subtitle">
                Create a strong master password to
                protect your sensitive information.
            </p>


            <!-- Form -->

            <form action="maspass.php" method="POST">


                <!-- Master Password -->

                <div class="form-group">

                    <label for="mpass">
                        Enter Master Password
                    </label>

                    <div class="input-box">

                        <span class="input-icon">
                            🔑
                        </span>

                        <input
                            type="password"
                            id="mpass"
                            name="mpass"
                            placeholder="Enter master password"
                            minlength="6"
                            required
                        >

                    </div>

                </div>


                <!-- Confirm Password -->

                <div class="form-group">

                    <label for="cmpass">
                        Confirm Master Password
                    </label>

                    <div class="input-box">

                        <span class="input-icon">
                            🔒
                        </span>

                        <input
                            type="password"
                            id="cmpass"
                            name="cmpass"
                            placeholder="Confirm master password"
                            minlength="6"
                            required
                        >

                    </div>

                </div>


                <!-- Submit -->

                <button
                    type="submit"
                    name="masterpass">

                    🔐 Generate Master Password

                </button>


            </form>


            <!-- Security Information -->

            <div class="security-box">

                <div class="security-icon">
                    🛡️
                </div>

                <div class="security-text">

                    <strong>Secure Password</strong>

                    <br>

                    Your password will be securely
                    processed and protected before
                    being stored.

                </div>

            </div>


            <div class="footer">

                Secure Password Management System

            </div>

        </div>

    </div>

</body>

</html>
