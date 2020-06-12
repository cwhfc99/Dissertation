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
    <title>Notifications</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('acceptInvite');
if ($_SESSION['loggedIn'] == 'true')
{

    $noteSelect = filter_has_var(INPUT_GET, 'noteSelected')
        ? $_GET['noteSelected'] : null;

    $dbConn = getConnection();
    $SQLquery = "SELECT Notification.id_pk, Notification.class_id_fk, from_id_fk
                  FROM Notification
                  WHERE Notification.id_pk =:noteID AND Notification.to_id_fk =:userID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':noteID' => $noteSelect, ':userID' => $_SESSION['userID']));
    $noteID = $stmt->fetchObject();

    if($noteID)
    {
        $check = testAlreadyAccepted($_SESSION['userID'], $noteID->class_id_fk);

        if ($check == false)
        {
            $dbConn = getConnection();
            $insertSQL = "INSERT INTO ClassTeachers (teacher_id_fk, class_id_fk)
                      VALUES (:id, :classID)";
            $stmt = $dbConn->prepare($insertSQL);
            $stmt->execute(array(':id' => $_SESSION['userID'], 'classID' => $noteID->class_id_fk));

            $dbConn = getConnection();
            $insertSQL = "INSERT INTO AccountClasses (user_id_fk, class_id_fk)
                      VALUES (:id, :classID)";
            $stmt = $dbConn->prepare($insertSQL);
            $stmt->execute(array(':id' => $_SESSION['userID'], 'classID' => $noteID->class_id_fk));

            $message = $_SESSION['username']." accepted your invite.";

            $dbConn = getConnection();
            $insertSQL = "INSERT INTO Notification(from_id_fk, to_id_fk, message, invite, date, type)
                                  VALUES (:userID, :inviteeID, :message, :invite, :date, :type)";
            $stmt = $dbConn->prepare($insertSQL);
            $stmt->execute(array(':userID' => $_SESSION['userID'], ':inviteeID' => $noteID->from_id_fk, ':message' => $message, ':invite' => false, ':date' => date('Y/m/d'), 'type' => 'Invite Accepted'));

        }
    }

}
else
{
    notLoggedIn();
}
header('Location: notifications.php');

?>


</body>

</html>