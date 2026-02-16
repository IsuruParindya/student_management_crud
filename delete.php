<?php
require_once "config.php";

$MY_NAME = "Parindya";

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function showErrorPage(string $message) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Delete Failed</title>
        <link rel="stylesheet" href="css/style.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1>Delete Failed</h1>

                <div class="error-box">
                    <?= e($message) ?>
                </div>

                <div class="button-group">
                    <a class="btn-primary" href="index.php">Back to Student List</a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

$id = isset($_GET["id"]) ? trim((string)$_GET["id"]) : "";
if ($id === "") {
    showErrorPage("Invalid student ID.");
    $conn->close();
    exit;
}

$stmt = $conn->prepare("DELETE FROM student WHERE studentID = ? AND createdBy = ?");
if (!$stmt) {
    showErrorPage("Prepare failed: " . $conn->error);
    $conn->close();
    exit;
}

$stmt->bind_param("ss", $id, $MY_NAME);

if ($stmt->execute()) {

    if ($stmt->affected_rows === 0) {
        $stmt->close();
        $conn->close();
        showErrorPage("Access denied or record not found (you can only delete your own records).");
        exit;
    }

    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit;
}

$error = "Delete failed: " . $stmt->error;
$stmt->close();
$conn->close();
showErrorPage($error);
?>