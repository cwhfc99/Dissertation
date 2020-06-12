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
checkLogin('menu');
if ($_SESSION['loggedIn'] == 'true')
{
    $input = array();
    $errors = false;

    //Gathers the variables from the form and sets stores them in the input array.
    $input['password'] = filter_has_var(INPUT_GET, 'password')
        ? $_GET['password'] : null;
    $input['retype_password'] = filter_has_var(INPUT_GET, 'retype_password')
        ? $_GET['retype_password'] : null;


    $input['password'] = trim($input['password']);
    $input['retype_password'] = trim($input['retype_password']);



    //Checks both passwords are the same.
    if ((!$input['password']==$input['retype_password']) || (strlen($input['password']) < 8)) //if the first password doesn't equals the second password or the password is too short.
    {
        $errors = true;
    }

    if ($errors == true)
    {
        header('Location: index.php');
    }
    else
    {
        $input['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT); //Creates a hash of the password entered.

        //Code to add initial details to account table.
        $dbConn = getConnection();
        $insertSQL = "UPDATE Account
                      SET Account.password_hash=:passwordHash
                      WHERE Account.id_pk=:userID";
        $stmt = $dbConn->prepare($insertSQL);
        $stmt->execute(array(':passwordHash' => $input['password_hash'], ':userID' => $_SESSION['userID']));

        header('Location: loginProcess.php?username='.$_SESSION['username'].'&password='.$input['password']);
    }
}
else
{
    notLoggedIn();
}


?>