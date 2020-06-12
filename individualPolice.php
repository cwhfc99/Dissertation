<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$policeSelected = filter_has_var(INPUT_GET, 'policeSelected')
    ? $_GET['policeSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $policeSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualPolice.php?policeSelected='.$policeSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT PoliceBuilding.id_pk
                  FROM PoliceBuilding
                  WHERE PoliceBuilding.id_pk =:policeID AND PoliceBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':policeID' => $policeSelected, ':cityID' => $_SESSION['cityID']));
    $policeTest = $stmt->fetchObject();
    if (!$policeTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT PoliceBuilding.id_pk
                      FROM PoliceBuilding
                      WHERE PoliceBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $policeTemp = $stmt->fetchObject();
        $policeSelected = $policeTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT PoliceBuilding.id_pk, PoliceType.name, PoliceBuilding.police_type_id_fk, PoliceType.running_cost,  PoliceType.workers,
                          PoliceBuilding.quality, PoliceType.upgrade_a_name, PoliceType.upgrade_a_quality, PoliceType.upgrade_a_cost,
                         PoliceType.upgrade_b_name, PoliceType.upgrade_b_quality, PoliceType.upgrade_b_cost,
                         PoliceType.upgrade_c_name, PoliceType.upgrade_c_quality, PoliceType.upgrade_c_cost, PoliceType.base_quality,
                         PoliceBuilding.upgrade_a_bought, PoliceBuilding.upgrade_b_bought, PoliceBuilding.upgrade_c_bought, PoliceType.price
                  FROM PoliceBuilding
                  LEFT JOIN PoliceType ON PoliceBuilding.police_type_id_fk = PoliceType.id_pk
                  WHERE PoliceBuilding.id_pk =:elecID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':elecID' => $policeSelected));
    $policeDetails = $stmt->fetchObject();

    $imagePath = getRecImage($policeDetails->police_type_id_fk);

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality, SUM(Job.salary) AS totSal
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 5
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $policeSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();
    $jobHappiness = getAverageJobHap(1, $policeSelected,5);


    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='images/police_icon.png'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$policeDetails->name}</div>
                                    <form id='changeRent' action='changeSalaryProcess2.php' method='get' oninput='y.value=salChangeValue.value'>
                                       <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif;float: right; margin-top: -30px;'>0<input  style='margin-top: 15px; width: 330px; ' type='range' name='salChangeValue' value='{$jobDetails->salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                       <br><span><div style='margin-left: 540px;'>New Salary: <output form='changeRent' name='y'></output></div></span>
                                       <input type='submit' value='Change Salary' class='changeRentButt' id='changeRentButt'>
                                       <input type='hidden' name='empID' value='{$policeSelected}'>
                                       <input type='hidden' name='industry' value='1'>
                                       <input type='hidden' name='utilType' value='5'>
                                    </form>
                                </div>
    
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 200px;'>Police Officers: {$jobDetails->jobCounter}/{$policeDetails->workers}</td>
                                                    <td style='width: 200px;'>Job Quality: {$jobDetails->quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$jobHappiness}</td>
                                                </tr>
                                                <tr>
                                                            ";

    echo "<td style = 'width: 200px;' >Quality: {$policeDetails->quality}</td >";


    echo "                                          <td style='width: 200px;'>Upkeep Cost: {$policeDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 200px;'>Yearly Wages: {$jobDetails->totSal} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                </tr>
                                        </table>
                                     </div>
                                    
                        </div>";

    echo"                <div class='individualBusLower'>
                            <div class='individualHouseUpgradeA'>
                                <p style='text-align: center'>{$policeDetails->upgrade_a_name}</p>
                                <p style='text-align: center'>+ {$policeDetails->upgrade_a_quality} Quality</p>";
    $standardQuality = $policeDetails->base_quality;
    if ($policeDetails->upgrade_a_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeALink'><div class='upgradeButt' id='upgradeAButt' cost='{$policeDetails->upgrade_a_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$policeDetails->upgrade_a_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $policeDetails->upgrade_a_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeALink'><div class='upgradeButt' id='upgradeAButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }

    echo "                  </div>
                            <div class='individualHouseUpgradeB'>
                                <p style='text-align: center'>{$policeDetails->upgrade_b_name}</p>
                                <p style='text-align: center'>+ {$policeDetails->upgrade_b_quality} Quality</p>";
    if ($policeDetails->upgrade_b_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeBLink'><div class='upgradeButt' id='upgradeBButt' cost='{$policeDetails->upgrade_b_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$policeDetails->upgrade_b_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $policeDetails->upgrade_b_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeBLink'><div class='upgradeButt' id='upgradeBButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }

    echo "
                            </div>
                            <div class='individualHouseUpgradeC'>
                                <p style='text-align: center'>{$policeDetails->upgrade_c_name}</p>
                                <p style='text-align: center'>+ {$policeDetails->upgrade_c_quality} Quality</p>";
    if ($policeDetails->upgrade_c_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeCLink'><div class='upgradeButt' id='upgradeCButt' cost='{$policeDetails->upgrade_c_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$policeDetails->upgrade_c_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $policeDetails->upgrade_c_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeCLink'><div class='upgradeButt' id='upgradeCButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }


    echo "                            </div>
                            <div class='individualRepair'>";

    echo "<div class='demolishButt' onclick='displayLoading(\"closeUtilProcess.php?utilType=police&utilSelected={$policeSelected}\")'><p style='margin-top: 3px; margin-left: 55px'>Close Utility</p></div>
";

    if ($policeDetails->quality < $standardQuality)
    {
        $qualityDifference = $standardQuality - ($policeDetails->quality);
        $repairCost = ($qualityDifference/100)*($policeDetails->price);
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
    var upgradeAButt = document.getElementById('upgradeAButt');
    var upgradeBButt = document.getElementById('upgradeBButt');
    var upgradeCButt = document.getElementById('upgradeCButt');
    var repairButt = document.getElementById('repairButt');

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
            var text = 'upgradeUtilBoughtProcess.php?utilType=police&upgrade=a&utilSelected=';
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
            var text = 'upgradeUtilBoughtProcess.php?utilType=police&upgrade=b&utilSelected=';
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
            var text = 'upgradeUtilBoughtProcess.php?utilType=police&upgrade=c&utilSelected=';
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
            var text = 'repairUtilProcess.php?utilType=police&utilSelected=';
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

</script>

</body>

</html>