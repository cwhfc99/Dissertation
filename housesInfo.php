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
checkLogin('housesInfo');
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
    turnButtBlack('housesInfo');

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
                echo "<p>Your citizens need a place to stay and therefore their Housing Happiness is a key factor in their Overall Happiness.</p>
                      <p>It is important to ensure all your citizens have a home, as most will leave the city if they do not at the end of the year. Bear in mind that your citizens can only live in houses that they can afford depending on their wealth status:</p>
                      <ul>
                        <li>Broke: Up to 250 Coins</li>
                        <li>Poor: Up to 500 Coins</li>
                        <li>Medium: Up to 1000 Coins</li>
                        <li>Wealthy: Up to 2000 Coins</li>
                        <li>V. Wealthy: Up to 5000 Coins</li>
                      </ul>
                      <p>The most important factor for Housing Happiness is the quality of house that they citizens lives in. Citizens have an expectation of the quality of their home, based on their wealth status and will be unhappy if this expectation is not met. Quality can be improved by buying upgrades for the accommodation. Quality also decreases by 1 point every 5 years due to deterioration. This can be repaired for a small fee.</p>
                      <p>Citizens also do not want to be paying too much rent. The more expensive you set the rent, the less happy the citizen will be.</p>
                      <p>Citizens are automatically assigned to houses. The wealthier citizens are prioritised for the best quality houses whilst poorer citizens are assigned cheaper accommodations. Citizens will always be put in the best quality accommodation they can afford.</p>
";


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