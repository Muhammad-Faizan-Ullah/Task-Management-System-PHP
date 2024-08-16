<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$userId = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['delete_task_id'])){
    $taskId = $_POST['delete_task_id'];
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$taskId,$userId]);
    header('Location:my_tasks.php');
    exit(); 
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'new_status');

    if ($task_id && $new_status) {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_status, $task_id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            header('Location: user_dashboard.php?message=Task status updated successfully!');
        } else {
            header('Location: user_dashboard.php?message=Failed to update task status.');
        }
    } else {
        header('Location: user_dashboard.php?message=Invalid input.');
    }
}
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ?");
$stmt->execute([$userId]);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>My Tasks</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Discription</th>
                <th>Status</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td><?= htmlspecialchars($task['description']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                    <td>
                        <?php if (!empty($task['image_path']) && file_exists($task['image_path'])): ?>
                            <img src="<?= htmlspecialchars($task['image_path']) ?>" alt="Task Image" style="max-width: 100px; max-height: 100px;">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                       
                        <form method="post" action="">
                            <input type="hidden" name="delete_task_id" value="<?= $task['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    
                    </td>
                    </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="user_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    <td>
    <a href="edit_task.php?task_id=<?= htmlspecialchars($task['id']) ?>" class="btn btn-warning btn">Edit</a>
</td>


</div>
</body>
</html>