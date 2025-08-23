<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connect.php';

$message = "";

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    // Prevent deleting OC officers
    $stmt = $conn->prepare("DELETE FROM Officers WHERE officer_id = ? AND LOWER(rank) != 'oc'");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = "✅ Officer ID $delete_id deleted successfully!";
    } else {
        $message = "❌ Cannot delete Officer ID $delete_id (maybe OC rank or not found).";
    }
}

// Branch filter
$branchFilter = isset($_GET['branch']) ? $_GET['branch'] : "";

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
        
        button {
            padding: 6px 12px;
            margin: 3px 0;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            transition: all 0.3s ease;
        }

        button.delete { background-color: #ec0033ff; } /* Red */
        button.delete:hover { opacity: 0.85; transform: translateY(-1px); }

        .message { margin-top: 15px; font-weight: bold; color: #2ecc71; }
    </style>
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
    <h2>Delete Officer</h2>

    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>


    <form method="GET">
        <label for="branch">Filter by Branch:</label>
        <select name="branch" id="branch" onchange="this.form.submit()">
            <option value="">All Branches</option>
            <option value="Dhaka" <?= ($branchFilter=="Dhaka")?'selected':'' ?>>Dhaka</option>
            <option value="Rajshahi" <?= ($branchFilter=="Rajshahi")?'selected':'' ?>>Rajshahi</option>
            <option value="Bogura" <?= ($branchFilter=="Bogura")?'selected':'' ?>>Bogura</option>
        </select>
    </form>

 
    <table>
        <tr>
            <th>Officer ID</th>
            <th>Name</th>
            <th>Branch</th>
            <th>Action</th>
        </tr>
        <?php foreach($officers as $off): ?>
        <tr>
            <td><?= $off['officer_id'] ?></td>
            <td><?= htmlspecialchars($off['full_name']) ?></td>
            <td><?= htmlspecialchars($off['branch']) ?></td>
            <td>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Are you sure you want to delete Officer ID <?= $off['officer_id'] ?>?');">
                    <input type="hidden" name="delete_id" value="<?= $off['officer_id'] ?>">
                    <button type="submit" class="delete">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
