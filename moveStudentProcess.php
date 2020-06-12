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
checkLogin('moveStudentProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {

        $previousClass = filter_has_var(INPUT_GET, 'previousClass')
            ? $_GET['previousClass'] : null;

        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        $studentsToMove = array();

        $j = 0;
        foreach ($_SESSION['studentIDs'] as $i)
        {
            $studentsToMove[$j] = $i;

            $j++;
        }

        //Check class belongs to teacher

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
            $dbConn = getConnection();
            $SQLquery = "SELECT Class.id_pk
                         FROM Class
                         LEFT JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk 
                         WHERE ClassTeachers.teacher_id_fk =:teacherID AND Class.id_pk =:classID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':teacherID' => $_SESSION['userID'], ':classID' => $previousClass));
            $pClass = $stmt->fetchObject();

            if ($pClass)
            {
                for ($j = 0; $j <= count($studentsToMove) - 1; $j++)
                {

                    //Check if student is in class
                    $i = $studentsToMove[$j];

                    if (!$i == Null)
                    {

                        $dbConn = getConnection();
                        $SQLquery = "SELECT AccountClasses.id_pk
                                     FROM AccountClasses 
                                     LEFT JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                                     WHERE AccountClasses.id_pk =:studentID AND Class.id_pk =:classID";
                        $stmt = $dbConn->prepare($SQLquery);
                        $stmt->execute(array(':studentID' => $i, ':classID' => $classSelected));
                        $student = $stmt->fetchObject();

                        if (!$student)
                        {
                            //Add student to class in AccountClasses
                            $dbConn = getConnection();

                            $SQLinsert = "INSERT INTO AccountClasses(user_id_fk, class_id_fk)
                                          VALUES (:studentID, :classID)";
                            $stmt = $dbConn->prepare($SQLinsert);
                            $stmt->execute(array(':studentID' => $i, ':classID' => $classSelected));

                            //Remove student from previous class in AccountClasses
                            $dbConn = getConnection();
                            $SQLremove = "DELETE FROM AccountClasses
                                          WHERE AccountClasses.user_id_fk =:studentID AND AccountClasses.class_id_fk =:classID";
                            $stmt = $dbConn->prepare($SQLremove);
                            $stmt->execute(array(':studentID' => $i, ':classID' => $previousClass));
                        }
                    }
                }
            }
        }
        unset($_SESSION['studentIDs']);
        header('Location: classes.php');
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