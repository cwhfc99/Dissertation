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
checkLogin('repairUtilProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $utilSelected = filter_has_var(INPUT_GET, 'utilSelected')
        ? $_GET['utilSelected'] : null;

    $utilType = filter_has_var(INPUT_GET, 'utilType')
        ? $_GET['utilType'] : null;

    $utilTypes = array('fire', 'police', 'healthcare');

    if (in_array($utilType, $utilTypes))
    {
        if ($utilType == 'fire')
        {
            $tableName = 'FireBuilding';
            $tableName2 = 'FireType';
            $joiner = 'fire_type_id_fk';
            $redirect = 'individualFire.php?fireSelected=';
        }
        else if ($utilType == 'police')
        {
            $tableName = 'PoliceBuilding';
            $tableName2 = 'PoliceType';
            $joiner = 'police_type_id_fk';
            $redirect = 'individualPolice.php?policeSelected=';
        }
        else
        {
            $tableName = 'HealthBuilding';
            $tableName2 = 'HealthType';
            $joiner = 'health_type_id_fk';
            $redirect = 'individualHealth.php?healthSelected=';
        }


        $dbConn = getConnection();
        $SQLselect = "SELECT id_pk
                  FROM $tableName
                  WHERE $tableName.id_pk =:utilID AND $tableName.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilSelected, ':cityID' => $_SESSION['cityID']));
        $utilTest = $stmt->fetchObject();
        if ($utilTest)
        {

            //Get difference in quality
            if ($utilType == 'healthcare')
            {
                $dbConn = getConnection();
                $SQLselect = "SELECT $tableName.quality, 
                         $tableName2.base_quality, $tableName2.price 
                          FROM $tableName
                          LEFT JOIN $tableName2 ON $tableName.$joiner = $tableName2.id_pk
                          WHERE $tableName.id_pk =:utilID
                 ";
                $stmt = $dbConn->prepare($SQLselect);
                $stmt->execute(array(':utilID' => $utilSelected));
                $houseDetails = $stmt->fetchObject();

                $standardQuality = $houseDetails->base_quality;

            }
            else
            {
                $dbConn = getConnection();
                $SQLselect = "SELECT $tableName.quality, 
                         $tableName.upgrade_a_bought, $tableName.upgrade_b_bought, $tableName.upgrade_c_bought, 
                         $tableName2.upgrade_a_quality, $tableName2.upgrade_b_quality, $tableName2.upgrade_c_quality,
                         $tableName2.base_quality, $tableName2.price 
                          FROM $tableName
                          LEFT JOIN $tableName2 ON $tableName.$joiner = $tableName2.id_pk
                          WHERE $tableName.id_pk =:utilID
                 ";
                $stmt = $dbConn->prepare($SQLselect);
                $stmt->execute(array(':utilID' => $utilSelected));
                $houseDetails = $stmt->fetchObject();

                $standardQuality = $houseDetails->base_quality;

                if ($houseDetails->upgrade_a_bought == 1)
                {
                    $standardQuality = $standardQuality + $houseDetails->upgrade_a_quality;
                }
                if ($houseDetails->upgrade_b_bought == 1)
                {
                    $standardQuality = $standardQuality + $houseDetails->upgrade_b_quality;
                }
                if ($houseDetails->upgrade_c_bought == 1)
                {
                    $standardQuality = $standardQuality + $houseDetails->upgrade_c_quality;
                }
            }


            $qualityDifference = $standardQuality - ($houseDetails->quality);
            $repairCost = ($qualityDifference/100)*($houseDetails->price);
            $repairCost = round($repairCost, 0);

            //Check if user can afford repair cost
            if ($repairCost <= $_SESSION['coins'])
            {
                //Set quality column in accommodation table
                $dbConn = getConnection();
                $SQLupdate = "UPDATE $tableName
                          SET quality = :quality
                          WHERE $tableName.id_pk =:utilID
                              ";
                $stmt = $dbConn->prepare($SQLupdate);
                $stmt->execute(array(':quality' => $standardQuality, ':utilID' => $utilSelected));

                //Charge User
                chargeUser($repairCost);

                utilHappiness();
                header('Location: '.$redirect.$utilSelected);

            }
            else
            {
                echo "Insufficient funds to complete repair."."<br>";
                echo "<a href='menu.php'><button>Back</button></a>";
            }

        }
        else
        {
            echo "A problem occurred and the utility was not repaired."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }
    }
    else
    {
        echo "A problem occurred and the utility was not repaired."."<br>";
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