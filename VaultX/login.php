<?php
session_start();
include("db/config.php");
$message = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "❌ Please fill in all fields.";
    } else {
        // Query database matching columns: id, username, email, password
        $stmt = $conn->prepare("SELECT id, username, password FROM userlogin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            // Verify bcrypt password
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['username'];
                
                $stmt->close();
                header("Location: dashboard.html");
                exit();
            } else { 
                $message = "❌ Invalid Password"; 
            }
        } else { 
            $message = "❌ User Not Found"; 
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            font-family: 'Segoe UI', sans-serif; 
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
            border-radius: 8px; 
            padding: 40px; 
            width: 100%; 
            max-width: 400px; 
            background: rgba(0, 0, 0, 0.25); 
            backdrop-filter: blur(6px);
            position: relative;
            z-index: 1;
        }
        .container h2 { 
            margin-bottom: 20px; 
            text-align: center; 
            font-size: 2.5rem; 
            color: chocolate; 
        }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-size: 14px; color: floralwhite; }
        .input-group input { 
            width: 100%; 
            padding: 12px; 
            border-radius: 4px; 
            border: 1px solid #ccc; 
            font-size: 15px; 
            outline: none;
        }
        .input-group input:focus { border-color: #4facfe; }
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
            transition: background 0.2s;
        }
        .login-btn:hover { background: rgb(176, 87, 87); }
        .alert { 
            padding: 10px; 
            margin-bottom: 15px; 
            border-radius: 5px; 
            text-align: center; 
            color: white; 
            background: rgba(255, 0, 0, 0.35); 
            font-size: 14px;
        }
        .signup-link { 
            text-align: center; 
            margin-top: 20px; 
            font-size: 14px;
            color: #c99027; 
            position: relative;
            z-index: 10;
        }
        .signup-link a {
            color: #60a5fa !important;
            text-decoration: underline !important;
            cursor: pointer !important;
            font-weight: bold;
            display: inline-block;
            padding: 2px 4px;
        }
        .signup-link a:hover {
            color: #93c5fd !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <?php if (!empty($message)): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autocomplete="email">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            <button type="submit" name="login" class="login-btn">Log In</button>
            <p class="signup-link">
                Don't have an account? <a href="register.php">Sign up</a>
            </p>
        </form>  
    </div>
</body>
</html>