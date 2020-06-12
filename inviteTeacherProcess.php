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
checkLogin('classes');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $teacherUsername = filter_has_var(INPUT_GET, 'teacherUsername')
            ? $_GET['teacherUsername'] : null;

        $classSelected = $_SESSION['classSelected'];

        buildBanner();
        turnButtBlack('teacherButt');
        $updatedClassSelected = buildTeacherInterface($classSelected, null);

        try
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT Account.id_pk
                         FROM Account
                         WHERE Account.username =:username";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':username' => $teacherUsername));
            $temp1 = $stmt->fetchObject();

            if ($temp1)
            {
                $inviteeID = $temp1->id_pk;

                $dbConn = getConnection();
                $querySQL = "SELECT id_pk
                 FROM Teacher
                 WHERE account_id_fk = :userID";
                $stmt = $dbConn->prepare($querySQL);
                $stmt->execute(array(':userID' => $inviteeID));
                $temp = $stmt->fetchColumn();

                if ($temp)
                {

                    $dbConn = getConnection();
                    $SQLquery = "SELECT Class.name
                                 FROM Class
                                 WHERE Class.id_pk =:classID";
                    $stmt = $dbConn->prepare($SQLquery);
                    $stmt->execute(array(':classID' => $updatedClassSelected));
                    $class = $stmt->fetchObject();
                    $className = $class->name;

                    //Check ClassTeachers table to see if teacher is already a teacher of that class

                    $dbConn = getConnection();
                    $SQLquery = "SELECT ClassTeachers.id_pk
                                 FROM ClassTeachers
                                 WHERE ClassTeachers.class_id_fk =:classID AND ClassTeachers.teacher_id_fk =:teacherID";
                    $stmt = $dbConn->prepare($SQLquery);
                    $stmt->execute(array(':classID' => $updatedClassSelected, ':teacherID' => $inviteeID));
                    $classTeacher = $stmt->fetchObject();

                    if (!$classTeacher)
                    {
                        $message = "{$_SESSION['username']} has invited you to manage class: {$className}";

                        $dbConn = getConnection();
                        $insertSQL = "INSERT INTO Notification(from_id_fk, to_id_fk, message, invite, date, class_id_fk, type)
                                  VALUES (:userID, :inviteeID, :message, :invite, :date, :classID, :type)";
                        $stmt = $dbConn->prepare($insertSQL);
                        $stmt->execute(array(':userID' => $_SESSION['userID'], ':inviteeID' => $inviteeID, ':message' => $message, ':invite' => true, ':date' => date('Y/m/d'), 'classID' => $updatedClassSelected, 'type' => 'Invitation'));

                        echo "<div class='deleteStatement'>You have invited {$teacherUsername} to manage class '{$className}'. You will be notified when the accept your invitation.</div>";
                    }
                    else
                    {
                        echo "{$teacherUsername} is already managing class {$className}.";
                    }
                }
                else
                {
                    echo "The username you have entered is not a teacher.";
                }
            }
            else
            {
                echo "The username you have entered does not exist.";
            }

        }
        catch (Exception $e)
        {
            echo "An error occurred.";
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


</body>

</html>