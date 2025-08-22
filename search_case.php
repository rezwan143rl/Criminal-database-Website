<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'connect.php';

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Search filter
$searchID = $_GET['search_id'] ?? '';

// Fetch cases with victims, criminals, evidence using LEFT JOINs
$sql = "
SELECT 
    c.case_id, c.case_title, c.case_status, c.description AS case_desc, c.open_date, c.close_date,
    v.victim_id, v.full_name AS victim_name, v.sex AS victim_sex,
    cr.criminal_id, cr.full_name AS criminal_name, cr.status AS criminal_status,
    e.ev_id, e.description AS evidence_desc, e.file_location
FROM cases c
LEFT JOIN victims v ON v.case_id = c.case_id
LEFT JOIN criminal_case cc ON cc.case_id = c.case_id
LEFT JOIN criminals cr ON cr.criminal_id = cc.criminal_id
LEFT JOIN evidence e ON e.case_id = c.case_id
";

if (!empty($searchID)) {
    $sql .= " WHERE c.case_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $searchID);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

// Organize data by case_id
$cases = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['case_id'];
    if (!isset($cases[$id])) {
        $cases[$id] = [
            'case_id' => $id,
            'case_title' => $row['case_title'],
            'case_status' => $row['case_status'],
            'case_desc' => $row['case_desc'],
            'open_date' => $row['open_date'],
            'close_date' => $row['close_date'],
            'victims' => [],
            'criminals' => [],
            'evidence' => []
        ];
    }

    if ($row['victim_id']) {
        $cases[$id]['victims'][$row['victim_id']] = [
            'name' => $row['victim_name'],
            'sex' => $row['victim_sex']
        ];
    }

    if ($row['criminal_id']) {
        $cases[$id]['criminals'][$row['criminal_id']] = [
            'name' => $row['criminal_name'],
            'status' => $row['criminal_status']
        ];
    }

    if ($row['ev_id']) {
        $cases[$id]['evidence'][$row['ev_id']] = [
            'desc' => $row['evidence_desc'],
            'file' => $row['file_location']
        ];
    }
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Cases</title>

    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="searchcase.css">
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
    <h1>            </h1>
    <h1>Search Cases</h1>

    <form method="GET">
        <input type="number" name="search_id" placeholder="Enter Case ID" value="<?= htmlspecialchars($searchID) ?>">
        <button type="submit">Search</button>
    </form>

    <ul id="caseList"></ul>

    <!-- Popup -->
    <div id="casePopup">
        <button id="closePopup">X</button>
        <h2 id="popupTitle"></h2>
        <p>Status: <span id="popupStatus"></span></p>
        <p>Description: <span id="popupDesc"></span></p>
        <p>Open Date: <span id="popupOpenDate"></span></p>
        <p>Close Date: <span id="popupCloseDate"></span></p>

        <h3>Victims:</h3>
        <ul id="popupVictims"></ul>

        <h3>Criminals:</h3>
        <ul id="popupCriminals"></ul>

        <h3>Evidence:</h3>
        <ul id="popupEvidence"></ul>
    </div>

    <script>
        const cases = <?= json_encode($cases) ?>;

        const caseList = document.getElementById('caseList');

        for (const caseId in cases) {
            const c = cases[caseId];
            const li = document.createElement('li');
            const link = document.createElement('a');
            link.href = "javascript:void(0)";
            link.textContent = c.case_id + " - " + c.case_title;
            link.onclick = () => openPopup(c.case_id);
            li.appendChild(link);
            caseList.appendChild(li);
        }

        const popup = document.getElementById('casePopup');
        const closeBtn = document.getElementById('closePopup');
        closeBtn.onclick = () => { popup.style.display = 'none'; };

        function openPopup(caseId) {
            const data = cases[caseId];
            document.getElementById('popupTitle').innerText = data.case_title;
            document.getElementById('popupStatus').innerText = data.case_status;
            document.getElementById('popupDesc').innerText = data.case_desc;
            document.getElementById('popupOpenDate').innerText = data.open_date;
            document.getElementById('popupCloseDate').innerText = data.close_date || 'N/A';

            const victimHTML = Object.values(data.victims)
                .map(v => `<li>${v.name} (${v.sex})</li>`).join('');
            document.getElementById('popupVictims').innerHTML = victimHTML || 'None';

            const criminalHTML = Object.values(data.criminals)
                .map(c => `<li>${c.name} (${c.status})</li>`).join('');
            document.getElementById('popupCriminals').innerHTML = criminalHTML || 'None';

            const evidenceHTML = Object.values(data.evidence)
                .map(e => `<li>${e.desc} ${e.file ? `<a href="${e.file}" target="_blank">View</a>` : ''}</li>`).join('');
            document.getElementById('popupEvidence').innerHTML = evidenceHTML || 'None';

            popup.style.display = 'block';
        }
    </script>
</body>
</html>
