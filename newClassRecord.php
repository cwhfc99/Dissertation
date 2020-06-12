<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 19/02/2020
 * Time: 13:32
 */
    ini_set("session.save_path", "/home/unn_w17006735/sessionData");
    session_start();
    require_once("functions.php");

    $input = filter_has_var(INPUT_GET, 'newClassInput')
            ? $_GET['newClassInput'] : null;

    if(($input == null)||(strlen($input)>20)) //if the first name entry is null or is greater than 20.
    {
        header('Location: createClass.php?classCreateError=true');
    }
    else
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT Class.name
                     FROM ClassTeachers
                     LEFT JOIN Class ON ClassTeachers.class_id_fk = Class.id_pk
                     WHERE ClassTeachers.teacher_id_fk = :userID
                     ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('userID' => $_SESSION['userID']));

        $test = false;
        while ($rowObj = $stmt->fetchObject())
        {
            if ($rowObj->name == $input)
            {
                $test = true;
            }
        }

        if ($test == true)
        {
            header('Location: createClass.php?classCreateError=true');
        }
        else
        {
            try
            {
                $dbConn = getConnection();
                $insertSQL = "INSERT INTO Class (name)
                          VALUES (:name)";
                $stmt = $dbConn->prepare($insertSQL);
                $stmt->execute(array(':name' => $input));
            }
            catch(Exception $e)
            {
                echo "A problem occurred. Please try again.";
            }

            try
            {
                $dbConn = getConnection();
                $SQLquery = "SELECT id_pk
                         FROM Class
                         WHERE name =:tName
                         ORDER BY id_pk DESC";
                $stmt = $dbConn->prepare($SQLquery);
                $stmt->execute(array('tName' => $input));
                $temp = $stmt->fetchObject();
                $classID = $temp->id_pk;
            }
            catch(Exception $e)
            {
                echo "A problem occurred. Please try again.";
            }

            try
            {
                $dbConn = getConnection();
                $insertSQL = "INSERT INTO ClassTeachers (teacher_id_fk, class_id_fk)
                          VALUES (:teacherID, :classID)";
                $stmt = $dbConn->prepare($insertSQL);
                $stmt->execute(array('teacherID' => $_SESSION['userID'], 'classID' => $classID));
            }
            catch(Exception $e)
            {
                echo "A problem occurred. Please try again.";
            }

            try
            {
                $dbConn = getConnection();
                $insertSQL = "INSERT INTO AccountClasses (user_id_fk, class_id_fk)
                          VALUES (:userID, :classID)";
                $stmt = $dbConn->prepare($insertSQL);
                $stmt->execute(array('userID' => $_SESSION['userID'], 'classID' => $classID));
            }
            catch(Exception $e)
            {
                echo "A problem occurred. Please try again.";
            }

            header('Location: classes.php?classSelected='.$classID);

        }

    }