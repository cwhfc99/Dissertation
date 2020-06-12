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

    buildBanner();
    turnButtBlack('gameButt');

    $noteSelect = filter_has_var(INPUT_GET, 'noteSelected')
        ? $_GET['noteSelected'] : null;

    //Checks notifications belongs to user.//
    $dbConn = getConnection();
    $SQLquery = "SELECT Notification.id_pk
                 FROM Notification
                 WHERE Notification.to_id_fk =:userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $noteID = array();
    while ($rowObj = $stmt->fetchObject())
    {
        $noteID[] = $rowObj->id_pk;
    }

    if (!(count($noteID)) == 0)
    {
        $inClass = false;
        for ($i = 0; $i <= (count($noteID) - 1); $i++)
        {
            if ($noteSelect == $noteID[$i]) {
                $inClass = true;
            }
        }
    }
    else
    {
        $inClass = false;
    }

    if (!(count($noteID)) == 0)
    {
        if (($noteSelect == null) || ($inClass == false))
        {
            $noteSelect = $noteID[0];
        }
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT Notification.id_pk
                 FROM Notification
                 WHERE Notification.id_pk =:noteID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':noteID' => $noteSelect));

    $notification = $stmt->fetchObject();

    if ($notification)
    {
        //Gets data about the notifications from the database.//
        $dbConn = getConnection();
        $SQLquery = "SELECT Notification.date, Notification.type, Notification.from_id_fk, Notification.message, Notification.reward_id_fk, Notification.class_id_fk
                 FROM Notification
                 WHERE Notification.id_pk =:noteID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':noteID' => $noteSelect));
        $noteDetails = $stmt->fetchObject();

        $date = date("d/m/y", strtotime($noteDetails->date));

        $type = $noteDetails->type;

        if ($type == 'Invitation')
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT Account.username
                 FROM Account
                 WHERE Account.id_pk =:userID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':userID' => $noteDetails->from_id_fk));
            $teacher = $stmt->fetchObject();
            $teacherUsername = $teacher->username;

            $check = testAlreadyAccepted($_SESSION['userID'], $noteDetails->class_id_fk);
            echo
            "    
                <div class='noteBackButt'><a href='notifications.php'>Back</a></div>
                <div class='noteDeleteButt'><a href='deleteNotification.php?noteSelected={$noteSelect}'>Delete</a></div>
                <div class='classesMain'>
                    <table class='inNoteTable'>
                        <tr>
                            <th>Date</th>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <th>From</th>                            
                            <td>{$teacherUsername}</td>
                        </tr>
                         <tr>
                            <th>Type</th>
                            <td>{$type}</td>
                        </tr>
                    </table>
                    <div class='noteTableMessage'>
                        {$noteDetails->message}";
            if ($check == false)
            {
                echo "<div class='noteAcceptButt' ><a href = 'acceptInvite.php?noteSelected={$noteSelect}' > Accept Invitation </a ></div>";
            }
            else
            {
                echo "<div class='noteAlreadyAccepted'>Already Accepted</div>";
            }

                    echo "</div>                    
                </div>
            ";
        }

        else if ($type == 'Reward')
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT Account.username
                 FROM Account
                 WHERE Account.id_pk =:userID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':userID' => $noteDetails->from_id_fk));
            $teacher = $stmt->fetchObject();
            $teacherUsername = $teacher->username;

            $dbConn = getConnection();
            $SQLquery = "SELECT Rewards.coins, Rewards.q_coins
                         FROM Rewards
                         WHERE Rewards.id_pk =:rewardID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':rewardID' => $noteDetails->reward_id_fk));
            $rewardDetails = $stmt->fetchObject();

            echo
            "    
                <div class='noteBackButt'><a href='notifications.php'>Back</a></div>
                <div class='noteDeleteButt'><a href='deleteNotification.php?noteSelected={$noteSelect}'>Delete</a></div>
                <div class='classesMain'>
                    <table class='inNoteTable'>
                        <tr>
                            <th>Date</th>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <th>From</th>                            
                            <td>{$teacherUsername}</td>
                        </tr>
                         <tr>
                            <th>Type</th>
                            <td>{$type}</td>
                        </tr>
                        <tr>
                            <th>Coins</th>
                            <td>{$rewardDetails->coins}</td>
                        </tr>
                        <tr>
                            <th>Q-Coins</th>
                            <td>{$rewardDetails->q_coins}</td>
                        </tr>
                    </table>
                    <div class='noteTableMessage'>
                        {$noteDetails->message}<br>
                        Your reward has already been credited to your account.";


            echo "</div>                    
                </div>
            ";
        }
        else if ($type == 'Message')
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT Account.username
                 FROM Account
                 WHERE Account.id_pk =:userID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':userID' => $noteDetails->from_id_fk));
            $teacher = $stmt->fetchObject();
            $teacherUsername = $teacher->username;

            echo
            "    
                <div class='noteBackButt'><a href='notifications.php'>Back</a></div>
                <div class='noteDeleteButt'><a href='deleteNotification.php?noteSelected={$noteSelect}'>Delete</a></div>
                <div class='classesMain'>
                    <table class='inNoteTable'>
                        <tr>
                            <th>Date</th>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <th>From</th>                            
                            <td>{$teacherUsername}</td>
                        </tr>
                         <tr>
                            <th>Type</th>
                            <td>{$type}</td>
                        </tr>
                        
                    </table>
                    <div class='noteTableMessage'>
                        {$noteDetails->message}<br>
                        ";


            echo "</div>                    
                </div>
            ";
        }
        else if ($type == 'Daily Gift')
        {
            $fromPerson = $_SESSION['cityName'];

            $dbConn = getConnection();
            $SQLquery = "SELECT Rewards.xp
                         FROM Rewards
                         WHERE Rewards.id_pk =:rewardID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':rewardID' => $noteDetails->reward_id_fk));
            $rewardDetails = $stmt->fetchObject();

            echo
            "    
                <div class='noteBackButt'><a href='notifications.php'>Back</a></div>
                <div class='noteDeleteButt'><a href='deleteNotification.php?noteSelected={$noteSelect}'>Delete</a></div>
                <div class='classesMain'>
                    <table class='inNoteTable'>
                        <tr>
                            <th>Date</th>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <th>From</th>                            
                            <td>{$fromPerson}</td>
                        </tr>
                         <tr>
                            <th>Type</th>
                            <td>{$type}</td>
                        </tr>
                        <tr>
                            <th>XP:</th>
                            <td>{$rewardDetails->xp}</td>
                        </tr>
                        
                    </table>
                    <div class='noteTableMessage'>
                        {$noteDetails->message}<br>
                        Your reward has already been credited to your account.";


            echo "</div>                    
                </div>
            ";
        }
        else if ($type == 'Daily Streak')
        {
            $fromPerson = $_SESSION['cityName'];

            $dbConn = getConnection();
            $SQLquery = "SELECT Rewards.xp, Rewards.coins
                         FROM Rewards
                         WHERE Rewards.id_pk =:rewardID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':rewardID' => $noteDetails->reward_id_fk));
            $rewardDetails = $stmt->fetchObject();

            echo
            "    
                <div class='noteBackButt'><a href='notifications.php'>Back</a></div>
                <div class='noteDeleteButt'><a href='deleteNotification.php?noteSelected={$noteSelect}'>Delete</a></div>
                <div class='classesMain'>
                    <table class='inNoteTable'>
                        <tr>
                            <th>Date</th>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <th>From</th>                            
                            <td>{$fromPerson}</td>
                        </tr>
                         <tr>
                            <th>Type</th>
                            <td>{$type}</td>
                        </tr>
                        <tr>
                            <th>XP:</th>
                            <td>{$rewardDetails->xp}</td>
                        </tr>
                        <tr>
                            <th>Coins:</th>
                            <td>{$rewardDetails->coins}</td>
                        </tr>
                        
                    </table>
                    <div class='noteTableMessage'>
                        {$noteDetails->message}<br>
                        Your reward has already been credited to your account.";


            echo "</div>                    
                </div>
            ";
        }
        else if ($type == 'Invite Accepted')
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT Account.username
                 FROM Account
                 WHERE Account.id_pk =:userID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':userID' => $noteDetails->from_id_fk));
            $teacher = $stmt->fetchObject();
            $teacherUsername = $teacher->username;
            echo
            "    
                <div class='noteBackButt'><a href='notifications.php'>Back</a></div>
                <div class='noteDeleteButt'><a href='deleteNotification.php?noteSelected={$noteSelect}'>Delete</a></div>
                <div class='classesMain'>
                    <table class='inNoteTable'>
                        <tr>
                            <th>Date</th>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <th>From</th>                            
                            <td>{$teacherUsername}</td>
                        </tr>
                         <tr>
                            <th>Type</th>
                            <td>{$type}</td>
                        </tr>
                        
                    </table>
                    <div class='noteTableMessage'>
                        {$noteDetails->message}<br>
                        ";


            echo "</div>                    
                </div>
            ";
        }

    }


}
else
{
    notLoggedIn();
}


?>


</body>

</html>