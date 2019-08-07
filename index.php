<?php
    $connection = mysqli_connect("localhost", "root", "", "todolistdata");
    $username = "";
    $password = "";
    $valid_login = "false";
    $task_list = array();

    if(isset($_POST['user'])){
        $username = $_POST['user'];
    }
    else if (isset($_SESSION['username']))
    {
        $username = $_SESSION['username'];
    }
    if(isset($_POST['user'])){
        $password = $_POST['pass'];
    }
    else if (isset($_SESSION['password']))
    {
        $password = $_SESSION['password'];
    }
     // to prevent mysql injection
     $username = stripcslashes($username);
     $password = stripcslashes($password);
     $username = mysqli_real_escape_string($connection, $username);
     $password = mysqli_real_escape_string($connection, $password);

    $result = mysqli_query($connection, "SELECT * FROM users WHERE username = '$username' AND password = '$password'", MYSQLI_USE_RESULT) OR DIE("Failed to execute query ".mysql_error());
    $row = mysqli_fetch_array($result);

    if ($row['username'] == $username && $row['password'] == $password && $row['username'] != "" && $row['password'] != "")
    {
        session_start();
        mysqli_free_result($result);
        $task_result = mysqli_query($connection, "SELECT task FROM todolist WHERE username = '$username'") OR DIE("Failed to execute query".mysql_error());
        $task_list = array();

        while ($task_row = mysqli_fetch_assoc($task_result))
        {
            foreach ($task_row as $value)
            {
                array_push($task_list, $value);
            }
        }
        mysqli_free_result($task_result);
        $valid_login = "true";
        $_SESSION['connection'] = $connection;
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        $_SESSION['task_list'] = $task_list;
    }
    else if ($row['username'] == $username && $row['password'] != $password)
    {
        $valid_login = "wrong pass";
    }
    else
    {
        mysqli_free_result($result);
        $newUser = mysqli_query($connection, "INSERT INTO users (username, password) VALUES ('$username', '$password')");
        $valid_login = "new account";
    }
?> 


<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Todo List App</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    </head>
    
    <body>
        
        <header>
            <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
            <form method = "POST" action = "processInfo.php">
                <input type="text" placeholder="Enter an activity..." id="item" name = "item">
                    <button id="addItem">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"viewBox="0 0 16 16" style="enable-background:new 0 0 16 16;" xml:space="preserve"><g><path class="fill" d="M16,8c0,0.5-0.5,1-1,1H9v6c0,0.5-0.5,1-1,1s-1-0.5-1-1V9H1C0.5,9,0,8.5,0,8s0.5-1,1-1h6V1c0-0.5,0.5-1,1-1s1,0.5,1,1v6h6C15.5,7,16,7.5,16,8z"/></g></svg></button>
            </form>
        </header>
        <div class="taskList">
            <ul class ="uncompleted" id="uncompleted"></ul>
            <ul class = "completed" id="completed"></ul>
        </div>
        <div style="top:0px">
            <form method = "POST" action ="index.php" style="left:60%; top:0px; position:absolute;"> 
                    <h1>Login or Create Account</h1>
                    <p><label>Username:</label>
                    <input type="text" id = "user" name = "user"/></p>

                    <p><label>Password:</label>
                    <input type="password" id = "pass" name = "pass"/></p>

                    <p><input type="Submit" id = "submit" name = "Login"/></p>
            </form>
        </div>
<script>
var user = "";
var data = (localStorage.getItem("toDoList")) ? JSON.parse(localStorage.getItem("toDoList")) : {
    toDo: []
};
var task_list = data;

// SVG string of remove and finish buttons
var removeSVG = '<svg class="fill" style="enable-background:new 0 0 22 22"version=1.1 viewBox="0 0 22 22"x=0px xml:space=preserve xmlns=http://www.w3.org/2000/svg xmlns:xlink=http://www.w3.org/1999/xlink y=0px><g><path class="fill" d="M16.1,3.6h-1.9V3.3c0-1.3-1-2.3-2.3-2.3h-1.7C8.9,1,7.8,2,7.8,3.3v0.2H5.9c-1.3,0-2.3,1-2.3,2.3v1.3c0,0.5,0.4,0.9,0.9,1v10.5c0,1.3,1,2.3,2.3,2.3h8.5c1.3,0,2.3-1,2.3-2.3V8.2c0.5-0.1,0.9-0.5,0.9-1V5.9C18.4,4.6,17.4,3.6,16.1,3.6z M9.1,3.3c0-0.6,0.5-1.1,1.1-1.1h1.7c0.6,0,1.1,0.5,1.1,1.1v0.2H9.1V3.3z M16.3,18.7c0,0.6-0.5,1.1-1.1,1.1H6.7c-0.6,0-1.1-0.5-1.1-1.1V8.2h10.6L16.3,18.7L16.3,18.7z M17.2,7H4.8V5.9c0-0.6,0.5-1.1,1.1-1.1h10.2c0.6,0,1.1,0.5,1.1,1.1V7z"/></g><g><g><path class="fill" d=M11,18c-0.4,0-0.6-0.3-0.6-0.6v-6.8c0-0.4,0.3-0.6,0.6-0.6s0.6,0.3,0.6,0.6v6.8C11.6,17.7,11.4,18,11,18z /></g><g><path class="fill" d=M8,18c-0.4,0-0.6-0.3-0.6-0.6v-6.8C7.4,10.2,7.7,10,8,10c0.4,0,0.6,0.3,0.6,0.6v6.8C8.7,17.7,8.4,18,8,18z /></g><g><path class="fill" d="M14,18c-0.4,0-0.6-0.3-0.6-0.6v-6.8c0-0.4,0.3-0.6,0.6-0.6c0.4,0,0.6,0.3,0.6,0.6v6.8C14.6,17.7,14.3,18,14,18z"/></g></g></svg>';
var finishSVG = '<svg class= "fill" style="enable-background:new 0 0 22 22"version=1.1 viewBox="0 0 22 22"x=0px xml:space=preserve xmlns=http://www.w3.org/2000/svg xmlns:xlink=http://www.w3.org/1999/xlink y=0px><rect class="noFill" height=22 width=22 y=0 /><g><path class="fill" d="M9.7,14.4L9.7,14.4c-0.2,0-0.4-0.1-0.5-0.2l-2.7-2.7c-0.3-0.3-0.3-0.8,0-1.1s0.8-0.3,1.1,0l2.1,2.1l4.8-4.8c0.3-0.3,0.8-0.3,1.1,0s0.3,0.8,0,1.1l-5.3,5.3C10.1,14.3,9.9,14.4,9.7,14.4z"/></g></svg>';
processSubmission();

