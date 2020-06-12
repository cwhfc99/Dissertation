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
checkLogin('rewardProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $errors = false;

        //Get reward variables
        $presetName = filter_has_var(INPUT_GET, 'name')
            ? $_GET['name'] : null;

        $presetMessage = filter_has_var(INPUT_GET, 'message')
            ? $_GET['message'] : null;

        $presetCoins = filter_has_var(INPUT_GET, 'coins')
            ? $_GET['coins'] : null;

        $presetQCoins = filter_has_var(INPUT_GET, 'qCoins')
            ? $_GET['qCoins'] : null;

        $presetUpdateCheck = filter_has_var(INPUT_GET, 'updateCheck')
            ? $_GET['updateCheck'] : null;

        $presetSelected = filter_has_var(INPUT_GET, 'presetSelected')
            ? $_GET['presetSelected'] : null;


        //Validate reward variables

        if((strlen($presetName)>25)) //if the name entry is greater than 25.
        {
            $errors = true;
        }

        if((strlen($presetMessage)>200)) //if the message entry is greater than 200.
        {
            $errors = true;
        }

        if(!(filter_var($presetCoins, FILTER_VALIDATE_INT)))
        {
            if (!($presetCoins == null))
            {
                $errors = true;
            }
        }

        echo $presetQCoins;

        if(!(filter_var($presetQCoins, FILTER_VALIDATE_INT)))
        {
            if (!($presetQCoins == null))
            {
                if(!($presetQCoins == 0))
                {
                    $errors = true;
                }
            }
        }

        if(!(($presetUpdateCheck == 'false')||($presetUpdateCheck == 'true')))
        {
            $errors = true;
        }

        if ($errors == false)
        {
            //Check if update or create
            if ($presetUpdateCheck == 'true')
            {
                if (!($presetSelected == 'null'))
                {
                    //Check the preset selected belongs to the teacher logged in.
                    $dbConn = getConnection();
                    $SQLquery = "SELECT Preset.id_pk
                                 FROM Preset
                                 WHERE Preset.id_pk =:presetID AND Preset.teacher_id_fk =:teacherID";
                    $stmt = $dbConn->prepare($SQLquery);
                    $stmt->execute(array(':presetID' => $presetSelected, 'teacherID' => $_SESSION['userID']));
                    $presetCheck = $stmt->fetchObject();

                    if ($presetCheck)
                    {
                        $dbConn = getConnection();
                        $insertSQL = "UPDATE Preset
                                      SET name =:nameT, message =:message, coins =:coins, q_coins =:qCoins
                                      WHERE Preset.id_pk =:presetID";
                        $stmt = $dbConn->prepare($insertSQL);
                        $stmt->execute(array(':presetID' => $presetSelected, ':nameT' => $presetName, ':message' => $presetMessage, ':coins' => $presetCoins, ':qCoins' => $presetQCoins));
                    }
                }
                else
                {
                    $dbConn = getConnection();
                    $insertSQL = "INSERT INTO Preset (name, message, coins, q_coins, teacher_id_fk)
                                  VALUES (:nameT, :message, :coins, :qCoins, :teacherID)
                                  ";
                    $stmt = $dbConn->prepare($insertSQL);
                    $stmt->execute(array(':nameT' => $presetName, ':message' => $presetMessage, ':coins' => $presetCoins, ':qCoins' => $presetQCoins, ':teacherID' => $_SESSION['userID'] ));
                }
            }

            //Get checkboxes

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
                        //Get players current coins
                        $dbConn = getConnection();
                        $SQLquery = "SELECT City.coins, City.q_coins
                                     FROM City
                                     WHERE City.player_id_fk =:studentID ";
                        $stmt = $dbConn->prepare($SQLquery);
                        $stmt->execute(array(':studentID' => $i));
                        $studentDetails = $stmt->fetchObject();
                        $studentCoins = $studentDetails->coins;
                        $studentQCoins = $studentDetails->q_coins;

                        //Add the players current coins to the coins received.

                        $newCoins = $studentCoins + $presetCoins;
                        $newQCoins = $studentQCoins + $presetQCoins;

                        //Update the coins in the city table for the player.
                        $dbConn = getConnection();
                        $updateSQL = "UPDATE City
                                      SET coins =:coins, q_coins =:qCoins
                                      WHERE player_id_fk = :studentID
                                      ";
                        $stmt = $dbConn->prepare($updateSQL);
                        $stmt->execute(array(':coins' => $newCoins, ':qCoins' => $newQCoins, 'studentID' => $i));

                        //Create Reward Record
                        $dbConn = getConnection();
                        $insertSQL = "INSERT INTO Rewards(teacher_id_fk, player_id_fk, message, coins, q_coins, date)
                                      VALUES (:teacherID, :studentID, :message, :coins, :qCoins, :dateT)
                                      ";
                        $stmt = $dbConn->prepare($insertSQL);
                        $stmt->execute(array(':teacherID' => $_SESSION['userID'], ':studentID' => $i, ':message' => $presetMessage, ':coins' => $presetCoins, ':qCoins' => $presetQCoins, ':dateT' => date('Y/m/d')));

                        //Get the reward ID created
                        $dbConn = getConnection();
                        $SQLquery = "SELECT id_pk
                                     FROM Rewards
                                     ORDER BY id_pk DESC";
                        $stmt = $dbConn->prepare($SQLquery);
                        $stmt->execute();
                        $rewardRecord = $stmt->fetchObject();
                        $newRewardID = $rewardRecord->id_pk;

                        if (($presetCoins == null)&&($presetQCoins == null) )
                        {
                            $typeT = 'Message';
                        }
                        else
                        {
                            $typeT = 'Reward';
                        }

                        //Create notification records
                        $dbConn = getConnection();
                        $insertSQL = "INSERT INTO Notification(from_id_fk, to_id_fk, message, date, type, reward_id_fk)
                                      VALUES (:userID, :studentID, :message, :dateT, :type, :rewardID)";
                        $stmt = $dbConn->prepare($insertSQL);
                        $stmt->execute(array(':userID' => $_SESSION['userID'], ':studentID' => $i, ':message' => $presetMessage, ':dateT' => date('Y/m/d'), ':type' => $typeT, ':rewardID' => $newRewardID));

                    }
                }
            }
           header('Location: classes.php');

        }
        else
        {
            echo "A problem occurred and no action has been taken.";
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