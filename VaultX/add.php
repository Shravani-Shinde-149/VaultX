<?php

session_start();
include("db/config.php");

$message = "";
$msg_type = "";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// AES-128 key
// IMPORTANT: For a real application, this should be derived
// securely from the user's master password.
$encryption_key = "1234567890123456"; // Exactly 16 characters

if (isset($_POST['save_credentials'])) {

    $site_name = trim($_POST['site_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (empty($site_name) || empty($username) || empty($password)) {

        $message = "❌ Please fill all fields!";
        $msg_type = "error";

    } else {

        // Generate random IV
        $iv = random_bytes(16);

        // AES-128-CBC encryption
        $encrypted_password = openssl_encrypt(
            $password,
            "AES-128-CBC",
            $encryption_key,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Convert encrypted data and IV to text
        $encrypted_password = base64_encode($encrypted_password);
        $iv = base64_encode($iv);

        // Store encrypted password + IV
        $stmt = $conn->prepare(
            "INSERT INTO credentials
            (user_id, site_name, username, password, iv)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issss",
            $user_id,
            $site_name,
            $username,
            $encrypted_password,
            $iv
        );

        if ($stmt->execute()) {

            $message = "✅ Credentials saved successfully!";
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Credentials</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    min-height: 100vh;
    background: #f4f7fb;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    width: 420px;
    background: white;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

h2 {
    text-align: center;
    color: #1e293b;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    color: #64748b;
    margin-bottom: 25px;
    font-size: 14px;
}

label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 18px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
    font-size: 15px;
}

input:focus {
    border-color: #2563eb;
}

button {
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 8px;
    background: #2563eb;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.message {
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 7px;
    text-align: center;
}

.success {
    background: #dcfce7;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
    color: #2563eb;
}

</style>

</head>

<body>

<div class="container">

<h2>🔐 Add Credentials</h2>

<p class="subtitle">
Save your website credentials securely
</p>

<?php if (!empty($message)) { ?>

<div class="message <?php echo $msg_type; ?>">
    <?php echo $message; ?>
</div>

<?php } ?>

<form method="POST">

<label>Site Name</label>

<input
    type="text"
    name="site_name"
    placeholder="Example: Gmail"
    required
>

<label>Username / Email</label>

<input
    type="text"
    name="username"
    placeholder="Enter username or email"
    required
>

<label>Password</label>

<input
    type="password"
    name="password"
    placeholder="Enter website password"
    required
>

<button type="submit" name="save_credentials">
    🔒 Save Credentials
</button>

</form>

<a href="dashboard.php" class="back">
    ← Back to Dashboard
</a>

</div>

</body>
</html>