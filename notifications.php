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
checkLogin('notifications');
if ($_SESSION['loggedIn'] == 'true')
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE Notification
                  SET Notification.read = 1
                  WHERE Notification.to_id_fk =:userID ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    buildBanner();
    turnButtBlack('gameButt');
    turnButtBlack('notificationButt');

    echo "
         <div class='notificationMain'>
            <table class='noteTable' id='noteTable'>
                <tr>
                    <th class='noteTableDate'>Date</th>
                    <th class='noteTableType'>Type</th>
                    <th class='noteTableFrom'>From</th>
                    <th>Message</th>
                    <th class='noteTableCoins'>Coins</th>
                    <th class='noteTableQCoins'>Q-Coins</th>
                    <th>XP</th>
                </tr>
         </div>   
         ";

    $dbConn = getConnection();
    $SQLquery = "SELECT Notification.id_pk, Notification.date, Notification.from_id_fk, Notification.message, Notification.reward_id_fk, Notification.class_id_fk, Notification.type
                 FROM Notification
                 WHERE Notification.to_id_fk =:userID
                 ORDER BY Notification.id_pk DESC";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $accountArray = array();
    while ($rowObj = $stmt->fetchObject())
    {
        $accountArray[] = $rowObj;
    }
    for ($i = 0; $i < sizeof($accountArray); $i++)
    {

        if (($accountArray[$i]->type == 'Message')||($accountArray[$i]->type == 'Invitation')||($accountArray[$i]->type == 'Reward'))
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT Account.username
                 FROM Account
                 WHERE Account.id_pk =:userID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':userID' => $accountArray[$i]->from_id_fk));
            $teacher = $stmt->fetchObject();
            $teacherUsername = $teacher->username;
        }
        else
        {
            $teacherUsername = $_SESSION['cityName'];
        }

        $date = date("d/m/y",strtotime($accountArray[$i]->date));

        $noteID = $accountArray[$i]->id_pk;

        echo "<tr onclick=\"document.location='inNotifications.php?noteSelected=".$noteID."'\" style='cursor:pointer' class='tableRow'>
                <td>{$date}</td>
                <td>{$accountArray[$i]->type}</td>
                <td>{$teacherUsername}</td>
                <td>{$accountArray[$i]->message}</td>";

        if (($accountArray[$i]->type == 'Message')||($accountArray[$i]->type == 'Invitation') || ($accountArray[$i]->type == 'Invite Accepted'))
        {
            echo "  <td> </td >
                    <td> </td >
                    <td> </td>";
        }
        else
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT Rewards.coins, Rewards.q_coins, Rewards.xp
                         FROM Rewards
                         WHERE Rewards.id_pk =:rewardID
                         ";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':rewardID' => $accountArray[$i]->reward_id_fk));
            $coinDetails = $stmt->fetchObject();
            echo "  <td>{$coinDetails->coins}</td>
                    <td>{$coinDetails->q_coins}</td>
                    <td>{$coinDetails->xp}</td>";
        }
        echo" </tr>";

    }
    echo "</table>";

}
else
{
    notLoggedIn();
}


?>


</body>

</html>