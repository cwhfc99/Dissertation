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
checkLogin('deleteClass');

if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true) {
        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        buildBanner();
        turnButtBlack('teacherButt');
        $updatedClassSelected = buildTeacherInterface($classSelected, null);

        $dbConn = getConnection();
        $SQLquery = "SELECT Class.name
                     FROM Class
                     WHERE Class.id_pk=:classID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('classID' => $updatedClassSelected));

        $className = $stmt->fetchObject();

        if ($updatedClassSelected == null)
        {
            echo "You have no classes to delete.";
        }
        else
        {
            echo "<div class='deleteStatement'>Are you sure you want to delete class: " . $className->name . "?</div>";


            echo
            "
        <div class='yesNoButtContainer'>
            <div class='noButtContainer'><a href='classes.php?classSelected={$updatedClassSelected}'>No</a></div>
            <div class='yesButtContainer'><a href='deleteClassRecord.php?classSelected={$updatedClassSelected}'>Yes</a></div>
        </div><br>
        ";
            echo "<br><br><div class='deleteStatement'>Please note, this will only delete the class and the accounts of the students in the class.</div>";
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