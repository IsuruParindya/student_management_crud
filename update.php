<?php
require_once "config.php";

$MY_NAME = "Parindya";

function clean($v) {
    $v = trim((string)$v);
    return ($v === '') ? null : $v;
}

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function showErrorPage(array $errors, string $backId) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Update Failed</title>
        <link rel="stylesheet" href="css/style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1>Update Failed</h1>

                <div class="error-box">
                    <ul>
                        <?php foreach ($errors as $er): ?>
                            <li><?= e($er) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="button-group">
                    <a class="btn-primary" href="edit.php?id=<?= urlencode($backId) ?>">Go Back</a>
                    <a class="btn-secondary" href="index.php">Student List</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/* ===== Read IDs (string) ===== */
$oldStudentID = isset($_POST["oldStudentID"]) ? trim((string)$_POST["oldStudentID"]) : "";
$newStudentID = clean($_POST["studentID"] ?? "");

if ($oldStudentID === "" || !$newStudentID) {
    showErrorPage(["Invalid student ID."], $oldStudentID ?: "");
    $conn->close();
    exit;
}

/* ===== Read fields ===== */
$firstName    = clean($_POST["firstName"] ?? "");
$lastName     = clean($_POST["lastName"] ?? "");
$birthDate    = clean($_POST["birthDate"] ?? "");
$email        = clean($_POST["email"] ?? "");
$city         = clean($_POST["city"] ?? "");
$courseName   = clean($_POST["courseName"] ?? "");
$enrolledYear = clean($_POST["enrolledYear"] ?? "");

/* createdBy is FORCED (ignore any posted value) */
$createdBy = $MY_NAME;

/* ===== Validation ===== */
$errors = [];
if (!$firstName) $errors[] = "First Name is required.";
if (!$lastName)  $errors[] = "Last Name is required.";

if (!empty($errors)) {
    showErrorPage($errors, $oldStudentID);
    $conn->close();
    exit;
}

/* ===== Permission check: ensure this oldStudentID belongs to Parindya ===== */
$perm = $conn->prepare("SELECT 1 FROM student WHERE studentID = ? AND createdBy = ? LIMIT 1");
if (!$perm) {
    showErrorPage(["Prepare failed: " . $conn->error], $oldStudentID);
    $conn->close();
    exit;
}
$perm->bind_param("ss", $oldStudentID, $MY_NAME);
$perm->execute();
$permRes = $perm->get_result();
$allowed = ($permRes && $permRes->num_rows > 0);
$perm->close();

if (!$allowed) {
    showErrorPage(["Access denied: You can only update your own records."], $oldStudentID);
    $conn->close();
    exit;
}

/* ===== If ID changed, ensure new ID isn't taken by someone else ===== */
if ($newStudentID !== $oldStudentID) {
    $chk = $conn->prepare("SELECT 1 FROM student WHERE studentID = ? LIMIT 1");
    if (!$chk) {
        showErrorPage(["Prepare failed: " . $conn->error], $oldStudentID);
        $conn->close();
        exit;
    }
    $chk->bind_param("s", $newStudentID);
    $chk->execute();
    $chkRes = $chk->get_result();
    $exists = ($chkRes && $chkRes->num_rows > 0);
    $chk->close();

    if ($exists) {
        showErrorPage(["Student ID already exists. Choose a different ID."], $oldStudentID);
        $conn->close();
        exit;
    }
}

/* ===== Update ONLY your row (createdBy=Parindya) ===== */
$stmt = $conn->prepare(
    "UPDATE student
     SET studentID=?, firstName=?, lastName=?, birthDate=?, email=?, city=?, courseName=?, enrolledYear=?, createdBy=?
     WHERE studentID=? AND createdBy=?"
);

if (!$stmt) {
    showErrorPage(["Prepare failed: " . $conn->error], $oldStudentID);
    $conn->close();
    exit;
}

$stmt->bind_param(
    "sssssssssss",
    $newStudentID,
    $firstName,
    $lastName,
    $birthDate,
    $email,
    $city,
    $courseName,
    $enrolledYear,
    $createdBy,
    $oldStudentID,
    $MY_NAME
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit;
}

$err = "Update failed: " . $stmt->error;
$stmt->close();
$conn->close();
showErrorPage([$err], $oldStudentID);
?>