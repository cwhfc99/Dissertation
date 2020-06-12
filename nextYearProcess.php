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
if ($_SESSION['loggedIn'] == 'true')
{
    $dbConn = getConnection();
    $SQLselect = "SELECT Account.last_log_in
                  FROM Account
                  WHERE Account.id_pk =:userID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $lastLogIn = $stmt->fetchObject();
    $date = $lastLogIn->last_log_in;
    $date = strtotime($date);

    $newDate = strtotime("+24 hours", $date);
    $newDate = date('Y/m/d', $newDate);

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Account
                  SET last_log_in =:dateT
                  WHERE Account.id_pk =:userID
                                     ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':userID' => $_SESSION['userID'], ':dateT' => $newDate));

    newYear();
    header('Location: '.$_SESSION['redirect'].'.php');

}
else
{
    notLoggedIn();
}

?>

</body>

</html>