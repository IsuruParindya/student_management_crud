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

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $studentID    = clean($_POST["studentID"] ?? "");
    $firstName    = clean($_POST["firstName"] ?? "");
    $lastName     = clean($_POST["lastName"] ?? "");
    $birthDate    = clean($_POST["birthDate"] ?? "");
    $email        = clean($_POST["email"] ?? "");
    $city         = clean($_POST["city"] ?? "");
    $courseName   = clean($_POST["courseName"] ?? "");
    $enrolledYear = clean($_POST["enrolledYear"] ?? "");

    $createdBy = $MY_NAME;

    if (!$studentID) $errors[] = "Student ID is required.";
    if (!$firstName) $errors[] = "First Name is required.";
    if (!$lastName)  $errors[] = "Last Name is required.";

    if ($studentID && !preg_match('/^(BSIT)?\d{4,10}$/i', $studentID)) {
        $errors[] = "Student ID format invalid. Example:221004";
    }

    if (empty($errors)) {

        // Prevent duplicate ID
        $check = $conn->prepare("SELECT studentID FROM student WHERE studentID=?");
        $check->bind_param("s", $studentID);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();
        $check->close();

        if ($exists) {
            $errors[] = "This Student ID already exists.";
        } else {

            $stmt = $conn->prepare(
                "INSERT INTO student
                (studentID, firstName, lastName, birthDate, email, city, courseName, enrolledYear, createdBy)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                $errors[] = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param(
                    "sssssssis",
                    $studentID, $firstName, $lastName, $birthDate,
                    $email, $city, $courseName, $enrolledYear, $createdBy
                );

                if ($stmt->execute()) {
                    header("Location: index.php");
                    exit;
                } else {
                    $errors[] = "Insert failed: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="card">

        <h1>Add New Student</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <ul>
                    <?php foreach ($errors as $er): ?>
                        <li><?= e($er) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Student ID *</label>
                <input type="text" name="studentID" placeholder="e.g. 221004"
                       value="<?= e($_POST['studentID'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="firstName" value="<?= e($_POST['firstName'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="lastName" value="<?= e($_POST['lastName'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Birth Date</label>
                <input type="text" name="birthDate" placeholder="YYYY-MM-DD"
                       value="<?= e($_POST['birthDate'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="courseName" value="<?= e($_POST['courseName'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Enrolled Year</label>
                <input type="text" name="enrolledYear" value="<?= e($_POST['enrolledYear'] ?? '') ?>">
            </div>

            <div class="button-group">
                <button type="submit" class="btn-primary">Save Student</button>
                <a href="index.php" class="btn-secondary">Back</a>
            </div>

        </form>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>