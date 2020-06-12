<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:43
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
        checkLogin("newTeacherForm")

    ?>

    <div>
        <!-- HTML for form to create a new teacher account -->
        <form id='newTeacherForm' action='newTeacherRecord.php' method='get' >
            <span>Title<br>
                <select id="title" name="title">
                    <option value="none">Please Select</option>
                    <option value="mr">Mr.</option>
                    <option value="mrs">Mrs.</option>
                    <option value="ms">Ms.</option>
                </select>
            </span><br>
            <span>First Name: <br><input type='text' name='first_name'></span><br>
            <span>Last Name: <br><input type='text' name='last_name'></span><br>
            <span>Password: <br><input type='password' name='password'></span><br>
            <span>Re-type Password: <br><input type='password' name='retype_password'></span><br>
            <input class='butt' type='submit' name='submit' value='Create Account'>
        </form>
    </div>

    <!-- Javascript to validate HTML form input -->
    <script type="text/javascript">
        window.addEventListener('load', function() {
            'use strict';

            validateForm();
            validateTitle();
            validateFirstName();
            validateLastName();
            validatePassword();
            validateRePassword();
        });

        //Declares validated bool varibles as false.
        var titleValidated = false;
        var fnValidated = false;
        var lnValidated = false;
        var passValidated = false;
        var rePassValidated = false;


        //Declares each form input as a variable.
        var titleField = document.forms["newTeacherForm"]["title"];
        var firstNameField = document.forms["newTeacherForm"]["first_name"];
        var lastNameField = document.forms["newTeacherForm"]["last_name"];
        var passwordField = document.forms["newTeacherForm"]["password"];
        var rePasswordField = document.forms["newTeacherForm"]["retype_password"];
        var createAccButt = document.forms["newTeacherForm"]["submit"];

        //Adds event listening for each form input, upon user input.
        titleField.addEventListener("change", validateTitle);
        firstNameField.addEventListener("input", validateFirstName);
        lastNameField.addEventListener("input", validateLastName);
        passwordField.addEventListener("input", validatePassword);
        rePasswordField.addEventListener("input", validateRePassword);

        function validateTitle() //Validates the title.
        {
            titleField.style.borderWidth = "2px";
            if (titleField.value == "none") //If the title is left as please select.
            {
                titleValidated = false;
                titleField.style.borderColor = "red";
            }
            else
            {
                titleValidated = true;
                titleField.style.borderColor = "green";
            }
            validateForm();
        }

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
            if ((titleValidated == true) && (fnValidated == true) && (lnValidated == true) && (passValidated == true) && (rePassValidated == true)) //Checks that all the validated bool variables are true.
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
