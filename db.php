<?php
$host = 'localhost';
$database = 'task_managment';
$user = 'root';
$password = '';
try{
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8",$user,$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
echo 'Connection failed' . $e->getMessage();
}
?>