<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 17:25
 */

ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>
<?php

    require_once("functions.php");
    //$_SESSION['redirect'] = 'index';
    checkLogin('index');

    echo
    "    <div class='indexImageHolder'><img src='images/loading_screen_icon.png' width='300px' height='300px'></div>
    <hr>
        <a href='menu.php'><button class='playGameButt'>Play Game</button></a>
        <a href='newTeacherForm.php'><button class='newAccountButt'>Create New Teacher Account</button></a>
    ";

?>
</body>
</html>

