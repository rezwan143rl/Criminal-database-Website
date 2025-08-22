<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Officers where user_name = ? ";
   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // $_SESSION['user_data'] = $row;
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['rank']=$row['rank'];

            $message = "✅ Welcome " . $row['full_name'] . " (" . $row['rank']. ")";
            echo $message;
            // You can redirect instead:
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "❌ Wrong password!";
            echo $message;
        }
    } else {
        $message = "❌ No such user found.";
        echo $message;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Criminal Database - Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <h1>Criminal Database</h1>
        <p>Access records securely</p>

        <form class="login-form" method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <div class="forgot-password">
            <a href="forgot.php">Forgot Password?</a> 
        </div>
    </div>

    
</body>
</html>


