<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 18:51
 */

ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();
require_once("functions.php");

validateLogIn();

list($input, $errors) = validateLogIn();
if ($errors) {
    checkLogin($_SESSION['redirect']);
    foreach ($errors as $error) {
        echo $error;
    }
}
else {
    echo "Logged in";
    //header('Location: '.$_SESSION['redirect'].'.php');
}

function validateLogIn(){
    $errors = array();
    $input = array();

       $input['username'] = filter_has_var(INPUT_GET, 'username')
           ? $_GET['username'] : null;
       $input['password'] = filter_has_var(INPUT_GET, 'password')
           ? $_GET['password'] : null;

       echo $input['username'];
       echo $input['password'];

    try
    {

        $dbConn = getConnection();

        $querySQL = "SELECT password_hash, initial_password
                 FROM Account
                 WHERE username = :username";

        $stmt = $dbConn->prepare($querySQL);
        $stmt->execute(array(':username' => $input['username']));

        $user = $stmt->fetchObject();
        if ($user) {
            $passwordHash = $user->password_hash;
            if (password_verify($input['password'], $passwordHash)) {
                $_SESSION['loggedIn'] = true;
                $_SESSION['username'] = $input['username'];

                $dbConn = getConnection();
                $querySQL = "SELECT id_pk
                 FROM Account
                 WHERE username = :username";
                $stmt = $dbConn->prepare($querySQL);
                $stmt->execute(array(':username' => $input['username']));

                $_SESSION['userID'] = $stmt->fetchColumn();


                $dbConn = getConnection();
                $querySQL = "SELECT id_pk
                 FROM Teacher
                 WHERE account_id_fk = :userID";
                $stmt = $dbConn->prepare($querySQL);
                $stmt->execute(array(':userID' => $_SESSION['userID']));
                $temp = $stmt->fetchColumn();

                if ($temp)
                {
                    $_SESSION['teacher'] = true;
                }
                else
                {
                    $_SESSION['teacher'] = false;
                }
                echo $_SESSION['teacher'];

            } else {
                $errors[] = "The Username or Password was incorrect.";
                $_SESSION['loggedIn'] = false;
            }
        } else {
            $errors[] = "The Username or Password was incorrect.";
            $_SESSION['loggedIn'] = false;
        }

        if ($_SESSION['teacher'] == false)
        {
            $iniPass = $user->initial_password;
            if (password_verify($iniPass, $passwordHash))
            {
                header('Location: changePassword.php');
            }
            else
            {
                $dbConn = getConnection();
                $SQLselect = "SELECT Account.last_log_in
                      FROM Account
                      WHERE Account.id_pk =:userID";
                $stmt = $dbConn->prepare($SQLselect);
                $stmt->execute(array(':userID' => $_SESSION['userID']));
                $lastLog = $stmt->fetchObject();

                if ($lastLog->last_log_in == '0000-00-00') //If first log in.
                {
                    header('location: newCity.php');
                }
                else
                {
                    header('Location: ' . $_SESSION['redirect'] . '.php');
                }
            }
        }
        else
        {
            $dbConn = getConnection();
            $SQLselect = "SELECT Account.last_log_in
                      FROM Account
                      WHERE Account.id_pk =:userID";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':userID' => $_SESSION['userID']));
            $lastLog = $stmt->fetchObject();

            if ($lastLog->last_log_in == '0000-00-00') //If first log in.
            {
                header('location: newCity.php');
            } else
            {
                header('Location: ' . $_SESSION['redirect'] . '.php');
            }
        }
    }
    catch (Exception $e)
    {
        echo "A problem occurred. Please try again.";
    }
    return array($input, $errors);}

?>