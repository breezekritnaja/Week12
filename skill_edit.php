
<?php
require 'crud_bootstrap.php';

$id = valid_id($_GET['id'] ?? $_POST['id'] ?? null);
$pid = profile_id($conn);
$error = '';
$row = null;

if (!$id) {
    http_response_code(400);
    exit('Invalid record ID');
}

$sql = 'SELECT skill_name, skill_level
        FROM skill WHERE id = ? AND profile_id = ? LIMIT 1';

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ii', $id, $pid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $oldName, $oldLevel);

    if (mysqli_stmt_fetch($stmt)) {
        $row = [
            'skill_name' => $oldName,
            'skill_level' => $oldLevel
        ];
    }

    mysqli_stmt_close($stmt);
}

if (!$row) {
    http_response_code(404);
    exit('Record not found');
}

$name = $row['skill_name'];
$level = $row['skill_level'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string)($_POST['skill_name'] ?? ''));
    $level = trim((string)($_POST['skill_level'] ?? ''));

    if ($name === '' || mb_strlen($name) > 120 ||
        !in_array($level, ['Beginner', 'Intermediate', 'Advanced'], true)) {
        $error = 'Please enter valid values.';
    } else {
        $sql = 'UPDATE skill
                SET skill_name = ?, skill_level = ?
                WHERE id = ? AND profile_id = ?';

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            $error = 'Could not prepare the update query.';
        } else {
            mysqli_stmt_bind_param(
                $stmt, 'ssii', $name, $level, $id, $pid
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: skills.php?success=updated');
                exit;
            }

            $error = 'Update failed. Please check the database.';
            mysqli_stmt_close($stmt);
        }
    }
}

page_start('UPDATE | Edit Skill');

if ($error !== '') {
    echo '<p class="err">' . h($error) . '</p>';
}
?>

<form method="post">
    <?php csrf_field(); ?>
    <input type="hidden" name="id" value="<?= h($id) ?>">

    <label for="skill">Skill name</label>
    <input id="skill" name="skill_name" required maxlength="120"
           value="<?= h($name) ?>">

    <label for="level">Level</label>
    <select id="level" name="skill_level">
        <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $choice): ?>
            <option value="<?= h($choice) ?>"
                <?= $choice === $level ? 'selected' : '' ?>>
                <?= h($choice) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p><button type="submit">Update skill</button></p>
</form>

<?php page_end(); ?>
