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
checkLogin('deleteStudentRecord');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $deletedOwnAccount = false;
        $idTicked = array();

        $numOfCheckboxes = filter_has_var(INPUT_GET, 'num')
            ? $_GET['num'] : null;

        for ($i = 0; $i < $numOfCheckboxes; $i++)
        {
            $varNameHolder = "var".$i;

            $idTicked[$i] = filter_has_var(INPUT_GET, $varNameHolder)
                ? $_GET[$varNameHolder] : null;
        }

        foreach ($idTicked as $i)
        {
            if (!$i == null)
            {
                //Check student is a member of a class belonging to the teacher

                $dbConn = getConnection();
                $SQLquery = "SELECT DISTINCT Account.id_pk
                             FROM Account
                             LEFT JOIN AccountClasses ON AccountClasses.user_id_fk = Account.id_pk
                             LEFT JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                             LEFT JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk
                             WHERE ClassTeachers.teacher_id_fk =:teacherID AND Account.id_pk =:studentID
                            ";
                $stmt = $dbConn->prepare($SQLquery);
                $stmt->execute(array(':teacherID' => $_SESSION['userID'], ':studentID' => $i));

                $student = $stmt->fetchObject();
                if ($student)
                {
                    deleteAccount($i);

                    if ($i == $_SESSION['userID'])
                    {
                        $deletedOwnAccount = true;
                    }

                }
            }
        }
        if ($deletedOwnAccount == true)
        {

            header('Location: logOutProcess.php');
        }
        else
        {
            header('Location: classes.php');

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