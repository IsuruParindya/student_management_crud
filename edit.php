<?php
require_once "config.php";

$MY_NAME = "Parindya";

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET["id"]) ? trim((string)$_GET["id"]) : "";
if ($id === "") {
    die("Invalid student ID.");
}

$stmt = $conn->prepare("SELECT * FROM student WHERE studentID = ? AND createdBy = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ss", $id, $MY_NAME);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found or no permission.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="card">

        <h1>Edit Student</h1>

        <form method="POST" action="update.php">

            <input type="hidden" name="oldStudentID" value="<?= e($student['studentID']) ?>">

            <div class="form-group">
                <label>Student ID *</label>
                <input type="text" name="studentID" value="<?= e($student['studentID']) ?>" required>
            </div>

            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="firstName" value="<?= e($student['firstName']) ?>" required>
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="lastName" value="<?= e($student['lastName']) ?>" required>
            </div>

            <div class="form-group">
                <label>Birth Date</label>
                <input type="text" name="birthDate" value="<?= e($student['birthDate']) ?>" placeholder="YYYY-MM-DD">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($student['email']) ?>">
            </div>

            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" value="<?= e($student['city']) ?>">
            </div>

            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="courseName" value="<?= e($student['courseName']) ?>">
            </div>

            <div class="form-group">
                <label>Enrolled Year</label>
                <input type="text" name="enrolledYear" value="<?= e($student['enrolledYear']) ?>">
            </div>

            <input type="hidden" name="createdBy" value="<?= e($MY_NAME) ?>">

            <div class="button-group">
                <button type="submit" class="btn-primary">Update</button>
                <a href="index.php" class="btn-secondary">Back</a>
            </div>

        </form>

    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>