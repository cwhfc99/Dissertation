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
        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        $presetSelected = filter_has_var(INPUT_GET, 'presetSelected')
            ? $_GET['presetSelected'] : null;

        buildBanner();
        turnButtBlack('teacherButt');
        $updatedClassSelected = buildTeacherInterface($classSelected, $presetSelected);
        buildAccountTable($updatedClassSelected);
        $updatedPresetSelected = buildRewardForm($classSelected, $presetSelected);

        echo
        "
            <div class='classesBottomContainer'>
                <div class='deleteStudentButt' onclick='linkToDelete()'><a href='#'>Delete</a></div>
                <div class='removeStudentButt' onclick='linkToRemove({$updatedClassSelected})'><a href='#'>Remove</a></div>
                <div class='moveStudentButt' onclick='linkToMove({$updatedClassSelected})'><a href='#'>Move</a></div>
                <div class='addToButt' onclick='linkToAdd()'><a href='#'>Add To</a></div>
            </div>
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

    var checkboxes = document.getElementsByName('accountCheck');
    function selectAll(source)
    {

        for(var i=0, n=checkboxes.length;i<n;i++)
        {
            checkboxes[i].checked = source.checked;
        }
    }

    function linkToDelete()
    {
        const checkboxes = document.getElementsByName('accountCheck');
        const checkboxesNum = checkboxes.length;
        const page = 'deleteStudentRecord.php';
        const checkboxNumVar = "num=";
        const comp = checkboxesCheck();
        if (comp == "?")
        {
            alert("Please select the students you wish to delete.")
        }
        else
        {
            var confirmation = confirm("Deleting students will delete their game and account off the system.\r\nAre you sure you wish to delete all students selected.")
            if (confirmation == true)
            {
                const holder = page.concat(comp, checkboxNumVar, checkboxesNum.toString());
                document.location = holder;
            }
        }
    }

    function linkToRemove(classSelect)
    {
        const checkboxes = document.getElementsByName('accountCheck');
        const checkboxesNum = checkboxes.length;
        const page = 'removeStudentProcess.php';
        const checkboxNumVar = "num="
        const comp = checkboxesCheck();
        const classVar = "&classSelected=";

        //const classAll = classVar.concat(classSelect);

        if (comp == "?")
        {
            alert("Please select the students you wish to remove.")
        }
        else
        {
            var confirmation = confirm("Removing students will remove them from the class selected. If a student is not a member of another class their game and account will be deleted of the system.\r\nAre you sure you wish to remove all students selected.")
            if (confirmation == true)
            {
                const holder = page.concat(comp, checkboxNumVar, checkboxesNum.toString(), classVar, classSelect);
                document.location = holder;
            }
        }
    }

    function linkToMove(classSelect)
    {
        const checkboxes = document.getElementsByName('accountCheck');
        const checkboxesNum = checkboxes.length;
        const page = 'moveStudentPage.php';
        const checkboxNumVar = "num="
        const comp = checkboxesCheck();
        const classVar = "&classSelected=";
        if (comp == "?")
        {
            alert("Please select the students you wish to move classes.")
        }
        else
        {
            const holder = page.concat(comp, checkboxNumVar, checkboxesNum.toString(), classVar, classSelect);
            document.location = holder;
        }
    }

    function linkToAdd()
    {
        const checkboxes = document.getElementsByName('accountCheck');
        const checkboxesNum = checkboxes.length;
        const page = 'addStudentPage.php';
        const checkboxNumVar = "num="
        const comp = checkboxesCheck();
        if (comp == "?")
        {
            alert("Please select the students you wish to move classes.")
        }
        else
        {
            const holder = page.concat(comp, checkboxNumVar, checkboxesNum.toString());
            document.location = holder;
        }
    }

    function checkboxesCheck()
    {

        var checkboxes = document.getElementsByName('accountCheck');

        var comp = "?"
        for(var i=0, n=checkboxes.length;i<n;i++)
        {
            var add = ""
            if (checkboxes[i].checked == true)
            {
                const box = i;
                const vari = "var"
                const boxText = vari.concat(box);
                const equals = "="
                const value = checkboxes[i].value;
                const amper = "&"
                add = add.concat(boxText, equals, value, amper)
            }

            comp = comp.concat(add);
        }
        return comp;
    }


</script>

</body>

</html>