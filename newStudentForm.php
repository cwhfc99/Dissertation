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
checkLogin('newStudentForm');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        buildBanner();
        turnButtBlack('teacherButt');
        $updatedClassSelected = buildTeacherInterface($classSelected, null);

        if (!isset($_SESSION['classSelected']))
        {
            $_SESSION['classSelected'] = null;
        }

        $_SESSION['classSelected'] = $updatedClassSelected;


        if ($updatedClassSelected == null)
        {
            echo "You must create a class before you can create a student account.";
        }
        else
        {
        echo "
            <div>
                <!-- HTML for form to create a new teacher account -->
                <form id='newStudentForm' action='newStudentRecord.php' method='get' >
                    <span>First Name: <br><input type='text' name='first_name'></span><br>
                    <span>Last Name: <br><input type='text' name='last_name'></span><br>
                    <input class='butt' type='submit' name='submit' value='Create Account'>
                </form>
            </div>";
        }


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

        validateForm();
        validateFirstName();
        validateLastName();

    });

    //Declares validated bool varibles as false.
    var fnValidated = false;
    var lnValidated = false;


    //Declares each form input as a variable.
    var firstNameField = document.forms["newStudentForm"]["first_name"];
    var lastNameField = document.forms["newStudentForm"]["last_name"];
    var createAccButt = document.forms["newStudentForm"]["submit"];

    //Adds event listening for each form input, upon user input.

    firstNameField.addEventListener("input", validateFirstName);
    lastNameField.addEventListener("input", validateLastName);

    function validateFirstName() //Validates the first name.
    {
        firstNameField.style.borderWidth = "2px";
        if ((firstNameField.value) == "" || (firstNameField.value.length) > 20) //If the first name is empty or over 20 character.
        {
            fnValidated = false;
            firstNameField.style.borderColor = "red";
        }
        else
        {
            fnValidated = true;
            firstNameField.style.borderColor = "green";
        }
        validateForm();
    }

    function validateLastName() //Validates the last name.
    {
        lastNameField.style.borderWidth = "2px";
        if ((lastNameField.value) == "" || (lastNameField.value.length) > 20) //If the last name is empty or over 20 character.
        {
            lnValidated = false;
            lastNameField.style.borderColor = "red";
        }
        else
        {
            lnValidated = true;
            lastNameField.style.borderColor = "green";
        }
        validateForm();
    }

    function validateForm() //Function to check all the fields are validated and enable to submit button.
    {
        if ((fnValidated == true) && (lnValidated == true)) //Checks that all the validated bool variables are true.
        {
            createAccButt.disabled = false;
        }
        else
        {
            createAccButt.disabled = true;
        }
    }

</script>

</body>

</html>