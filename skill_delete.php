
<?php
require 'crud_bootstrap.php';

$id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);
$pid = profile_id($conn);
$error = '';

if (!$id) {
    http_response_code(400);
    exit('Invalid record ID');
}

// ตรวจว่ารายการนี้เป็นของโปรไฟล์ที่เข้าสู่ระบบ
$sql = 'SELECT id FROM skill WHERE id = ? AND profile_id = ? LIMIT 1';
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    exit('Could not prepare query');
}

mysqli_stmt_bind_param($stmt, 'ii', $id, $pid);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$exists = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$exists) {
    http_response_code(404);
    exit('Record not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $sql = 'DELETE FROM skill WHERE id = ? AND profile_id = ?';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        $error = 'Could not prepare the delete query.';
    } else {
        mysqli_stmt_bind_param($stmt, 'ii', $id, $pid);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: skills.php?success=deleted');
            exit;
        }

        $error = 'Delete failed. Please check the database.';
        mysqli_stmt_close($stmt);
    }
}

page_start('DELETE | Confirm Skill Removal');

if ($error !== '') {
    echo '<p class="err">' . h($error) . '</p>';
}
?>

<p>Are you sure you want to delete skill record #<?= h($id) ?>?</p>

<form method="post">
    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?= h($id) ?>">

    <button type="submit" class="danger">Yes, delete this skill</button>
    <a href="skills.php">Cancel</a>
</form>

<?php page_end(); ?>
