<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = filter_input(INPUT_POST, 'title');
    $description = filter_input(INPUT_POST, 'description');
    $user_id = $_SESSION['user_id'];
    $status = filter_input(INPUT_POST, 'status');
    $taskId = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);

    
    if (empty($title) || empty($description)) {
        $message = "Task title and description cannot be empty!";
    } elseif (!empty($_FILES['image']['name'])) {
        $fileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if ($fileType != 'jpg') {
            $message = "Only JPG images are allowed!";
        } else {                 
            $targetDir= "uploads/";
            if(!is_dir($targetDir)){
                mkdir($targetDir,0755,true);
            }
             $fileName = uniqid() . "." . $fileType;
             $targetFilePath = $targetDir . $fileName;
           if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, image_path, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $description, $targetFilePath, 'pending']);
                $message = "Task assigned successfully with image!";
            } else {
                $message = "There was an error uploading the image.";
            }
        }
    } else {
        $message = "Please upload an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Task</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Assign Task</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Type your task" required />
        </div>
        <div class="form-group">
            <label for="description">Task Description:</label>
            <textarea name="description" id="description" class="form-control" placeholder="Enter task description" required></textarea>
        </div>
      

        <div class="form-group">
            <label for="image">Task Image (JPG only):</label>
            <input type="file" name="image" id="image" class="form-control-file" accept=".jpg" required />
        </div>
        <button type="submit" class="btn btn-primary">Assign Task</button>
    </form>
    <a href="user_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
