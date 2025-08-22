<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connect.php';

$message = "";

if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    $stmt = $conn->prepare("DELETE FROM Officers WHERE officer_id = ? AND LOWER(rank) != 'oc'");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "✅ Officer ID $delete_id deleted successfully!";
    } else {
        $message = "❌ Error deleting officer: " . $stmt->error;
    }
}

$branchFilter = isset($_GET['branch']) ? $_GET['branch'] : "";

// non-OC officers from view
if ($branchFilter) {
    $stmt = $conn->prepare("SELECT * FROM NonOC_Officers WHERE branch = ?");
    $stmt->bind_param("s", $branchFilter);
} else {
    $stmt = $conn->prepare("SELECT * FROM NonOC_Officers");
}

$stmt->execute();
$result = $stmt->get_result();
$officers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Officer - Police Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; color:white;}
        th, td { border: 1px solid #fff; padding: 8px; text-align: left;}
        th { background: rgba(0,0,0,0.7);}
        td { background: rgba(0,0,0,0.5);}
        input[type="number"], select { padding: 5px; margin: 5px 0;}
        /* General button style */
button, a.button {
    padding: 8px 15px;
    margin: 5px 0;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    color: #fff;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

/* Delete button */
button.delete {
    background-color: #ec0033ff;  /* Red for delete */
}

/* Hover effect */
button:hover {
    opacity: 0.85;
    transform: translateY(-1px);
}

    </style>
</head>
<body>

<header>
    <h1>Police Criminal Database Dashboard</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="add_officer.php">Add Officer</a>
        <a href="delete_officer.php">Delete Officer</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<form method="POST" onsubmit="return confirm('Are you sure you want to delete this officer?');">
    <label for="delete_id">Enter Officer ID to Delete:</label>
    <input type="number" name="delete_id" id="delete_id" required>
    <button type="submit" class="delete">Delete Officer</button>
</form>

<div class="container">
    <h2>Delete Officer</h2>

    <?php if ($message) echo "<p class='message'>$message</p>"; ?>

    <!-- Branch Filter -->
    <form method="GET">
        <label for="branch">Filter by Branch:</label>
        <select name="branch" id="branch" onchange="this.form.submit()">
            <option value="">All Branches</option>
            <option value="Dhaka" <?= ($branchFilter=="Dhaka")?'selected':'' ?>>Dhaka</option>
            <option value="Rajshahi" <?= ($branchFilter=="Rajshahi")?'selected':'' ?>>Rajshahi</option>
            <option value="Bogura" <?= ($branchFilter=="Bogura")?'selected':'' ?>>Bogura</option>
        </select>
    </form>

    <!-- Officers Table -->
    <table>
        <tr>
            <th>Officer ID</th>
            <th>Name</th>
            <th>Branch</th>
        </tr>
        <?php foreach($officers as $off): ?>
        <tr>
            <td><?= $off['officer_id'] ?></td>
            <td><?= $off['full_name'] ?></td>
            <td><?= $off['branch'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    
</div>

</body>
</html>
