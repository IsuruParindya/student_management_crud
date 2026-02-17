<?php
require_once "config.php";

$MY_NAME = "Parindya";

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$defaultID = "221004";
$firstName = "Isuru";
$lastName  = "Pigera";
$birthDate = "2000-11-18";
$email     = "parindyapigera@gmail.com";
$city      = "Kelaniya";
$course    = "BSc.IT";
$year      = 2022;
$createdBy = $MY_NAME;

$checkStmt = $conn->prepare("SELECT COUNT(*) AS total FROM student WHERE createdBy = ?");
if (!$checkStmt) die("Prepare failed: " . $conn->error);

$checkStmt->bind_param("s", $MY_NAME);
$checkStmt->execute();
$countRes = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

$total = (int)($countRes["total"] ?? 0);

if ($total === 0) {

    $stmt = $conn->prepare("
        INSERT INTO student
        (studentID, firstName, lastName, birthDate, email, city, courseName, enrolledYear, createdBy)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param(
        "sssssssis",
        $defaultID,
        $firstName,
        $lastName,
        $birthDate,
        $email,
        $city,
        $course,
        $year,
        $createdBy
    );
    $stmt->execute();
    $stmt->close();

} else {

    $fix = $conn->prepare("
        UPDATE student
        SET studentID = ?
        WHERE createdBy = ?
          AND (studentID = '0' OR studentID = '' OR studentID IS NULL)
        LIMIT 1
    ");

    if ($fix) {
        $fix->bind_param("ss", $defaultID, $MY_NAME);
        $fix->execute();
        $fix->close();
    }

    $fixCourse = $conn->prepare("
        UPDATE student
        SET courseName = ?
        WHERE createdBy = ?
          AND (courseName = '0' OR courseName = '' OR courseName IS NULL)
        LIMIT 1
    ");
    if ($fixCourse) {
        $fixCourse->bind_param("ss", $course, $MY_NAME);
        $fixCourse->execute();
        $fixCourse->close();
    }
}

$myNameEsc = $conn->real_escape_string($MY_NAME);

$sql = "
    SELECT studentID, firstName, lastName, birthDate, email, city, courseName, enrolledYear, createdBy
    FROM student
    ORDER BY (createdBy = '$myNameEsc') DESC, CAST(studentID AS UNSIGNED) ASC, studentID ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student List</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="card">

        <div class="header-row">
            <h1>Student Management List</h1>
            <a href="add.php" class="btn-primary">+ Add Student</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Birth Date</th>
                        <th>Student Email</th>
                        <th>City</th>
                        <th>Course</th>
                        <th>Enrolled Year</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($row['studentID']) ?></td>
                            <td><?= e($row['firstName'] . " " . $row['lastName']) ?></td>
                            <td><?= e($row['birthDate']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($row['city']) ?></td>
                            <td><?= e($row['courseName']) ?></td>
                            <td><?= e($row['enrolledYear']) ?></td>
                            <td><?= e($row['createdBy']) ?></td>

                            <td class="action-buttons">
                                <?php if ((string)$row['createdBy'] === (string)$MY_NAME): ?>
                                    <a href="edit.php?id=<?= urlencode($row['studentID']) ?>" class="btn-edit">Edit</a>
                                    <a href="delete.php?id=<?= urlencode($row['studentID']) ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Delete this student?');">
                                       Delete
                                    </a>
                                <?php else: ?>
                                    <span style="color:#999; font-style:italic;">No Permission</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="no-data">No students found.</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>