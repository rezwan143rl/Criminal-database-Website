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

$rank = $_SESSION['rank'];
$name = $_SESSION['full_name'];
$isOC = strtolower($rank) === 'oc';
$message = "";

// Handle search
$caseID = $_GET['case_id'] ?? null;
$caseData = null;
$criminalsList = [];
$evidenceList = [];
$victimsList = [];

if ($caseID) {
    $stmt = $conn->prepare("SELECT * FROM cases WHERE case_id=?");
    $stmt->bind_param("i", $caseID);
    $stmt->execute();
    $caseData = $stmt->get_result()->fetch_assoc();

    // Criminals
    $stmt = $conn->prepare("SELECT c.criminal_id, c.full_name, c.status, cc.role 
                            FROM criminal_case cc
                            JOIN criminals c ON c.criminal_id=cc.criminal_id
                            WHERE cc.case_id=?");
    $stmt->bind_param("i", $caseID);
    $stmt->execute();
    $criminalsList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Evidence
    $stmt = $conn->prepare("SELECT * FROM evidence WHERE case_id=?");
    $stmt->bind_param("i", $caseID);
    $stmt->execute();
    $evidenceList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Victims
    $stmt = $conn->prepare("SELECT * FROM victims WHERE case_id=?");
    $stmt->bind_param("i", $caseID);
    $stmt->execute();
    $victimsList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$allCriminals = [];
$result = $conn->query("SELECT criminal_id, full_name, gender, status FROM criminals ORDER BY full_name ASC");
if ($result) {
    $allCriminals = $result->fetch_all(MYSQLI_ASSOC);
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_criminal'])) {
    $criminal_name = $_POST['criminal_name'];
    $gender = $_POST['gender'];
    $status = $_POST['status'] ?? 'ONGOING';
    $role = $_POST['role'] ?? 'Suspect';
    
    $stmt = $conn->prepare("INSERT INTO criminals (full_name, gender, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $criminal_name, $gender, $status);
    $stmt->execute();
    $criminal_id = $stmt->insert_id;

    $stmt = $conn->prepare("INSERT INTO criminal_case (criminal_id, case_id, role) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $criminal_id, $caseID, $role);
    $stmt->execute();

    header("Location: update_case.php?case_id=$caseID");
    exit;
}

// Handle Linking Existing Criminal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['link_criminal'])) {
    $criminal_id = $_POST['criminal_id'];
    $role = $_POST['role'] ?? 'Suspect';
    $status = $_POST['status'] ?? 'ONGOING';

    $stmt = $conn->prepare("INSERT IGNORE INTO criminal_case (criminal_id, case_id, role) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $criminal_id, $caseID, $role);
    $stmt->execute();

    if ($isOC) {
        $stmt = $conn->prepare("UPDATE criminals SET status=? WHERE criminal_id=?");
        $stmt->bind_param("si", $status, $criminal_id);
        $stmt->execute();
    }

    header("Location: update_case.php?case_id=$caseID");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_evidence'])) {
    $evidence_desc = $_POST['evidence_desc'];
    
    // Handle file upload
    $file_location = null;
    if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] == 0) {
        $targetDir = "evidences/";
        
        // Ensure folder exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = basename($_FILES['evidence_file']['name']);
        $targetFile = $targetDir . time() . "_" . $fileName; // unique name
        
        if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $targetFile)) {
            $file_location = $targetFile;
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO evidence (case_id, description, file_location) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $caseID, $evidence_desc, $file_location);
    $stmt->execute();

    header("Location: update_case.php?case_id=$caseID");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Case - Police Database</title>
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="updatecase.css">
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
        <a href="search_case.php">Search Cases</a>
        <a href="criminal_data.php">Criminal Data Search</a>
        <a href="logout.php" class="logout">Logout</a>
    </nav>
</header>
<div class="header-spacer"></div>

<div class="container">
<h2>Update Case</h2>

<form method="GET">
    <label>Search Case by ID:</label>
    <input type="number" name="case_id" value="<?= htmlspecialchars($caseID) ?>" required>
    <button type="submit" class="button">Search</button>
</form>

<?php if ($caseData): ?>
<h3>Case Details</h3>
<p><strong>Title:</strong> <?= htmlspecialchars($caseData['case_title']) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($caseData['case_status']) ?></p>
<p><strong>Description:</strong> <?= htmlspecialchars($caseData['description']) ?></p>

<h3>Victims</h3>
<ul>
<?php foreach($victimsList as $v): ?>
<li><?= htmlspecialchars($v['full_name']) ?> (<?= htmlspecialchars($v['sex']) ?>)</li>
<?php endforeach; ?>
</ul>

<h3>Criminals 
  <button type="button" class="button" onclick="toggleCriminalForm()">Add New</button>
  <button type="button" class="button" onclick="openCriminalPopup()">Choose Existing</button>
</h3>
<ul>
<?php foreach($criminalsList as $c): ?>
<li><?= htmlspecialchars($c['full_name']) ?> - <?= htmlspecialchars($c['role']) ?> (<?= htmlspecialchars($c['status']) ?>)</li>
<?php endforeach; ?>
</ul>

<!-- Add New Criminal Form -->
<div id="addCriminalForm" class="toggleForm">
<h4>Add New Criminal</h4>
<form method="POST">
    <input type="hidden" name="add_criminal" value="1">
    <label>Name</label>
    <input type="text" name="criminal_name" required>
    <label>Gender</label>
    <select name="gender" required>
        <option value="">Select</option>
        <option value="MALE">MALE</option>
        <option value="FEMALE">FEMALE</option>
        <option value="INTERSEX">INTERSEX</option>
    </select>
    <label>Role</label>
    <input type="text" name="role" value="Suspect">
    <?php if($isOC): ?>
    <label>Status</label>
    <select name="status">
        <option value="ONGOING">ONGOING</option>
        <option value="IMPRISONED">IMPRISONED</option>
        <option value="FUGITIVE">FUGITIVE</option>
        <option value="FREE">FREE</option>
    </select>
    <?php endif; ?>
    <button type="submit" class="button">Add Criminal</button>
</form>
</div>

<!-- Choose Existing Criminal Popup -->
<div id="criminalPopup" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeCriminalPopup()">&times;</span>
    <h4>Select Existing Criminal</h4>
    <input type="text" id="criminalSearch" placeholder="Search criminal by name..." onkeyup="filterCriminals()">
    <ul id="criminalList">
      <?php foreach($allCriminals as $cr): ?>
        <li>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="link_criminal" value="1">
            <input type="hidden" name="criminal_id" value="<?= $cr['criminal_id'] ?>">
            <span><b><?= htmlspecialchars($cr['full_name']) ?></b> (<?= $cr['gender'] ?>, <?= $cr['status'] ?>)</span><br>
            <label>Role:</label>
            <input type="text" name="role" value="Suspect">
            <?php if($isOC): ?>
            <label>Status:</label>
            <select name="status">
                <option value="ONGOING">ONGOING</option>
                <option value="IMPRISONED">IMPRISONED</option>
                <option value="FUGITIVE">FUGITIVE</option>
                <option value="FREE">FREE</option>
            </select>
            <?php endif; ?>
            <button type="submit" class="button">Link</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<h3>Evidence <button type="button" class="button" onclick="toggleEvidenceForm()">Add Evidence</button></h3>
<ul>
<?php foreach($evidenceList as $e): ?>
    <li>
        <?php echo $e['file_location'] ?>
        <?= htmlspecialchars($e['description']) ?>
        <?php if (!empty($e['file_location'])): ?> 
            <button class="view-btn" onclick="showEvidence('<?= $e['file_location'] ?>')">View</button>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>

<!-- Evidence Modal -->
<div id="evidenceModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <div id="evidenceDisplay"></div>
  </div>
</div>

<div id="addEvidenceForm" class="toggleForm">
<h4>Add Evidence</h4>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="add_evidence" value="1">
    <label>Description</label>
    <textarea name="evidence_desc" required></textarea>
    <label>File</label>
    <input type="file" name="evidence_file">
    <button type="submit" class="button">Add Evidence</button>
</form>
</div>

<?php endif; ?>
</div>

<footer style="text-align:center; padding:15px; background:rgba(0,0,0,0.85);">
<p>Developed by: Rezwan , Shoronika , Rizwan , Rahi | Project ID: 311</p>
</footer>

<script>
function toggleCriminalForm() {
    var f = document.getElementById("addCriminalForm");
    f.style.display = f.style.display==="none"?"block":"none";
}
function toggleEvidenceForm() {
    var f = document.getElementById("addEvidenceForm");
    f.style.display = f.style.display==="none"?"block":"none";
}
function openCriminalPopup() {
    document.getElementById("criminalPopup").style.display = "block";
}
function closeCriminalPopup() {
    document.getElementById("criminalPopup").style.display = "none";
}
function filterCriminals() {
    let input = document.getElementById("criminalSearch").value.toLowerCase();
    let lis = document.querySelectorAll("#criminalList li");
    lis.forEach(li => {
        let text = li.innerText.toLowerCase();
        li.style.display = text.includes(input) ? "" : "none";
    });
}
function showEvidence(filePath) {
    let display = document.getElementById("evidenceDisplay");
    display.innerHTML = "";

    if (filePath.match(/\.(jpg|jpeg|png|gif)$/i)) {
        display.innerHTML = `<img src="${filePath}" alt="Evidence Image">`;
    } else if (filePath.match(/\.(mp4|webm|ogg)$/i)) {
        display.innerHTML = `<video controls><source src="${filePath}" type="video/mp4">Your browser does not support video.</video>`;
    } else {
        display.innerHTML = `<a href="${filePath}" target="_blank">Download Evidence</a>`;
    }

    document.getElementById("evidenceModal").style.display = "block";
}
function closeModal() {
    document.getElementById("evidenceModal").style.display = "none";
}
document.addEventListener('DOMContentLoaded', function () {
  const header = document.querySelector('header.header');
  const spacer = document.querySelector('.header-spacer');
  if (!header || !spacer) return;
  function setSpacerHeight() { spacer.style.height = header.offsetHeight + 'px'; }
  setSpacerHeight();
  window.addEventListener('resize', setSpacerHeight);
});
</script>

</body>
</html>
