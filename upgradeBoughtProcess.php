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
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('upgradeBoughtProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $houseSelected = filter_has_var(INPUT_GET, 'houseSelected')
        ? $_GET['houseSelected'] : null;

    $upgradeLet = filter_has_var(INPUT_GET, 'upgrade')
        ? $_GET['upgrade'] : null;

    $upgradeTypes = array('a','b','c');

    if (in_array($upgradeLet, $upgradeTypes))
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT Accommodation.id_pk
                  FROM Accommodation
                  WHERE Accommodation.id_pk =:houseID AND Accommodation.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':houseID' => $houseSelected, ':cityID' => $_SESSION['cityID']));
        $houseTest = $stmt->fetchObject();
        if ($houseTest)
        {

            //Get upgrade info
            $dbConn = getConnection();
            $SQLselect = "SELECT AccommodationType.upgrade_a_cost, AccommodationType.upgrade_b_cost, AccommodationType.upgrade_c_cost, 
                                 AccommodationType.upgrade_a_quality, AccommodationType.upgrade_b_quality, AccommodationType.upgrade_c_quality
                          FROM AccommodationType
                          LEFT JOIN Accommodation ON AccommodationType.id_pk = Accommodation.accom_type_id_fk
                          WHERE Accommodation.id_pk =:houseID
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':houseID' => $houseSelected));
            $upgradeInfo = $stmt->fetchObject();

            //Check user can afford upgrade
            if ($upgradeLet == 'a')
            {
                $upgradeCost = $upgradeInfo->upgrade_a_cost;
                $upgradeQuality = $upgradeInfo->upgrade_a_quality;

            }
            else if ($upgradeLet == 'b')
            {
                $upgradeCost = $upgradeInfo->upgrade_b_cost;
                $upgradeQuality = $upgradeInfo->upgrade_b_quality;
            }
            else if ($upgradeLet == 'c')
            {
                $upgradeCost = $upgradeInfo->upgrade_c_cost;
                $upgradeQuality = $upgradeInfo->upgrade_c_quality;
            }

            if (($upgradeCost)<=($_SESSION['coins']))
            {
                //if the upgrade is already bought
                $dbConn = getConnection();
                $SQLselect = "SELECT Accommodation.upgrade_a_bought, Accommodation.upgrade_b_bought, Accommodation.upgrade_c_bought
                              FROM Accommodation
                              WHERE Accommodation.id_pk =:houseID
                             ";
                $stmt = $dbConn->prepare($SQLselect);
                $stmt->execute(array(':houseID' => $houseSelected));
                $upgradeBoughtDetails = $stmt->fetchObject();

                $upgradeBought = false;

                if ($upgradeLet == 'a')
                {
                    if ($upgradeBoughtDetails->upgrade_a_bought == 1)
                    {
                        $upgradeBought = true;
                    }

                }
                else if ($upgradeLet == 'b')
                {
                    if ($upgradeBoughtDetails->upgrade_b_bought == 1)
                    {
                        $upgradeBought = true;
                    }
                }
                else if ($upgradeLet == 'c')
                {
                    if ($upgradeBoughtDetails->upgrade_c_bought == 1)
                    {
                        $upgradeBought = true;
                    }
                }

                if ($upgradeBought == false)
                {
                    //Charge user
                    $newCityCoins = ($_SESSION['coins']-$upgradeCost);
                    $_SESSION['coins'] = $newCityCoins;

                    $dbConn = getConnection();
                    $SQLupdate = "UPDATE City
                              SET coins = :coins
                              WHERE City.id_pk =:cityID
                              ";
                    $stmt = $dbConn->prepare($SQLupdate);
                    $stmt->execute(array(':coins' => $newCityCoins, ':cityID' => $_SESSION['cityID']));

                    //Change Accommodation Quality
                    $dbConn = getConnection();
                    $SQLselect = "SELECT Accommodation.quality
                              FROM Accommodation
                              WHERE Accommodation.id_pk =:houseID
                             ";
                    $stmt = $dbConn->prepare($SQLselect);
                    $stmt->execute(array(':houseID' => $houseSelected));
                    $accomQuality = $stmt->fetchObject();

                    $newQuality = ($accomQuality->quality)+($upgradeQuality);

                    $dbConn = getConnection();
                    $SQLupdate = "UPDATE Accommodation
                              SET quality = :quality
                              WHERE Accommodation.id_pk =:houseID
                              ";
                    $stmt = $dbConn->prepare($SQLupdate);
                    $stmt->execute(array(':quality' => $newQuality, ':houseID' => $houseSelected));

                    if ($upgradeLet == 'a')
                    {
                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE Accommodation
                                  SET upgrade_a_bought = 1
                                  WHERE Accommodation.id_pk =:houseID
                                 ";
                        $stmt = $dbConn->prepare($SQLupdate);
                        $stmt->execute(array(':houseID' => $houseSelected));
                    }
                    else if ($upgradeLet == 'b')
                    {
                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE Accommodation
                                  SET upgrade_b_bought = 1
                                  WHERE Accommodation.id_pk =:houseID
                                 ";
                        $stmt = $dbConn->prepare($SQLupdate);
                        $stmt->execute(array(':houseID' => $houseSelected));
                    }
                    else if ($upgradeLet == 'c')
                    {

                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE Accommodation
                                  SET upgrade_c_bought = 1
                                  WHERE Accommodation.id_pk =:houseID
                                 ";
                        $stmt = $dbConn->prepare($SQLupdate);
                        $stmt->execute(array(':houseID' => $houseSelected));
                    }
                    houseAssingment();
                    header('Location: individualHouse.php?houseSelected='.$houseSelected);
                }
                else
                {
                    echo "You have already bought this upgrade."."<br>";
                    echo "<a href='menu.php'><button>Back</button></a>";
                }

            }
            else
            {
                echo "Insufficient funds to buy upgrade."."<br>";
                echo "<a href='menu.php'><button>Back</button></a>";
            }

        }
        else
        {
            echo "A problem occurred and the upgrade was not bought."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }
    }
    else
    {
        echo "A problem occurred and the upgrade was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}
else
{
    notLoggedIn();
}

?>

</body>

</html>