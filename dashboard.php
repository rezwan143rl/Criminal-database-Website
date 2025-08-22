<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connect.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$rank = $_SESSION['rank'];   
$name = $_SESSION['full_name'];   
$isOC = (strtolower($rank) === 'oc'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Police Dashboard</title>
   
</head>
<body>

<header>
    <h1>Police Criminal Database Dashboard</h1>
    <nav>
        <a href="add_case.php">Add Case</a>
        <a href="update_case.php">Update Case</a>
        <a href="Search_case.php">Search Criminal</a>
        <a href="criminal_data.php">Criminal Data Search</a>
    </nav>
</header>
<?php
if ($isOC == 'oc'){
?>
<div class="container">
    <div class="section">
        <h2>ADMIN</h2>
       <div class="oc-features">
    <a href="add_officer.php" class="button">Add Officer</a>
    <a href="delete_officer.php" class="button">Delete Officer</a>
    <a href="delete_evidence.php" class="button">Delete Evidence</a>
    <a href="update_criminal.php" class="button">Update Criminal Info</a>
    <a href="manage_cases.php" class="button">Manage Cases</a>
</div>

    </div>

</div>
<?php
}
?>

<footer>
    <p>Developed by:</p>
</footer>

</body>
</html>
