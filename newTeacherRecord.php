<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 14/02/2020
 * Time: 17:02
 */

    ini_set("session.save_path", "/home/unn_w17006735/sessionData");
    session_start();
    require_once("functions.php");

    $input = array();
    $errors = false;

    //Gathers the variables from the form and sets stores them in the input array.
    $input['title'] = filter_has_var(INPUT_GET, 'title')
        ? $_GET['title'] : null;
    $input['first_name'] = filter_has_var(INPUT_GET, 'first_name')
        ? $_GET['first_name'] : null;
    $input['last_name'] = filter_has_var(INPUT_GET, 'last_name')
        ? $_GET['last_name'] : null;
    $input['password'] = filter_has_var(INPUT_GET, 'password')
        ? $_GET['password'] : null;
    $input['retype_password'] = filter_has_var(INPUT_GET, 'retype_password')
        ? $_GET['retype_password'] : null;

    //Trims any whiteshpace out of the input data.
    $input['title'] = trim($input['title']);
    $input['password'] = trim($input['password']);
    $input['retype_password'] = trim($input['retype_password']);

    //checks no input has been left empty.

    if(($input['first_name'] == null)||(strlen($input['first_name'])>20)) //if the first name entry is null or is greater than 20.
    {
        $errors = true;
    }
    if(($input['last_name'] == null)||(strlen($input['last_name'])>20)) //if the last name entry is null or is greater than 20.
    {
        $errors = true;
    }

    //checks the title is either 'Mr.', 'Mrs.' or 'Ms.'
    $titleChoices = array('mr','mrs','ms');
    if (!in_array($input['title'],$titleChoices))
    {
        $errors = true;
    }

    //Checks both passwords are the same.
    if ((!$input['password']==$input['retype_password']) || (strlen($input['password']) < 8)) //if the first password doesn't equals the second password or the password is too short.
    {
        $errors = true;
    }

    //If there are errors the page will redirect page to the form page, else it will call the function to add the data to the database.
    if ($errors == true)
    {
        header('Location: '.newTeacherForm.'.php');
    }
    else
    {
        $input['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT); //Creates a hash of the password entered.
        try
        {

            $input['username'] = generateUsername($input['first_name'], $input['last_name']); //Creates the username and stores in the a variable.

            //Code to add initial details to account table.
            $dbConn = getConnection();
            $insertSQL = "INSERT INTO Account (first_name, last_name, password_hash, username)
                          VALUES (:first_name, :last_name, :password_hash, :username)";
            $stmt = $dbConn->prepare($insertSQL);
            $stmt->execute(array(':first_name' => $input['first_name'], ':last_name' => $input['last_name'], ':password_hash' => $input['password_hash'], ':username' => $input['username']));

            $dbConn = getConnection();
            $SQLquery = "SELECT Account.id_pk
                         FROM Account
                         WHERE Account.username =:username";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':username' => $input['username']));

            $user = $stmt->fetchObject();
            $userID = $user->id_pk;

            $dbConn = getConnection();
            $insertSQL = "INSERT INTO Teacher (title, account_id_fk)
                          VALUES (:title, :userID)";
            $stmt = $dbConn->prepare($insertSQL);
            $stmt->execute(array(':title' => $input['title'], 'userID' => $userID));

            $names = array('Good Work!', 'Great Idea!', 'Attendance', 'Excellent Contribution!', 'Well Done!');
            $coins = array(1000, 1000, 500, 1500, 750);
            $qCoins = array(2, 2, 1, 3, 1);


            for ($i = 0; $i <= 4; $i++)
            {
                $dbConn = getConnection();
                $insertSQL = "INSERT INTO Preset (teacher_id_fk, name, message, coins, q_coins)
                              VALUES (:teacherID, :name, :message, :coins, :qCoins)";
                $stmt = $dbConn->prepare($insertSQL);
                $stmt->execute(array(':teacherID' => $userID, ':name' => $names[$i], ':message' => "", ':coins' => $coins[$i], ':qCoins' => $qCoins[$i]));
            }

        }
        catch (Exception $e)
        {
            echo "A problem occurred. Please try again.";
        }

        header('Location: loginProcess.php?username='.$input['username'].'&password='.$input['password']);
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

?>

