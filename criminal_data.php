<?php
session_start();
include 'connect.php';

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Fetch criminals with case count
$sql = "
SELECT cr.criminal_id, cr.full_name, cr.status, COUNT(cc.case_id) AS case_count
FROM criminals cr
LEFT JOIN criminal_case cc ON cr.criminal_id = cc.criminal_id
GROUP BY cr.criminal_id
ORDER BY case_count DESC
";
$result = $conn->query($sql);

$criminals = [];
while ($row = $result->fetch_assoc()) {
    $criminals[$row['criminal_id']] = [
        'id' => $row['criminal_id'],
        'name' => $row['full_name'],
        'status' => $row['status'],
        'case_count' => $row['case_count'],
        'cases' => []  // we’ll fill later
    ];
}

// Fetch case details for each criminal
$sqlCases = "
SELECT cc.criminal_id, c.case_id, c.case_title, c.case_status, c.description
FROM criminal_case cc
JOIN cases c ON c.case_id = cc.case_id
";
$resultCases = $conn->query($sqlCases);

while ($row = $resultCases->fetch_assoc()) {
    $cid = $row['criminal_id'];
    if (isset($criminals[$cid])) {
        $criminals[$cid]['cases'][$row['case_id']] = [
            'id' => $row['case_id'],
            'title' => $row['case_title'],
            'status' => $row['case_status'],
            'desc' => $row['description']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Criminal Data Search</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="criminal_data.css">
</head>
<body>
<header class="header">
    <div class="header-left">
        <h1>Police Criminal Database Dashboard</h1>
    </div>
    <nav class="header-right">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_case.php">Add Case</a>
        <a href="update_case.php">Update Case</a>
        <a href="Search_case.php">Search Cases</a>
        <a href="criminal_data.php">Criminal Data Search</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>
<div class="header-spacer"></div>

<div class="container">
    <h2>Criminal Data Search</h2>
    <table>
        <thead>
            <tr>
                <th>Criminal ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Case Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($criminals as $cr): ?>
                <tr>
                    <td>
                        <a href="javascript:void(0)" onclick="openPopup(<?= $cr['id'] ?>)">
                            <?= htmlspecialchars($cr['id']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($cr['name']) ?></td>
                    <td><?= htmlspecialchars($cr['status']) ?></td>
                    <td><?= htmlspecialchars($cr['case_count']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Popup -->
<div id="criminalPopup" class="modal">
    <div class="modal-content">
        <span id="closePopup" class="close">&times;</span>
        <h2 id="popupName"></h2>
        <p>Status: <span id="popupStatus"></span></p>
        <p>Total Cases: <span id="popupCaseCount"></span></p>
        <h3>Related Cases:</h3>
        <ul id="popupCases"></ul>
    </div>
</div>

<script>
    const criminals = <?= json_encode($criminals) ?>;
    const popup = document.getElementById('criminalPopup');
    const closeBtn = document.getElementById('closePopup');

    function openPopup(id) {
        const c = criminals[id];
        document.getElementById('popupName').innerText = c.name + " (ID: " + c.id + ")";
        document.getElementById('popupStatus').innerText = c.status;
        document.getElementById('popupCaseCount').innerText = c.case_count;

        let caseHTML = '';
        for (const k in c.cases) {
            const ca = c.cases[k];
            caseHTML += `<li><b>${ca.id}</b> - ${ca.title} [${ca.status}]<br>${ca.desc}</li>`;
        }
        document.getElementById('popupCases').innerHTML = caseHTML || "No related cases found.";

        popup.style.display = 'block';
    }

    closeBtn.onclick = () => { popup.style.display = 'none'; };
    window.onclick = (e) => { if (e.target == popup) popup.style.display = 'none'; };
</script>
</body>
</html>
