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
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='levelMeta' level='<?php echo $_SESSION['level']?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('buyHouses');
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
    turnButtBlack('buyHouses');

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
    $SQLquery = "SELECT AccommodationType.id_pk, AccommodationType.name, AccommodationType.price, AccommodationType.running_cost, AccommodationType.capacity, AccommodationType.base_quality, AccommodationType.level_unlocked, AccommodationType.xp_reward, AccommodationType.q_coin_reward
                 FROM AccommodationType
                 ORDER BY AccommodationType.level_unlocked ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getHouseImage($rowObj->id_pk);


        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateAccom($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price) 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='$imagePath'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td class='houseDetailsName'>{$rowObj->name}</td>
                                            <td class='houseDetailsType'>Price: {$rowObj->price} Coins</td>
                                            <td class='houseDetailsUpCost'>Upkeep Cost: {$rowObj->running_cost} Coins</td>
                                            <td class='houseDetailsUpgrades'>Capacity: {$rowObj->capacity}</td>
                                        </tr>
                                        <tr>
                                            <td class='houseDetailsOccupancy'>Quality: {$rowObj->base_quality}</td>
                                            <td class='houseDetailsType'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td class='houseDetailsUpCost'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td class='houseDetailsUpgrades'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
           echo "<td class='houseDetailsUpgrades'></td>";
        }

        echo "
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

<script type="text/javascript">
    window.addEventListener('load', function() {
        'use strict';
    });

    function validateAccom(accomType, accomLevel, accomPrice)
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var level = document.getElementById('levelMeta').getAttribute('level');
        var container = document.getElementById(accomType);
        const temp1 = 'link';
        const temp = temp1.concat(accomType);
        var link = document.getElementById(temp);

        coins = parseInt(coins);
        level = parseInt(level);

        var canBuy = false;

        if (coins >= accomPrice)
        {
            if (level >= accomLevel)
            {
                canBuy = true;
            }
        }

        if (canBuy == false)
        {
            link.href='#';
            container.style.backgroundColor = '#db4b12';
        }
        else
        {
            var text = 'buyHousesProcess.php?houseToBuy=';
            var textVar = text.concat(accomType);
            link.href = textVar;
            container.style.backgroundColor = '#55e67b';
        }
    }

    function mouseOff(accomType)
    {
        var container = document.getElementById(accomType);
        container.style.backgroundColor = '#ffffff';

    }


</script>

</body>

</html>