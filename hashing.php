<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connect.php';

$sql = "SELECT officer_id, password FROM Officers";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $id = $row['officer_id'];
    $plain = $row['password'];

    // bcrypt hashes are always 60 characters
    if (strlen($plain) < 60 || strpos($plain, '$2y$') !== 0) {
        $hashed = password_hash($plain, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE Officers SET password=? WHERE officer_id=?");
        $update->bind_param("si", $hashed, $id);

        if ($update->execute()) {
            echo "✅ Officer $id password hashed.<br>";
        } else {
            echo "❌ Error updating officer $id: " . $update->error . "<br>";
        }
}

}

echo "All plain-text passwords converted to hashed!";
?>
