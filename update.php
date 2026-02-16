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

$oldStudentID = isset($_POST["oldStudentID"]) ? trim((string)$_POST["oldStudentID"]) : "";
$newStudentID = clean($_POST["studentID"] ?? "");

if ($oldStudentID === "" || !$newStudentID) {
    showErrorPage(["Invalid student ID."], $oldStudentID ?: "");
    $conn->close();
    exit;
}

$firstName    = clean($_POST["firstName"] ?? "");
$lastName     = clean($_POST["lastName"] ?? "");
$birthDate    = clean($_POST["birthDate"] ?? "");
$email        = clean($_POST["email"] ?? "");
$city         = clean($_POST["city"] ?? "");
$courseName   = clean($_POST["courseName"] ?? "");
$enrolledYear = clean($_POST["enrolledYear"] ?? "");
$createdBy    = $MY_NAME;

$errors = [];
if (!$firstName) $errors[] = "First Name is required.";
if (!$lastName)  $errors[] = "Last Name is required.";

if (!empty($errors)) {
    showErrorPage($errors, $oldStudentID);
    $conn->close();
    exit;
}

if ($newStudentID !== $oldStudentID) {
    $chk = $conn->prepare("SELECT 1 FROM student WHERE studentID = ? AND createdBy = ? LIMIT 1");
    if (!$chk) {
        showErrorPage(["Prepare failed: " . $conn->error], $oldStudentID);
        $conn->close();
        exit;
    }
    $chk->bind_param("ss", $newStudentID, $MY_NAME);
    $chk->execute();
    $exists = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($exists) {
        showErrorPage(["Student ID already exists for your records."], $oldStudentID);
        $conn->close();
        exit;
    }
}

$yearInt = ($enrolledYear === null) ? null : (int)$enrolledYear;

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
    "sssssssisss",
    $newStudentID, $firstName, $lastName, $birthDate, $email, $city, $courseName, $yearInt, $createdBy,
    $oldStudentID, $MY_NAME
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