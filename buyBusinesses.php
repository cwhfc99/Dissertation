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
checkLogin('buyBusinesses');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    echo "<div class='mainGameContainer'>
                <div class='housesTopContainer'>
                    <ul class='housesButtContainer'>
                        <li class='housesButt' id='housesInfo'><a href='businessesInfo.php'>Businesses Info</a></li>
                        <li class='housesButt' id='buyHouses'><a href='buyBusinesses.php' id='noteRed'>Buy Businesses</a></li>
                        <li class='housesButt' id='myHouses'><a href='myBusinesses.php'>My Businesses</a></li>
                    </ul>
                </div>";
    turnButtBlack('buyHouses');

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Business.id_pk) AS counter, AVG(BusinessType.job_quality) AS avgQuality
                  FROM Business
                  LEFT JOIN BusinessType ON Business.bus_type_id_fk = BusinessType.id_pk
                  WHERE Business.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $businessInfo = $stmt->fetchObject();
    $businessCount = $businessInfo->counter;
    $avgQuality = $businessInfo->avgQuality;
    $avgQuality = round($avgQuality, 0);

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk = 0 AND Citizen.can_work = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt->fetchObject();
    $eCitizensCount = $citizensInfo->counter;

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter, AVG(Citizen.job_happiness) AS avgHappiness
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.can_work = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo2 = $stmt->fetchObject();

    $citizensCount = $citizensInfo2->counter;

    $jobHappiness = round($citizensInfo->avgHappiness, 0);
    $unemployRate = (($eCitizensCount/$citizensCount)*100);
    $unemployRate = round($unemployRate, 1);

    $dbConn = getConnection();
    $SQLquery = "SELECT Business.id_pk, Business.yearly_revenue, BusinessType.job_a_quantity, BusinessType.job_b_quantity, 
                        BusinessType.job_c_quantity, BusinessType.job_quality, BusinessType.running_cost, Business.job_a_salary, Business.job_b_salary, Business.job_c_salary
                 FROM Business
                 LEFT JOIN BusinessType ON Business.bus_type_id_fk = BusinessType.id_pk
                 WHERE Business.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $totalProfit = 0;

    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS countA
                     FROM Job
                     WHERE Job.employer_id_fk =:busID AND Job.citizen_employed = 1 AND Job.employer_role_type = 'a'
                     ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':busID' => $rowObj->id_pk));
        $countA = $stmt1->fetchObject();

        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS countB
                     FROM Job
                     WHERE Job.employer_id_fk =:busID AND Job.citizen_employed = 1 AND Job.employer_role_type = 'b'
                     ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':busID' => $rowObj->id_pk));
        $countB = $stmt1->fetchObject();

        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS countC
                     FROM Job
                     WHERE Job.employer_id_fk =:busID AND Job.citizen_employed = 1 AND Job.employer_role_type = 'c'
                     ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':busID' => $rowObj->id_pk));
        $countC = $stmt1->fetchObject();

        $yearlyWages = ($countA->countA * $rowObj->job_a_salary)+($countB->countB * $rowObj->job_b_salary)+($countC->countC * $rowObj->job_c_salary);
        $profitLoss = ($rowObj->yearly_revenue)-($rowObj->running_cost)-($yearlyWages);

        $totalProfit = $totalProfit + $profitLoss;
    }

    if ($businessCount > 0)
    {
        $avgProfit = $totalProfit/$businessCount;
    }
    else
    {
        $avgProfit = 0;
    }
    $avgProfit = round($avgProfit, 0);


    echo "      <div class='housesHolder'>
                    <div class='myHousesLeftBar'>
                        <p style='margin-top: 10px; margin-left: 10px;'>View and manage all the businesses in your city from this page.</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Job Happiness: {$jobHappiness}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Total Businesses: {$businessCount}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Average Job Quality: {$avgQuality}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Average Profit/Loss: {$avgProfit} <img src='images/coins_icon.png' width='15px' height='15px'></p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Total Yearly Profit/Loss: {$totalProfit} <img src='images/coins_icon.png' width='15px' height='15px'></p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Unemployed Citizens: {$eCitizensCount}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Unemployment Rate: {$unemployRate}%</p>
                    </div>";

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLquery = "SELECT BusinessType.id_pk, BusinessType.name, BusinessType.price, BusinessType.running_cost,
                        BusinessType.job_quality, BusinessType.level_unlocked, BusinessType.base_revenue, BusinessType.xp_reward,
                        BusinessType.q_coin_reward
                 FROM BusinessType
                 ORDER BY BusinessType.level_unlocked ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $reduction = getBusReducation();

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getBusImage($rowObj->id_pk);
        $potRev = ($rowObj->base_revenue)*1.05;

        $price = round(($rowObj->price*$reduction),0);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price) 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='$imagePath'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td class='houseDetailsName'>{$rowObj->name}</td>
                                            <td class='houseDetailsType'>Investment: {$price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td class='houseDetailsUpCost'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td class='houseDetailsUpgrades'>Potential Revenue: {$potRev} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                        </tr>
                                        <tr>
                                            <td class='houseDetailsOccupancy'>Job Quality: {$rowObj->job_quality}</td>
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

    function validateBus(accomType, accomLevel, accomPrice)
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
            var text = 'buyBusinessProcess.php?busToBuy=';
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