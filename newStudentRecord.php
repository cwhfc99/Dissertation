<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 14/02/2020
 * Time: 17:02
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
checkLogin('newStudentRecord');

if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {


        $classSelected = $_SESSION['classSelected'];

        $updatedClassSelected = buildTeacherInterface($classSelected, null);

        $input = array();
        $errors = false;

        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;


//Gathers the variables from the form and sets stores them in the input array.
        $input['first_name'] = filter_has_var(INPUT_GET, 'first_name')
            ? $_GET['first_name'] : null;
        $input['last_name'] = filter_has_var(INPUT_GET, 'last_name')
            ? $_GET['last_name'] : null;

//checks no input has been left empty.

        if (($input['first_name'] == null) || (strlen($input['first_name']) > 20)) //if the first name entry is null or is greater than 20.
        {
            $errors = true;
        }
        if (($input['last_name'] == null) || (strlen($input['last_name']) > 20)) //if the last name entry is null or is greater than 20.
        {
            $errors = true;
        }


//If there are errors the page will redirect page to the form page, else it will call the function to add the data to the database.
        if ($errors == true) {
            header('Location: newStudentForm.php');
        } else {
            try {
                $input['password'] = generatePassword();
                $input['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
                $input['username'] = generateUsername($input['first_name'], $input['last_name']); //Creates the username and stores in the a variable.

                //Code to add initial details to account table.
                $dbConn = getConnection();
                $insertSQL = "INSERT INTO Account (first_name, last_name, password_hash, username, initial_password)
                              VALUES (:first_name, :last_name, :password_hash, :username, :initial_password)";
                $stmt = $dbConn->prepare($insertSQL);
                $stmt->execute(array(':first_name' => $input['first_name'], ':last_name' => $input['last_name'], ':password_hash' => $input['password_hash'], ':username' => $input['username'], ':initial_password' => $input['password']));

                //Code to link the Account record to the Teacher table
                $dbConn = getConnection();
                $SQLquery = "SELECT id_pk
                     FROM Account
                     WHERE username =:username";
                $stmt = $dbConn->prepare($SQLquery);
                $stmt->execute(array(':username' => $input['username']));
                $input['userID'] = $stmt->fetchColumn();

                $dbConn = getConnection();
                $insertSQL = "INSERT INTO AccountClasses (user_id_fk, class_id_fk)
                      VALUES (:id, :classID)";
                $stmt = $dbConn->prepare($insertSQL);
                $stmt->execute(array(':id' => $input['userID'], ':classID' => $updatedClassSelected));


            } catch (Exception $e) {
                echo "A problem occurred. Please try again.";
            }

            header('Location: newStudentCon.php?username=' . $input['username'] . '&classSelected=' . $updatedClassSelected . '&password=' . $input['password']);
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

function generateUsername($first_name, $last_name)
{
    //Divides the first name string based on how big it is.
    if (strlen($first_name) >= 3)
    {
        $a = substr($first_name, 0, 3);
    }
    else if (strlen($first_name) > 1)
    {
        $a = substr($first_name, 0, 2);
    }
    else
    {
        $a = substr($first_name, 0, 1);
    }

    //Divides the last name string based on how big it is.
    if (strlen($last_name) >= 3)
    {
        $b = substr($last_name, 0, 3);
    }
    else if (strlen($last_name) > 1)
    {
        $b = substr($last_name, 0, 2);
    }
    else
    {
        $b = substr($last_name, 0, 1);
    }

    $a = strtolower($a);
    $b = strtolower($b);
    $name  = $a.$b;

    $num = findDuplicates($name);
    if ($num > 0)
    {
        $name = $name . $num; //Adds the next available number to the username.
    }
    return $name;
}

function findDuplicates($name) //Function that checks how many similar usernames already exist/
{

    $outputName = $name;
    $b = false;
    $i = 0;
    while ($b == false)
    {
        if (($i > 0))
        {
            $outputName = $name.$i;
        }
        $dbConn = getConnection();
        $SQLquery = "SELECT username
                         FROM Account
                         WHERE username =:username";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':username' => $outputName));
        $temp = $stmt->fetchColumn();

        if ($temp)
        {
            $i = $i + 1;
            $b = false;
        }
        else
        {
            $b = true;
        }
    }
    return $i;
}

function generatePassword()
{
    $chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','x');
    $password = "";

    $loop = rand(8,15);
    for ($i = 0; $i <= $loop; $i++)
    {
        $charToAdd = null;
        $randLetORNum = rand(0,1);
        if ($randLetORNum == 0)
        {
            $charToAdd = rand(0,9);
        }
        else
        {
            $randInt = rand(0,25);
            $charToAdd = $chars[$randInt];
        }
        $password = $password.$charToAdd;
    }
    return $password;
}

?>


    </body>
</html>
