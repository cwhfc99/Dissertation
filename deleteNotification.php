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
checkLogin('deleteNotification');
if ($_SESSION['loggedIn'] == 'true')
{
    $noteSelect = filter_has_var(INPUT_GET, 'noteSelected')
        ? $_GET['noteSelected'] : null;

    $dbConn = getConnection();
    $SQLquery = "SELECT Notification.id_pk
                  FROM Notification
                  WHERE Notification.id_pk =:noteID AND Notification.to_id_fk =:userID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':noteID' => $noteSelect, ':userID' => $_SESSION['userID']));
    $noteID = $stmt->fetchObject();

    if($noteID)
    {
        $dbConn = getConnection();
        $SQLdelete = "DELETE FROM Notification
                      WHERE Notification.id_pk =:noteID";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':noteID' => $noteID->id_pk));
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