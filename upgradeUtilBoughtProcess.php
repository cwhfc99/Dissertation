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
checkLogin('upgradeUtilBoughtProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $utilSelected = filter_has_var(INPUT_GET, 'utilSelected')
        ? $_GET['utilSelected'] : null;

    $utilType = filter_has_var(INPUT_GET, 'utilType')
        ? $_GET['utilType'] : null;

    $upgradeLet = filter_has_var(INPUT_GET, 'upgrade')
        ? $_GET['upgrade'] : null;

    $upgradeTypes = array('a','b','c');

    $utilTypes = array('fire', 'police');

    if (in_array($upgradeLet, $upgradeTypes))
    {
        if (in_array($utilType, $utilTypes))
        {
            if ($utilType == 'fire')
            {
                $tableName = 'FireBuilding';
                $tableName2 = 'FireType';
                $joiner = 'fire_type_id_fk';
                $redirect = 'individualFire.php?fireSelected=';
            }
            else
            {
                $tableName = 'PoliceBuilding';
                $tableName2 = 'PoliceType';
                $joiner = 'police_type_id_fk';
                $redirect = 'individualPolice.php?policeSelected=';

            }

            $dbConn = getConnection();
            $SQLselect = "SELECT id_pk
                          FROM $tableName
                          WHERE id_pk =:utilID AND city_id_fk =:cityID
                 ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':utilID' => $utilSelected, ':cityID' => $_SESSION['cityID']));
            $utilTest = $stmt->fetchObject();
            if ($utilTest)
            {

                //Get upgrade info
                $dbConn = getConnection();
                $SQLselect = "SELECT $tableName2.upgrade_a_cost, $tableName2.upgrade_b_cost, $tableName2.upgrade_c_cost, 
                                 $tableName2.upgrade_a_quality, $tableName2.upgrade_b_quality, $tableName2.upgrade_c_quality
                          FROM $tableName2
                          LEFT JOIN $tableName ON $tableName2.id_pk = $tableName.$joiner
                          WHERE $tableName.id_pk =:utilID
                         ";
                $stmt = $dbConn->prepare($SQLselect);
                $stmt->execute(array(':utilID' => $utilSelected));
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
                    $SQLselect = "SELECT $tableName.upgrade_a_bought, $tableName.upgrade_b_bought, $tableName.upgrade_c_bought
                              FROM $tableName
                              WHERE $tableName.id_pk =:utilID
                             ";
                    $stmt = $dbConn->prepare($SQLselect);
                    $stmt->execute(array(':utilID' => $utilSelected));
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
                        $SQLselect = "SELECT $tableName.quality
                              FROM $tableName
                              WHERE $tableName.id_pk =:utilID
                             ";
                        $stmt = $dbConn->prepare($SQLselect);
                        $stmt->execute(array(':utilID' => $utilSelected));
                        $accomQuality = $stmt->fetchObject();

                        $newQuality = ($accomQuality->quality)+($upgradeQuality);

                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE $tableName
                              SET quality = :quality
                              WHERE $tableName.id_pk =:utilID
                              ";
                        $stmt = $dbConn->prepare($SQLupdate);
                        $stmt->execute(array(':quality' => $newQuality, ':utilID' => $utilSelected));

                        if ($upgradeLet == 'a')
                        {
                            $dbConn = getConnection();
                            $SQLupdate = "UPDATE $tableName
                                  SET upgrade_a_bought = 1
                                  WHERE $tableName.id_pk =:utilID
                                 ";
                            $stmt = $dbConn->prepare($SQLupdate);
                            $stmt->execute(array(':utilID' => $utilSelected));
                        }
                        else if ($upgradeLet == 'b')
                        {
                            $dbConn = getConnection();
                            $SQLupdate = "UPDATE $tableName
                                  SET upgrade_b_bought = 1
                                  WHERE $tableName.id_pk =:utilID
                                 ";
                            $stmt = $dbConn->prepare($SQLupdate);
                            $stmt->execute(array(':utilID' => $utilSelected));
                        }
                        else if ($upgradeLet == 'c')
                        {
                            $dbConn = getConnection();
                            $SQLupdate = "UPDATE $tableName
                                  SET upgrade_c_bought = 1
                                  WHERE $tableName.id_pk =:utilID
                                 ";
                            $stmt = $dbConn->prepare($SQLupdate);
                            $stmt->execute(array(':utilID' => $utilSelected));
                        }
                        utilHappiness();
                        header('Location: '.$redirect.$utilSelected);
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