<?php
include 'db.php';
if(!isset($_SESSION['user_id'])){
    header('Location:login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ?');
$stmt->execute([$user_id]);
$task = $stmt->fetchAll();

?>
<h2>Your Tasks</h2>
<table border="1">
    <tr>
        <th>Description</th>
        <th>Completed</th>
    </tr>
    <?php foreach ($tasks as $task): ?>
        <tr>
            <td><?php echo htmlspecialchars($task['task_description']); ?></td>
            <td><?php echo $task['is_completed'] ? 'Yes' : 'No'; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
