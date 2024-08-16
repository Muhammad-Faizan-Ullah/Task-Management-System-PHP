<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$taskId = filter_input(INPUT_GET, 'task_id', FILTER_VALIDATE_INT);
if (!$taskId) {
    header('Location: my_tasks.php?message=Invalid task ID');
    exit();
}
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$taskId,$userId]);
$task = $stmt->fetch();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = filter_input(INPUT_POST, 'title');
    $description = filter_input(INPUT_POST, 'description');
    $status = filter_input(INPUT_POST, 'status');
    $uploadDir = 'uploads/';
    $imagePath = $task['image_path']; 
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $newImagePath = $uploadDir . $imageName;
    
        if (move_uploaded_file($imageTmpPath, $newImagePath)) {
            if (!empty($imagePath) && file_exists($imagePath)) {
                unlink($imagePath);
            }
            $imagePath = $newImagePath; 
        } else {
            $message = "Failed to upload image.";
        }
    }
    
    if ($title && $description && $status) {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, image_path = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $status, $imagePath, $taskId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            header('Location: my_tasks.php?message=Task updated successfully!');
            exit();
        } else {
            $message = "Failed to update task.";
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Task</h2>
    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>" required />
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($task['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" class="form-control" required>
                <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="in progress" <?= $task['status'] == 'in progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
        </div>
        <div class="form-group">
            <label for="image">Task Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*"    />
            <?php if (!empty($task['image_path'])): ?>
                <p>Current Image: <img src="<?= htmlspecialchars($task['image_path']) ?>" alt="Current Image" style="max-width: 100px; max-height: 100px;"></p>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Task</button>
        <a href="my_tasks.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
