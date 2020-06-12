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
checkLogin('removeStudentProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $idTicked = array();

        $numOfCheckboxes = filter_has_var(INPUT_GET, 'num')
            ? $_GET['num'] : null;

        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;


        for ($i = 0; $i < $numOfCheckboxes; $i++)
        {
            $varNameHolder = "var".$i;

            $idTicked[$i] = filter_has_var(INPUT_GET, $varNameHolder)
                ? $_GET[$varNameHolder] : null;
        }

        foreach ($idTicked as $i)
        {
            if (!$i == null) //If the check box is ticked.
            {
                //Check the class is belongs to the teacherID

                $dbConn = getConnection();
                $SQLquery = "SELECT Class.id_pk
                             FROM Class
                             LEFT JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk 
                             WHERE ClassTeachers.teacher_id_fk =:teacherID AND Class.id_pk =:classID";
                $stmt = $dbConn->prepare($SQLquery);
                $stmt->execute(array(':teacherID' => $_SESSION['userID'], ':classID' => $classSelected));
                $class = $stmt->fetchObject();
                if ($class)
                {
                    //Check the student selected belongs to the class selected.

                    $dbConn = getConnection();
                    $SQLquery = "SELECT Account.id_pk
                                 FROM Account
                                 LEFT JOIN AccountClasses ON AccountClasses.user_id_fk = Account.id_pk
                                 LEFT JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                                 WHERE Account.id_pk =:studentID AND Class.id_pk =:classID";
                    $stmt = $dbConn->prepare($SQLquery);
                    $stmt->execute(array(':studentID' => $i, ':classID' => $classSelected));
                    $student = $stmt->fetchObject();

                    if ($student)
                    {
                        //Check if the student is a member of another class.

                        $dbConn = getConnection();
                        $SQLquery = "SELECT AccountClasses.id_pk
                                     FROM AccountClasses
                                     WHERE AccountClasses.class_id_fk !=:classID AND AccountClasses.user_id_fk =:studentID";
                        $stmt = $dbConn->prepare($SQLquery);
                        $stmt->execute(array(':studentID' => $i, ':classID' => $classSelected));
                        $secondClass = $stmt->fetchObject();

                        if (!$secondClass) //If false
                        {
                            deleteAccount($i);
                        }
                        else
                        {
                            $dbConn = getConnection();
                            $SQLremove = "DELETE FROM AccountClasses
                                          WHERE AccountClasses.user_id_fk =:studentID AND AccountClasses.class_id_fk =:classID";
                            $stmt = $dbConn->prepare($SQLremove);
                            $stmt->execute(array(':studentID' => $i, ':classID' => $classSelected));
                        }
                    }
                }
            }
        }

        header('Location: classes.php?classSelected='.$classSelected);
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