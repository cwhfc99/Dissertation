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
checkLogin('repairProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $houseSelected = filter_has_var(INPUT_GET, 'houseSelected')
        ? $_GET['houseSelected'] : null;


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
        //Get difference in quality
        $dbConn = getConnection();
        $SQLselect = "SELECT Accommodation.quality, 
                         Accommodation.upgrade_a_bought, Accommodation.upgrade_b_bought, Accommodation.upgrade_c_bought, 
                         AccommodationType.upgrade_a_quality, AccommodationType.upgrade_b_quality, AccommodationType.upgrade_c_quality,
                         AccommodationType.base_quality, AccommodationType.price 
                  FROM Accommodation
                  LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                  WHERE Accommodation.id_pk =:houseID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':houseID' => $houseSelected));
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

        $qualityDifference = $standardQuality - ($houseDetails->quality);
        $repairCost = ($qualityDifference/100)*($houseDetails->price);
        $repairCost = round($repairCost, 0);

        //Check if user can afford repair cost
        if ($repairCost <= $_SESSION['coins'])
        {
            //Set quality column in accommodation table
            $dbConn = getConnection();
            $SQLupdate = "UPDATE Accommodation
                          SET quality = :quality
                          WHERE Accommodation.id_pk =:accomID
                              ";
            $stmt = $dbConn->prepare($SQLupdate);
            $stmt->execute(array(':quality' => $standardQuality, ':accomID' => $houseSelected));

            //Charge User
            chargeUser($repairCost);

            houseAssingment();
            header('Location: individualHouse.php?houseSelected='.$houseSelected);

        }
        else
        {
            echo "Insufficient funds to complete repair."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the accommodation was not repaired."."<br>";
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