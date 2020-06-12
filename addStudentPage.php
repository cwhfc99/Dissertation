<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

require_once('functions.php');
checkLogin('addStudentPage');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        echo "
        <!doctype html>
        <html lang=\"en\">
        <head>
            <meta charset=\"UTF-8\" />
            <title>Home Page</title>
            <link rel=\"stylesheet\" type=\"text/css\" href=\"stylesheet.css\">
        </head>
        <body>
        ";

        buildBanner();
        turnButtBlack('teacherButt');
        $updatedClassSelected = buildTeacherInterface($classSelected, null);

        echo "Which class would you like to add the students selected to?";

        //Populate select with classes belong to the teacher.

        $dbConn = getConnection();
        $SQLquery = "SELECT Class.name
                 FROM Class
                 JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk
                 WHERE ClassTeachers.teacher_id_fk =:teacherID AND Class.id_pk =:classID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':teacherID' => $_SESSION['userID'], ':classID' => $updatedClassSelected));
        $updatedClassSelectedID = $stmt->fetchObject();
        $updatedClassSelectedName = $updatedClassSelectedID->name;

        $dbConn = getConnection();
        $SQLquery = "SELECT Class.id_pk, Class.name
                 FROM Class
                 JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk
                 WHERE ClassTeachers.teacher_id_fk =:teacherID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('teacherID' => $_SESSION['userID']));

        $classID = array();
        $className = array();
        while ($rowObj = $stmt->fetchObject())
        {
            $classID[] = $rowObj->id_pk;
            $className[] = $rowObj->name;
        }

        echo "<div class='selectContainer2>'
                <form id='classSelector'>
                    <select id='classSelect2' name='classSelected')'>";


        for ($i = 0; $i <= (count($classID) - 1); $i++)
        {
            if ($classSelected == $classID[$i])
            {
                $temp = "<option selected value='" . $classID[$i] . "'>";
            }
            else
            {
                $temp = "<option value='" . $classID[$i] . "'>";
            }
            echo $temp;
            echo $className[$i];
            echo "</option>";
        }


        echo
        "
                </select>
             </form>
            </div>";

        $idTicked = array();

        $numOfCheckboxes = filter_has_var(INPUT_GET, 'num')
            ? $_GET['num'] : null;

        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;


        for ($i = 0; $i < $numOfCheckboxes; $i++)
        {
            $varNameHolder = "var" . $i;

            $idTicked[$i] = filter_has_var(INPUT_GET, $varNameHolder)
                ? $_GET[$varNameHolder] : null;
        }

        $_SESSION['studentIDs'] = $idTicked;

        echo
        "
        <div class='yesNoButtContainer'>
            <div class='noButtContainer'><a href='classes.php?classSelected={$updatedClassSelected}'>No</a></div>
            <div class='yesButtContainer' onclick='linkToAddProc()'><a href='#'>Yes</a></div>
        </div><br>
        ";


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

<script type="text/javascript">
    window.addEventListener('load', function() {
        'use strict';
    });

    function linkToAddProc()
    {
        var classSelect = document.getElementById('classSelect2').value;
        const page = "addStudentProcess.php?classSelected=";
        const holder = page.concat(classSelect);
        document.location = holder;
    }

</script>

</body>

</html>
