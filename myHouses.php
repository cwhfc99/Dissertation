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
checkLogin('myHouses');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    echo "<div class='mainGameContainer'>
                <div class='housesTopContainer'>
                    <ul class='housesButtContainer'>
                        <li class='housesButt' id='housesInfo'><a href='housesInfo.php'>Houses Info</a></li>
                        <li class='housesButt' id='buyHouses'><a href='buyHouses.php' id='noteRed'>Buy Houses</a></li>
                        <li class='housesButt' id='myHouses'><a href='myHouses.php'>My Houses</a></li>
                    </ul>
                </div>";
    turnButtBlack('myHouses');

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Accommodation.id_pk) AS counter, AVG(Accommodation.quality) AS quality, AVG(Accommodation.rent) AS rent, SUM(Accommodation.rent) AS totalRent, SUM(AccommodationType.running_cost) AS runningCost 
                  FROM Accommodation
                  LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                  WHERE Accommodation.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $housesInfo = $stmt->fetchObject();
    $housesCount = $housesInfo->counter;
    $housesQuality = round($housesInfo->quality, 0);
    $housesRent = round($housesInfo->rent, 0);
    $housesCost = round($housesInfo->runningCost, 0);
    $totalRent = round($housesInfo->totalRent, 0);

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.house_id_fk = 0
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt->fetchObject();
    $hCitizensCount = $citizensInfo->counter;

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter, AVG(Citizen.housing_happiness) AS avgHappiness
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt->fetchObject();
    $citizensCount = $citizensInfo->counter;

    $housingHappiness = round($citizensInfo->avgHappiness, 0);
    $homelessRate = (($hCitizensCount/$citizensCount)*100);
    $homelessRate = round($homelessRate, 1);


    echo "      <div class='housesHolder'>
                    <div class='myHousesLeftBar'>
                        <p style='margin-top: 10px; margin-left: 10px;'>View and manage all the current houses in your city from this page.</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Housing Happiness: {$housingHappiness}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Total Properties: {$housesCount}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Average Quality: {$housesQuality}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Average Rent: {$housesRent} <img src='images/coins_icon.png' width='15px' height='15px'></p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Total Yearly Upkeep: {$housesCost} <img src='images/coins_icon.png' width='15px' height='15px'></p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Homeless Citizens: {$hCitizensCount}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Homelessness Rate: {$homelessRate}%</p>
                    </div>";

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLquery = "SELECT Accommodation.id_pk, Accommodation.accom_type_id_fk, Accommodation.name, Accommodation.rent, Accommodation.quality, Accommodation.upgrade_a_bought, Accommodation.upgrade_b_bought, Accommodation.upgrade_c_bought, AccommodationType.capacity, AccommodationType.running_cost
                 FROM Accommodation
                 LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                 WHERE Accommodation.city_id_fk =:cityID
                 ORDER BY Accommodation.quality DESC
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

                    while($rowObj = $stmt->fetchObject())
                    {
                        $dbConn = getConnection();
                        $SQLquery = "SELECT COUNT(Room.id_pk) AS roomCounter
                                     FROM Room
                                     LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                                     WHERE Accommodation.id_pk =:accomID AND inhabited = 1
                                    ";
                        $stmt1 = $dbConn->prepare($SQLquery);
                        $stmt1->execute(array(':accomID' => $rowObj->id_pk));
                        $roomDetails = $stmt1->fetchObject();

                        $imagePath = getHouseImage($rowObj->accom_type_id_fk);
                        $type = getHouseType($rowObj->accom_type_id_fk);

                        $upgradesBought = 0;
                        if ($rowObj->upgrade_a_bought == 1)
                        {
                            $upgradesBought++;
                        }
                        if ($rowObj->upgrade_b_bought == 1)
                        {
                            $upgradesBought++;
                        }
                        if ($rowObj->upgrade_c_bought == 1)
                        {
                            $upgradesBought++;
                        }

                        echo "
                                <a href='individualHouse.php?houseSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='$imagePath'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td class='houseDetailsName'>{$rowObj->name}</td>
                                            <td class='houseDetailsType'>Type: {$type}</td>
                                            <td class='houseDetailsUpCost'></td>
                                            <td class='houseDetailsUpgrades'>Upgrades Bought: {$upgradesBought}/3</td>
                                        </tr>
                                        <tr>
                                            <td class='houseDetailsOccupancy'>Occupancy: {$roomDetails->roomCounter}/{$rowObj->capacity}</td>
                                            <td class='houseDetailsType'>Rent: {$rowObj->rent} Coins</td>
                                            <td class='houseDetailsUpCost'>Upkeep Cost: {$rowObj->running_cost} Coins</td>
                                            <td class='houseDetailsUpgrades'>Quality: {$rowObj->quality}</td>
                                        </tr>
                                    </table>
                                </div></a>
                             ";
                    }

    echo "          </div>                
               </div>";

    echo  "  </div>";

}
else
{
    notLoggedIn();
}

?>

</body>

</html>