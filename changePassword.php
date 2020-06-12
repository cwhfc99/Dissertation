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
checkLogin('index');
if ($_SESSION['loggedIn'] == 'true')
{
    echo "<div>
        <!-- HTML for form to create a new teacher account -->
        <form id='changePassword' action='changePasswordProcess.php' method='get' >
            <h1 style='text-align: center; font-family: \"Arial Black\", Arial, sans-serif'>Change Password</h1>
            <span>Password: <br><input type='password' name='password'></span><br>
            <span>Re-type Password: <br><input type='password' name='retype_password'></span><br>
            <input class='butt' type='submit' name='submit' value='Change Password'>
        </form>
    </div>";
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
        validatePassword();
        validateRePassword();
    });

    //Declares validated bool variables as false.
    var passValidated = false;
    var rePassValidated = false;


    //Declares each form input as a variable.
    var passwordField = document.forms["changePassword"]["password"];
    var rePasswordField = document.forms["changePassword"]["retype_password"];
    var createAccButt = document.forms["changePassword"]["submit"];

    //Adds event listening for each form input, upon user input.
    passwordField.addEventListener("input", validatePassword);
    rePasswordField.addEventListener("input", validateRePassword);



    function validatePassword() //Validates the first password.
    {
        passwordField.style.borderWidth = "2px";
        if ((passwordField.value) == "" || (passwordField.value.length) < 8) //If the password name is empty or under 8 character.
        {
            passValidated = false;
            passwordField.style.borderColor = "red";
        }
        else
        {
            passValidated = true;
            passwordField.style.borderColor = "green";
        }
        validateForm();
    }

    function validateRePassword() //Validates the second password.
    {
        rePasswordField.style.borderWidth = "2px";
        if (!((rePasswordField.value) == (passwordField.value))) //If the second password name is empty or does not match the first.
        {
            rePassValidated = false;
            rePasswordField.style.borderColor = "red";
        }
        else
        {
            rePassValidated = true;
            rePasswordField.style.borderColor = "green";

        }
        if (rePasswordField.value == "")
        {
            rePassValidated = false;
            rePasswordField.style.borderColor = "red";
        }
        validateForm();
    }

    function validateForm() //Function to check all the fields are validated and enable to submit button.
    {
        if ((passValidated == true) && (rePassValidated == true)) //Checks that all the validated bool variables are true.
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