// Add an uncompleted item is valid input
document.getElementById("addItem").addEventListener("click", function() {
    var value = document.getElementById("item").value;

    if (value && data.toDo.indexOf(value) < 0)
        {
            addItem(value);
        }
});

document.getElementById("item").addEventListener("keydown", function (key) {
    var value = this.value;
    if (key.code === "Enter" && value && data.toDo.indexOf(value) < 0)
    {
        // add item to user's database
        addItem(value);
    }
});


function loadData() {
    for (var i = 0; i < task_list.length; i++)
    {
        var value = task_list[i];
        addItemToUncompleted(value);
    } 
}

function updateData() {
    localStorage.setItem("toDoList", JSON.stringify(data));
}

function addItem(value) {
    addItemToUncompleted(value);
    data.toDo.push(value);
    updateData();
}

// Trash item
function removeItem() {
    // var item = this.parentNode.parentNode; // li, buttons
    // var parent = item.parentNode; // ul, li
    // var value = item.innerText; // innertext, 

    var item = this.parentNode.parentNode.parentNode;
    var parent = item.parentNode;
    var value = item.innerText;

    data.toDo.splice(data.toDo.indexOf(value), 1);
    parent.removeChild(item);
    updateData();
}


// Move item to top or bottom of list when completed or uncompleted
function completeOrUncompleteItem() {
        var item = this.parentNode.parentNode;
        var parent = item.parentNode;
        var value = item.innerText;
        var target = document.getElementById("uncompleted");
    parent.removeChild(item);
    var buttonStyle = item.childNodes[1].childNodes[1].childNodes[0].style;

    if (buttonStyle.fill != "rgb(255, 255, 255)")
    {
        buttonStyle.fill = "rgb(255, 255, 255)";
        buttonStyle.background = "rgb(45, 173, 196)";
        target.insertBefore(item, target.childNodes[target.childElementCount]);
    }
    else
    {
        buttonStyle.fill = "rgb(45, 173, 196)";
        buttonStyle.background = "rgb(255, 255, 255)";
        target.insertBefore(item, target.childNodes[0]);
    }
}


// Add uncompleted item it to do list
function addItemToUncompleted(value) {
    var list = document.getElementById("uncompleted");

    var item = document.createElement("li");
    item.innerText = value;
    item.classList.add("task");

    var buttons = document.createElement("div");
    buttons.classList.add("buttons");

    var removeForm = document.createElement("form");
    removeForm.setAttribute('method', 'POST');

    var input = document.createElement("input");

    input.setAttribute('type', 'submit');
    removeForm.setAttribute('action', 'removeInfo.php');
    input.classList.add('hide_button');

    var remove = document.createElement("button");
    remove.classList.add("remove");
    remove.innerHTML = removeSVG;
    remove.addEventListener("click", removeItem);

    var finish = document.createElement("button");
    finish.classList.add("finish");
    finish.innerHTML = finishSVG;
    finish.addEventListener("click", completeOrUncompleteItem);

    //document.body.appendChild(removeForm);
    // var input = document.createElement("input");
    // input.setAttribute('type', 'submit');
    //         remove.appendChild(input);

    //removeForm.appendChild(remove);
    input.appendChild(remove);
    removeForm.appendChild(input);
    buttons.appendChild(removeForm);
    buttons.appendChild(finish);
    item.appendChild(buttons);
    list.insertBefore(item, list.childNodes[0]);
}

function processSubmission()
{
    user = "<?php echo $username; ?>"
    valid_login = "<?php echo $valid_login; ?>";
    if (valid_login === "true")
    {
        //if (valid_login === "true" && )
        welcome();
    }
    else if (user === "" && valid_login === "false")
    {
        user = "Guest";
        task_list = data;
        welcome();
    }
    else if (valid_login === "wrong pass")
    {
        alert("Incorrect password");
    }
    else
    {
        alert("User not found. Created new user with username: " + user);
    }
}


function welcome()  
{
    task_list = JSON.parse('<?php echo JSON_encode($task_list);?>');
    loadData();
    alert("Welcome " + user);


    // for (var i = 0; i < 2; i++)
    // {
    //     console.log(task_list[i]);
    // }
    // console.log('<?php echo JSON_encode($_SESSION['task_list']);?>');
    // console.log(data.toDo); // equal to task_list
}
    </script>
    </body>
</html>