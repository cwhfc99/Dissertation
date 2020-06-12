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
checkLogin('inviteTeacherForm');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        buildBanner();
        turnButtBlack('teacherButt');
        $updatedClassSelected = buildTeacherInterface($classSelected, null);

        $dbConn = getConnection();
        $SQLquery = "SELECT Class.name
                     FROM Class
                     WHERE Class.id_pk=:classID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('classID' => $updatedClassSelected));

        $className = $stmt->fetchObject();

        echo "<div class='classesMain'>";
        echo "<div class='deleteStatement'>Enter the username of the teacher you want to invite to manage class: " . $className->name."</div><br>";


      echo "<form id='inviteTeacherForm' action='inviteTeacherProcess.php' method='get' >
                <span>Teacher Username: <input type='text' name='teacherUsername' id='teacherUsername'></span><br>
                <span><input type='submit' name='submit' value='Invite Teacher' class='newClassButt' id='newClassButt'></span><br>
            </form>
           
        </div>
    ";

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

?>

    <script type="text/javascript">

        window.addEventListener('load', function() {
            'use strict';

            validateUsername();
            validateForm();
        });

        //Declares validated bool varibles as false.
        var usernameValidated = false;



        //Declares each form input as a variable.
        var usernameField = document.forms["inviteTeacherForm"]["teacherUsername"];
        var createAccButt = document.forms["inviteTeacherForm"]["submit"];

        //Adds event listening for each form input, upon user input.

        usernameField.addEventListener("input", validateUsername);

        function validateUsername() //Validates the first name.
        {
            usernameField.style.borderWidth = "2px";
            if (usernameField.value == "") //If the username field is empty
            {
                usernameValidated = false;
                usernameField.style.borderColor = "red";
            }
            else
            {
                usernameValidated = true;
                usernameField.style.borderColor = "green";
            }
            validateForm();
        }



        function validateForm() //Function to check all the fields are validated and enable to submit button.
        {
            if ((usernameValidated == true)) //Checks that all the validated bool variables are true.
            {
                createAccButt.style.display = 'block';
            }
            else
            {
                createAccButt.style.display = 'none';
            }
        }


    </script>

</body>
</html>
