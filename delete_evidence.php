<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "connect.php"; 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$msg = "";

if (isset($_GET['delete_id'])) {
    $deleteID = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM evidence WHERE ev_id = ?");
    $stmt->bind_param("i", $deleteID);
    if ($stmt->execute()) {
        $msg = "Evidence deleted successfully!";
    } else {
        $msg = "Error deleting evidence.";
    }
    $stmt->close();
}

// Fetch all evidence with case info
$sql = "
    SELECT e.ev_id, e.description, e.file_location, 
           c.case_id, c.case_title
    FROM evidence e
    JOIN cases c ON e.case_id = c.case_id
    ORDER BY c.case_id DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete Evidence</title>
<link rel="stylesheet" href="deleteevidence.css"> 
</head>
<body>
<header class="header">
    <h1>Delete Evidence</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="update_case.php">Update Case</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<div class="header-spacer"></div>

<div class="container">
    <?php if (!empty($msg)): ?>
        <p class="message"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Evidence ID</th>
                <th>Case ID</th>
                <th>Case Name</th>
                <th>Description</th>
                <th>File</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['ev_id'] ?></td>
                    <td><?= $row['case_id'] ?></td>
                    <td><?= htmlspecialchars($row['case_title']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <?php if (!empty($row['file_location'])): ?>
                            <a class="link" href="<?= htmlspecialchars($row['file_location']) ?>" target="_blank">View</a>
                        <?php else: ?>
                            No File
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="view-btn" href="delete_evidence.php?delete_id=<?= $row['ev_id'] ?>" onclick="return confirm('Are you sure you want to delete this evidence?');">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No evidence found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
