<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$houseSelected = filter_has_var(INPUT_GET, 'houseSelected')
    ? $_GET['houseSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $houseSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualHouse.php?houseSelected='.$houseSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');



    echo "<div class='mainGameContainer'>
                <div class='housesTopContainer'>
                    <ul class='housesButtContainer'>
                        <li class='housesButt' id='housesInfo'><a href='housesInfo.php'>Houses Info</a></li>                    
                        <li class='housesButt' id='buyHouses'><a href='buyHouses.php' id='noteRed'>Buy Houses</a></li>
                        <li class='housesButt' id='myHouses'><a href='myHouses.php' id='noteRed'>My Houses</a></li>
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

    $dbConn = getConnection();
    $SQLselect = "SELECT Accommodation.id_pk
                  FROM Accommodation
                  WHERE Accommodation.id_pk =:houseID AND Accommodation.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':houseID' => $houseSelected, ':cityID' => $_SESSION['cityID']));
    $houseTest = $stmt->fetchObject();
    if (!$houseTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT Accommodation.id_pk
                      FROM Accommodation
                      WHERE Accommodation.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $houseTemp = $stmt->fetchObject();
        $houseSelected = $houseTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT Accommodation.id_pk, Accommodation.accom_type_id_fk, Accommodation.name, Accommodation.rent, Accommodation.quality, 
                         Accommodation.upgrade_a_bought, Accommodation.upgrade_b_bought, Accommodation.upgrade_c_bought, AccommodationType.capacity, 
                         AccommodationType.running_cost, AccommodationType.lower_rent_limit, AccommodationType.upper_rent_limit,
                         AccommodationType.upgrade_a_name, AccommodationType.upgrade_a_quality, AccommodationType.upgrade_a_cost,
                         AccommodationType.upgrade_b_name, AccommodationType.upgrade_b_quality, AccommodationType.upgrade_b_cost,
                         AccommodationType.upgrade_c_name, AccommodationType.upgrade_c_quality, AccommodationType.upgrade_c_cost,
                         AccommodationType.base_quality, AccommodationType.price 
                  FROM Accommodation
                  LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                  WHERE Accommodation.id_pk =:houseID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':houseID' => $houseSelected));
    $houseDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Room.id_pk) AS roomCounter
                 FROM Room
                 LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                 WHERE Accommodation.id_pk =:accomID AND inhabited = 1
                ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':accomID' => $houseSelected));
    $roomDetails = $stmt1->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT AVG(Citizen.housing_happiness) AS housingHappiness
                 FROM Citizen
                 LEFT JOIN Room ON Citizen.house_id_fk = Room.id_pk
                 LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                 WHERE Accommodation.id_pk =:accomID
                ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':accomID' => $houseSelected));
    $occupantsDetails = $stmt1->fetchObject();
    $occupantsHappiness = round($occupantsDetails->housingHappiness, 0);

    $imagePath = getHouseImage($houseDetails->accom_type_id_fk);
    $yearlyRent = (($houseDetails->rent)*($roomDetails->roomCounter));

    echo "          <div class='myHousesMain'>
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='$imagePath'></div>
                                <div class='indHouseUpperUpper'>
                                    <form id='renameHouse' action='renameHouseProcess.php' method='get'>
                                       <input type='text' name='renameValue' value=' {$houseDetails->name}' id='renameValue'>
                                       <input type='submit' value='Rename' class='renameHouseButt' id='renameHouseButt'>
                                       <input type='hidden' name='houseSelected' value='{$houseSelected}'>
                                    </form>
                                    <form id='changeRent' action='changeRentProcess.php' method='get' oninput='x.value=rentChangeValue.value'>
                                       <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif;'>{$houseDetails->lower_rent_limit}<input  style='margin-top: 15px; width: 330px;' type='range' name='rentChangeValue' value='{$houseDetails->rent}' id='rentChangeValue' min='{$houseDetails->lower_rent_limit}' max='{$houseDetails->upper_rent_limit}'>{$houseDetails->upper_rent_limit}</span>
                                       <br><br><span><div style='margin-left: 200px;'>New Rent: <output form='changeRent' name='x'></output></div></span>
                                       <input type='submit' value='Change Rent' class='changeRentButt' id='changeRentButt'>
                                       <input type='hidden' name='houseSelected' value='{$houseSelected}'>
                                    </form>
                                </div>
                                <table class='inHouseInfoTable'>
                                    <tr>
                                        <td style='width: 250px;'>Rent: {$houseDetails->rent} Coins</td>
                                        <td style='width: 180px;'>Occupants: {$roomDetails->roomCounter}/{$houseDetails->capacity}</td>
                                        <td style='text-align: right;'>Average Occupants Housing Happiness: {$occupantsHappiness}</td>
                                    </tr>
                                    <tr>
                                        <td>Yearly Rent Collected: {$yearlyRent} Coins</td>
                                        <td>Upkeep Cost: {$houseDetails->running_cost} Coins</td>
                                        <td style='text-align: right;'>Quality: {$houseDetails->quality}</td>
                                    </tr>
                                </table>
                        </div>";



    echo"                <div class='individualHouseLower'>
                            <div class='individualHouseUpgradeA'>
                                <p style='text-align: center'>{$houseDetails->upgrade_a_name}</p>
                                <p style='text-align: center'>+ {$houseDetails->upgrade_a_quality} Quality</p>";
    $standardQuality = $houseDetails->base_quality;
    if ($houseDetails->upgrade_a_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeALink'><div class='upgradeButt' id='upgradeAButt' cost='{$houseDetails->upgrade_a_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$houseDetails->upgrade_a_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $houseDetails->upgrade_a_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeALink'><div class='upgradeButt' id='upgradeAButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }

     echo "                  </div>
                            <div class='individualHouseUpgradeB'>
                                <p style='text-align: center'>{$houseDetails->upgrade_b_name}</p>
                                <p style='text-align: center'>+ {$houseDetails->upgrade_b_quality} Quality</p>";
    if ($houseDetails->upgrade_b_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeBLink'><div class='upgradeButt' id='upgradeBButt' cost='{$houseDetails->upgrade_b_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$houseDetails->upgrade_b_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $houseDetails->upgrade_b_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeBLink'><div class='upgradeButt' id='upgradeBButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }

    echo "
                            </div>
                            <div class='individualHouseUpgradeC'>
                                <p style='text-align: center'>{$houseDetails->upgrade_c_name}</p>
                                <p style='text-align: center'>+ {$houseDetails->upgrade_c_quality} Quality</p>";
    if ($houseDetails->upgrade_c_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeCLink'><div class='upgradeButt' id='upgradeCButt' cost='{$houseDetails->upgrade_c_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$houseDetails->upgrade_c_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $houseDetails->upgrade_c_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeCLink'><div class='upgradeButt' id='upgradeCButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }


    echo "                            </div>
                            <div class='individualRepair'>";

    echo "<div class='demolishButt' onclick='displayLoading(\"demolishHouseProcess.php?houseSelected={$houseSelected}\")'><p style='margin-top: 3px; margin-left: 70px'>Demolish</p></div>
          
          
          ";

    if ($houseDetails->quality < $standardQuality)
    {
        $qualityDifference = $standardQuality - ($houseDetails->quality);
        $repairCost = ($qualityDifference/100)*($houseDetails->price);
        $repairCost = round($repairCost, 0);
        echo "                  <p style='margin-top: -1px;'>Repair quality points lost to deterioration: +{$qualityDifference} Quality</p>
        <a href='#' class='repairButtLink' id='repairLink'><div class='repairButt' id='repairButt' cost={$repairCost}><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$repairCost}</p></div></a>";

    }

    echo "                      </div>
                    
                        </div>   
                    </div>                

                 </div>";

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

    var bool = false;
    var input = document.getElementById('renameValue');
    var butt = document.getElementById('renameHouseButt');
    var upgradeAButt = document.getElementById('upgradeAButt');
    var upgradeBButt = document.getElementById('upgradeBButt');
    var upgradeCButt = document.getElementById('upgradeCButt');
    var repairButt = document.getElementById('repairButt');


    input.addEventListener("input", validateInput);

    upgradeAButt.addEventListener("mouseover", upgradeAValidation);
    upgradeAButt.addEventListener("mouseout", upgradeAMouseOff);

    upgradeBButt.addEventListener("mouseover", upgradeBValidation);
    upgradeBButt.addEventListener("mouseout", upgradeBMouseOff);

    upgradeCButt.addEventListener("mouseover", upgradeCValidation);
    upgradeCButt.addEventListener("mouseout", upgradeCMouseOff);

    repairButt.addEventListener("mouseover", repairValidation);
    repairButt.addEventListener("mouseout", repairMouseOff);


    function upgradeAValidation()
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var houseSelected = document.getElementById('houseMeta').getAttribute('houseSelected');
        coins = parseInt(coins);
        var upgradeCost = document.getElementById('upgradeAButt').getAttribute('cost');
        var link = document.getElementById('upgradeALink');
        if (upgradeCost <= coins)
        {
            var text = 'upgradeBoughtProcess.php?upgrade=a&houseSelected=';
            var linkVar = text.concat(houseSelected);
            link.href = linkVar;
            upgradeAButt.style.backgroundColor = '#55e67b';
        }
        else
        {
            link.href = '#';
            upgradeAButt.style.backgroundColor = '#db4b12';
        }
    }

    function upgradeAMouseOff()
    {
        upgradeAButt.style.backgroundColor = '#bebebe';
    }

    function upgradeBValidation()
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var houseSelected = document.getElementById('houseMeta').getAttribute('houseSelected');
        coins = parseInt(coins);
        var upgradeCost = document.getElementById('upgradeBButt').getAttribute('cost');
        var link = document.getElementById('upgradeBLink');
        if (upgradeCost <= coins)
        {
            var text = 'upgradeBoughtProcess.php?upgrade=b&houseSelected=';
            var linkVar = text.concat(houseSelected);
            link.href = linkVar;
            upgradeBButt.style.backgroundColor = '#55e67b';
        }
        else
        {
            link.href = '#';
            upgradeBButt.style.backgroundColor = '#db4b12';
        }
    }

    function upgradeBMouseOff()
    {
        upgradeBButt.style.backgroundColor = '#bebebe';
    }

    function upgradeCValidation()
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var houseSelected = document.getElementById('houseMeta').getAttribute('houseSelected');
        coins = parseInt(coins);
        var upgradeCost = document.getElementById('upgradeCButt').getAttribute('cost');
        var link = document.getElementById('upgradeCLink');
        if (upgradeCost <= coins)
        {
            var text = 'upgradeBoughtProcess.php?upgrade=c&houseSelected=';
            var linkVar = text.concat(houseSelected);
            link.href = linkVar;
            upgradeCButt.style.backgroundColor = '#55e67b';
        }
        else
        {
            link.href = '#';
            upgradeCButt.style.backgroundColor = '#db4b12';
        }
    }

    function upgradeCMouseOff()
    {
        upgradeCButt.style.backgroundColor = '#bebebe';
    }

    function repairValidation()
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var houseSelected = document.getElementById('houseMeta').getAttribute('houseSelected');
        coins = parseInt(coins);
        var upgradeCost = document.getElementById('repairButt').getAttribute('cost');
        var link = document.getElementById('repairLink');
        if (upgradeCost <= coins)
        {
            var text = 'repairProcess.php?houseSelected=';
            var linkVar = text.concat(houseSelected);
            link.href = linkVar;
            repairButt.style.backgroundColor = '#55e67b';
        }
        else
        {
            link.href = '#';
            repairButt.style.backgroundColor = '#db4b12';
        }
    }

    function repairMouseOff()
    {
        repairButt.style.backgroundColor = '#bebebe';
    }

    function validateInput()
    {
        input.style.borderWidth = "2px";
        if ((input.value) == "" || (input.value.length) > 15) //If the first name is empty or over 20 character.
        {
            bool = false;
            input.style.borderColor = "red";
        }
        else
        {
            bool = true;
            input.style.borderColor = "green";
        }
        validateForm();
    }

    function validateForm()
    {
        if (bool == false)
        {
            butt.disabled = true;
        }
        else
        {
            butt.disabled = false;
        }
    }

</script>

</body>

</html>