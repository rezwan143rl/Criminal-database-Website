<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['Full_name'];
    $rank = $_POST['rank'];
    $branch = $_POST['Branch'];
    $username = $_POST['Username'];
    $password = $_POST['password'];
    $hashed_password= password_hash($password, PASSWORD_DEFAULT);
  

    $sql = "INSERT INTO Officers (full_name, rank, branch, user_name,password) VALUES (?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $rank, $branch, $username,$hashed_password);

    if ($stmt->execute()) {
        $officer_id = $stmt->insert_id;

       
        

        $message = "✅ officer added successfully!";
    } else {
        $message = "❌ Error adding officer: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Case - Police Database</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="add_case.css">
    
</head>
<body>

<header>
    <h1>Police Criminal Database Dashboard</h1>
    <nav class="navbar">
        <div class="nav-left">
            <a href="dashboard.php">Dashboard</a>
            <a href="add_case.php">Add Case</a>
            <a href="update_case.php">Update Case</a>
            <a href="Search_case.php">Search Cases</a>
            <a href="criminal_data.php">Criminal Data Search</a>
        </div>
        <div class="nav-right">
            <a href="logout.php">Logout</a>
        </div>
    </nav>
</header>


<div class="container">
    <h2>Add New officer</h2>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>

    <form method="POST" action="add_officer.php" onsubmit="return validateForm()">
        <label for="Full_name">Full Name</label>
        <input type="text" name="Full_name" id="Full_name" required>
        <select name="rank" id="rank" required>
            <option value="">Select Rank</option>
            <option value="SI">SI</option>
            <option value="ASI">ASI</option>
            <option value="CONSTABLE">CONSTABLE</option>
        </select>
        

        <select name="Branch" id="Branch" required>
            <option value="">Select Branch</option>
            <option value="Dhaka">Dhaka</option>
            <option value="Rajshahi">Rajshahi</option>
            <option value="Bogura">Bogura</option>
        </select>
        
        <label for="Username">USERNAME</label>
        <input type="text" name="Username" id="Username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter Password" required>



        <button type="submit">Add officer</button>
    </form>
</div>

<footer style="text-align:center; padding:15px; background:rgba(0,0,0,0.85);">
    <p>Developed by: Rezwan , Shoronika , Rizwan , Rahi | Project ID: 311</p>
</footer>

<script>
function validateForm() {
    let fullname = document.getElementById("Full_name").value;
    let username = document.getElementById("Username").value;
    let Branch = document.getElementById("Branch").value;
    let rank = document.getElementById("rank").value;
    let password = document.getElementById("password").value;
    

    if (fullname === "" || username === ""||Branch===""||rank===""||password==="") {
        alert("Please fill all required fields.");
        return false;
    }
    return true;
}
</script>

</body>
</html>
