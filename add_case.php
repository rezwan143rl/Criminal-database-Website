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
    $case_title = $_POST['case_title'];
    $case_status = $_POST['case_status'];
    $victim_name = $_POST['victim_name'];
    $gender = $_POST['victim_Gender'];
    $victim_age = $_POST['victim_age'];
    $crime_description = $_POST['crime_description'];
    $open_date = $_POST['open_date'];

    // Insert case
    $sql = "INSERT INTO Cases (case_title, description, case_status, open_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $case_title, $crime_description, $case_status, $open_date);

    if ($stmt->execute()) {
        $case_id = $stmt->insert_id;

        // Insert victim linked to case
        $sql2 = "INSERT INTO Victims (full_name, sex, age ,case_id) VALUES (?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        

        $stmt2->bind_param("ssii", $victim_name, $gender,$victim_age, $case_id);
        $stmt2->execute();

        $message = "✅ Case and victim added successfully!";
    } else {
        $message = "❌ Error adding case: " . $conn->error;
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
    <nav>
        <a href="add_case.php">Add Case</a>
        <a href="update_case.php">Update Case</a>
        <a href="Search_case.php">Search Criminal</a>
        <a href="criminal_data.php">Criminal Data Search</a>
    </nav>
</header>

<div class="container">
    <h2>Add New Case</h2>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>

    <form method="POST" action="add_case.php" onsubmit="return validateForm()">
        <label for="case_title">Case Title</label>
        <input type="text" name="case_title" id="case_title" required>

        <label for="case_status">Case Status</label>
        <select name="case_status" id="case_status" required>
            <option value="">Select Status</option>
            <option value="Open">Open</option>
            <option value="Closed">Closed</option>
            <option value="ONGOING">Under Investigation</option>
        </select>

        <label for="victim_name">Victim Name</label>
        <input type="text" name="victim_name" id="victim_name" required>
         <label for="victim_Gender">Gender</label>
        <select name="victim_Gender" id="victim_Gender" required>
            <option value="">Select Gender</option>
            <option value="MALE">MALE</option>
            <option value="FEMALE">FEMALE</option>
        </select>
        <label for="victim_age">Victim Age</label>
        <input type="number" name="victim_age" id="victim_age" required>

        <label for="crime_description">Crime Description</label>
        <textarea name="crime_description" id="crime_description" rows="4" required></textarea>

        <label for="open_date">Open Date</label>
        <input type="date" name="open_date" id="open_date" required>

        <button type="submit">Add Case</button>
    </form>
</div>

<footer style="text-align:center; padding:15px; background:rgba(0,0,0,0.85);">
    <p>Developed by: Rezwan | Project ID: 311</p>
</footer>

<script>
function validateForm() {
    let title = document.getElementById("case_title").value;
    let victim = document.getElementById("victim_name").value;
    let desc = document.getElementById("crime_description").value;

    if (title === "" || victim === "" || desc === "") {
        alert("Please fill all required fields.");
        return false;
    }
    return true;
}
</script>

</body>
</html>
