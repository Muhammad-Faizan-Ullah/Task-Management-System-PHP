<?php
session_start();
include 'db.php';
if (!isset($_SESSION['admin_id'])) {
    header('location:login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task_id'])) {
    $taskId = $_POST['delete_task_id'];

    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);

    header('Location: admin_dashboard.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_task'])) {
    $userId = $_POST['assign_user_id'];
    $title = $_POST['task_title'];
    $description = $_POST['task_description'];
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$userId, $title, $description]);

    header('Location: admin_dashboard.php');
    exit();
}
$searchQuery = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$stmt = $pdo->prepare("SELECT users.username, tasks.id, tasks.title, tasks.status, tasks.description, tasks.image_path 
    FROM users 
    LEFT JOIN tasks ON users.id = tasks.user_id
    WHERE users.role = 'user' AND users.username LIKE ?");
$stmt->execute([$searchQuery]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'user'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Admin Dashboard</h2><hr/>
    <p><strong>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</strong></p>
    
    <form method="get" action="admin_dashboard.php" class="form-inline mb-3">
        <input type="text" name="search" class="form-control mr-2" placeholder="Search by username" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit" class="btn btn-success">Search</button>
    </form>
    <form method="post" action="admin_dashboard.php" class="mb-4">
        <div class="form-group">
            <label for="assign_user">Assign Task to User</label>
            <select name="assign_user_id" id="assign_user" class="form-control" required>
                <option value="">Select User</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="task_title">Task Title</label>
            <input type="text" name="task_title" id="task_title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="task_description">Task Description</label>
            <textarea name="task_description" id="task_description" class="form-control" required></textarea>
        </div>
        <button type="submit" name="assign_task" class="btn btn-success">Assign Task</button>
    </form>

    <h3>User Tasks</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Task Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Image</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['username']) ?></td>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td><?= htmlspecialchars($task['description']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                    <td>
                        <?php if (!empty($task['image_path'])): ?>
                            <img src="<?= htmlspecialchars($task['image_path']) ?>" alt="Task Image" style="max-width: 150px; max-height: 150px;"/>
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" action="admin_dashboard.php" onsubmit="return confirm('Are you sure you want to delete this task?');">
                            <input type="hidden" name="delete_task_id" value="<?= htmlspecialchars($task['id']) ?>">
                            <button type="submit" class="btn btn-danger">Delete Task</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <a href="logout.php" class="btn btn-secondary mt-3">Logout</a>
</div> 
</body>
</html>

