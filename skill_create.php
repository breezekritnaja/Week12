
<?php
require 'crud_bootstrap.php';

$error = '';
$name = '';
$level = 'Beginner';
$pid = profile_id($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string)($_POST['skill_name'] ?? ''));
    $level = trim((string)($_POST['skill_level'] ?? ''));

    if ($pid === 0 || $name === '' || mb_strlen($name) > 120 ||
        !in_array($level, ['Beginner', 'Intermediate', 'Advanced'], true)) {
        $error = 'Please enter a valid skill and level.';
    } else {
        $sql = 'INSERT INTO skill (profile_id, skill_name, skill_level)
                VALUES (?, ?, ?)';

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            $error = 'Could not prepare the insert query.';
        } else {
            mysqli_stmt_bind_param($stmt, 'iss', $pid, $name, $level);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: skills.php?success=created');
                exit;
            }

            $error = 'Insert failed. Please check the database.';
            mysqli_stmt_close($stmt);
        }
    }
}

page_start('INSERT | Add Skill');

if ($error !== '') {
    echo '<p class="err">' . h($error) . '</p>';
}
?>

<form method="post">
    <?php csrf_field(); ?>

    <label for="skill">Skill name</label>
    <input id="skill" name="skill_name" required maxlength="120"
           value="<?= h($name) ?>">

    <label for="level">Skill level</label>
    <select id="level" name="skill_level">
        <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $choice): ?>
            <option value="<?= h($choice) ?>"
                <?= $choice === $level ? 'selected' : '' ?>>
                <?= h($choice) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <p><button type="submit">Save new skill</button></p>
</form>

<?php page_end(); ?>
