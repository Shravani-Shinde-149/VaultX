<?php
// Start session if you want to pass flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("db/config.php");

$message = "";
$msg_type = ""; 

// Preserve form values on failure
$old_username = "";
$old_email = "";

if (isset($_POST['register'])) {
    // 1. Sanitize and retrieve POST data
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    $old_username = $username;
    $old_email    = $email;

    // 2. Form Validations
    if (empty($username) || empty($email) || empty($password) || empty($cpassword)) {
        $message = "❌ All fields are required!";
        $msg_type = "error";
    }
    elseif (!preg_match("/^[a-zA-Z0-9_ -]{3,30}$/", $username)) {
        $message = "❌ Username must be 3-30 characters (letters, numbers, underscore, hyphens)!";
        $msg_type = "error";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format!";
        $msg_type = "error";
    }
    elseif (strlen($password) < 6) {
        $message = "❌ Password must be at least 6 characters!";
        $msg_type = "error";
    }
    elseif ($password !== $cpassword) {
        $message = "❌ Passwords do not match!";
        $msg_type = "error";
    }
    else {
        // 3. Check if email already exists in userlogin table
        $checkEmail = $conn->prepare("SELECT id FROM userlogin WHERE email = ? LIMIT 1");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $message = "❌ This email is already registered!";
            $msg_type = "error";
        } else {
            // 4. Securely hash password with bcrypt
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

            // 5. Insert into userlogin (matching your columns: username, email, password)
            $stmt = $conn->prepare("INSERT INTO userlogin (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_pass);

            if ($stmt->execute()) {
                $message = "✅ Registration Successful! <a href='login.php' style='color:#60a5fa; text-decoration:underline; font-weight:bold;'>Click here to Log In</a>";
                $msg_type = "success";
                // Clear fields after success
                $old_username = "";
                $old_email = "";
            } else {
                $message = "❌ Database Error: " . htmlspecialchars($stmt->error);
                $msg_type = "error";
            }
            $stmt->close();
        }
        $checkEmail->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(-45deg, #2947cc, #764ba2, #6213b2, #4facfe); 
            background-size: 400% 400%; 
            animation: gradientMove 10s ease infinite; 
            padding: 20px;
        }
        @keyframes gradientMove { 
            0% { background-position: 0% 50%; } 
            50% { background-position: 100% 50%; } 
            100% { background-position: 0% 50%; } 
        }
        .container { 
            border: 2px solid rgba(79, 78, 78, 0.25); 
            border-radius: 10px; 
            padding: 36px 32px; 
            width: 100%; 
            max-width: 420px; 
            background: rgba(0, 0, 0, 0.25); 
            backdrop-filter: blur(8px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .container h2 { 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 2.2rem; 
            color: chocolate; 
            letter-spacing: 0.5px;
        }
        .input-group { margin-bottom: 16px; }
        .input-group label { 
            display: block; 
            margin-bottom: 7px; 
            font-size: 13.5px; 
            color: floralwhite; 
            font-weight: 500;
        }
        .input-group input { 
            width: 100%; 
            padding: 11px 12px; 
            border-radius: 5px; 
            border: 1px solid #ddd; 
            font-size: 14.5px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-group input:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.3);
        }
        .login-btn { 
            width: 100%; 
            padding: 12px; 
            background: rgb(156, 77, 77); 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 16px; 
            margin-top: 6px;
            transition: background 0.2s, transform 0.1s;
        }
        .login-btn:hover { background: rgb(176, 87, 87); }
        .login-btn:active { transform: scale(0.99); }
        .alert { 
            padding: 11px; 
            margin-bottom: 16px; 
            border-radius: 6px; 
            text-align: center; 
            font-size: 13.5px;
            color: white; 
            background: rgba(255, 0, 0, 0.38); 
            border: 1px solid rgba(255, 0, 0, 0.4);
            line-height: 1.4;
        }
        .alert.success { 
            background: rgba(16, 185, 129, 0.35); 
            border-color: rgba(16, 185, 129, 0.5);
            color: #ecfdf5; 
        }
        .signup-link { 
            text-align: center; 
            margin-top: 18px; 
            font-size: 14px;
            color: #c99027; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        
        <?php if(!empty($message)): ?>
            <div class="alert <?php echo ($msg_type === 'success') ? 'success' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="input-group">
                <label>Username</label>
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Choose a username" 
                    value="<?php echo htmlspecialchars($old_username); ?>" 
                    required 
                    autocomplete="username"
                >
            </div>
            <div class="input-group">
                <label>Email</label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Enter your email" 
                    value="<?php echo htmlspecialchars($old_email); ?>" 
                    required 
                    autocomplete="email"
                >
            </div>
            <div class="input-group">
                <label>Password</label>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="At least 6 characters" 
                    required 
                    autocomplete="new-password"
                >
            </div>
            <div class="input-group">
                <label>Confirm Password</label>
                <input 
                    type="password" 
                    name="cpassword" 
                    placeholder="Confirm your password" 
                    required 
                    autocomplete="new-password"
                >
            </div>
            <button type="submit" name="register" class="login-btn">Sign Up</button>
            <p class="signup-link">Already have an account? <a href="login.php" style="color: #60a5fa; text-decoration: underline;">Log in</a></p>
        </form>  
    </div>
</body>
</html>