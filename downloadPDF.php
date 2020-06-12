<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 20/02/2020
 * Time: 18:15
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();
require_once('functions.php');

include ('PDFlibrary/tcpdf.php');

require_once('functions.php');
checkLogin('classes');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        $updatedClassSelected = buildTeacherInterface($classSelected, null);

        $pdf = new TCPDF('P','mm','A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $dbConn = getConnection();
        $SQLquery = "SELECT Account.first_name, Account.last_name
             FROM Account
             WHERE Account.id_pk =:userID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('userID' => $_SESSION['userID']));

        $user = $stmt->fetchObject();
        $teacher = $user->first_name." ".$user->last_name;

        $dbConn = getConnection();
        $SQLquery = "SELECT Class.name
             FROM Class
             WHERE Class.id_pk =:classID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('classID' => $classSelected));

        $class=$stmt->fetchObject();

        $dbConn = getConnection();
        $SQLquery = "SELECT DISTINCT Account.first_name, Account.last_name, Account.username, Account.password_hash, Account.initial_password
                     FROM Account
                     JOIN AccountClasses ON AccountClasses.user_id_fk = Account.id_pk
                     JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                     JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk
                     WHERE Class.id_pk =:classID
                     ORDER BY Account.username";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('classID' => $updatedClassSelected));

        $studentDetails = null;
        $text = false;
        while ($rowobj = $stmt->fetchObject())
        {
            if ((password_verify($rowobj->initial_password, $rowobj->password_hash))== true)
            {
                $newHTML = "<p>Student: {$rowobj->first_name} {$rowobj->last_name}</p>
                        <p>Username: {$rowobj->username}</p>
                        <p>Password: {$rowobj->initial_password}</p>
                        <p>To set up your account, visit 'www.unn-w17006735.newnumyspace.co.uk/dissertation/index.php'. Use the details above to log in. Click 'Play Game'. Set a different password to the one above. Enjoy the game!</p>
                        <p>-------------------------------------------------------------------------------------------------------------------------------------</p>";
                $studentDetails = $studentDetails . $newHTML;
                $text = true;
            }
        }

        $pdf->AddPage();

        $html = "<p>Teacher: {$teacher}</p>
                 <p>Class: {$class->name}</p>
                 <p>This PDF is designed to be printed off and used in the classroom. It contains the initial log in details for each student. Student who have already logged in and changed their password are not included.</p>
                 <br>
                 <p>-------------------------------------------------------------------------------------------------------------------------------------</p>".$studentDetails;

        if ($text == true)
        {
            $pdf->WriteHTMLCell(0, 0, "10", "10", $html, 0);
        }
        else
        {
            $pdf->WriteHTMLCell(0, 0, "10", "10", "<p>All students have logged in and changed their password.</p>", 0);
        }
        ob_end_clean();
        $pdf->Output();

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