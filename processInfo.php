<?php
    session_start();

    if (isset($_SESSION['username'])) 
    {
        if(isset($_POST['item']))
        {
            $newItem = $_POST['item'];
        }
        $connection = mysqli_connect("localhost", "root", "99391613512aZHU", "todolistdata");
        $username = $_SESSION['username'];
        $addTask = mysqli_query($connection, "INSERT INTO todolist (username, task) VALUES ('$username', '$newItem')");
 
        $task_list = $_SESSION['task_list'];
        array_push($task_list, $newItem);
        $_SESSION['task_list'] = $task_list;
    }
    else
    {

    }

    header("location:javascript://history.go(-1)");
?> 
