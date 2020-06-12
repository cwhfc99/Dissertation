<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
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

require_once('functions.php');
checkLogin('classes');

$classCreateError = filter_has_var(INPUT_GET, 'classCreateError')
                        ? $_GET['classCreateError'] : 'false';
$classSelected = filter_has_var(INPUT_GET, 'classSelected')
    ? $_GET['classSelected'] : null;


if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        buildBanner();
        turnButtBlack('teacherButt');
        buildTeacherInterface($classSelected, null);
        buildNewClassForm($classCreateError);
    }
    else
    {
        echo "<p>You do not have permission to view this page.</p>";
    }
}
else
{
    notLoggedIn();
}

function buildNewClassForm($classCreateError)
{
    echo
    "
        <div class='classesMain'>";

        if ($classCreateError == 'true')
        {
            echo "<p>An error occurred and no class has been created.</p>";
        }

      echo "<form id='newClassForm' action='newClassRecord.php' method='get' >
                <span>Class Name: <input type='text' name='newClassInput' id='newClassInput'></span><br>
                <span><input type='submit' value='Create New Class' class='newClassButt' id='newClassButt'></span><br>
            </form>
           
        </div>
    ";
}
?>

<script type="text/javascript">
    window.addEventListener('load', function() {
        'use strict';


        validateInput();
        validateForm();
    });

    var bool = false;
    var input = document.getElementById('newClassInput');
    var butt = document.getElementById('newClassButt');

    input.addEventListener("input", validateInput);


    function validateInput()
    {
        input.style.borderWidth = "2px";
        if ((input.value) == "" || (input.value.length) > 20) //If the first name is empty or over 20 character.
        {
            bool = false;
            input.style.borderColor = "red";
        }
        else
        {
            bool = true;
            input.style.borderColor = "green";
        }
        validateForm();
    }

    function validateForm()
    {
        if (bool == false)
        {
            butt.disabled = true;
            butt.style.display = 'none';
        }
        else
        {
            butt.disabled = false;
            butt.style.display = 'block';

        }
    }

</script>

</body>

</html>