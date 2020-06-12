<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$fireSelected = filter_has_var(INPUT_GET, 'fireSelected')
    ? $_GET['fireSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $fireSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualFire.php?fireSelected='.$fireSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT FireBuilding.id_pk
                  FROM FireBuilding
                  WHERE FireBuilding.id_pk =:fireID AND FireBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':fireID' => $fireSelected, ':cityID' => $_SESSION['cityID']));
    $fireTest = $stmt->fetchObject();
    if (!$fireTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT FireBuilding.id_pk
                      FROM FireBuilding
                      WHERE FireBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $fireTemp = $stmt->fetchObject();
        $fireSelected = $fireTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT FireBuilding.id_pk, FireType.name, FireBuilding.fire_type_id_fk, FireType.running_cost,  FireType.workers,
                          FireBuilding.quality, FireType.upgrade_a_name, FireType.upgrade_a_quality, FireType.upgrade_a_cost,
                         FireType.upgrade_b_name, FireType.upgrade_b_quality, FireType.upgrade_b_cost,
                         FireType.upgrade_c_name, FireType.upgrade_c_quality, FireType.upgrade_c_cost, FireType.base_quality,
                         FireBuilding.upgrade_a_bought, FireBuilding.upgrade_b_bought, FireBuilding.upgrade_c_bought, FireType.price
                  FROM FireBuilding
                  LEFT JOIN FireType ON FireBuilding.fire_type_id_fk = FireType.id_pk
                  WHERE FireBuilding.id_pk =:elecID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':elecID' => $fireSelected));
    $fireDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality, SUM(Job.salary) AS totSal
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 4
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $fireSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();

    $imagePath = getRecImage($fireDetails->fire_type_id_fk);
    $jobHappiness = getAverageJobHap(1, $fireSelected,4);


    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='images/fire_icon.png'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$fireDetails->name}</div>
                                    <form id='changeRent' action='changeSalaryProcess2.php' method='get' oninput='y.value=salChangeValue.value'>
                                       <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif;float: right; margin-top: -30px;'>0<input  style='margin-top: 15px; width: 330px; ' type='range' name='salChangeValue' value='{$jobDetails->salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                       <br><span><div style='margin-left: 540px;'>New Salary: <output form='changeRent' name='y'></output></div></span>
                                       <input type='submit' value='Change Salary' class='changeRentButt' id='changeRentButt'>
                                       <input type='hidden' name='empID' value='{$fireSelected}'>
                                       <input type='hidden' name='industry' value='1'>
                                       <input type='hidden' name='utilType' value='4'>
                                    </form>
                                </div>
    
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 200px;'>Firefighters: {$jobDetails->jobCounter}/{$fireDetails->workers}</td>
                                                    <td style='width: 200px;'>Job Quality: {$jobDetails->quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$jobHappiness}</td>
                                                </tr>
                                                <tr>
                                                            ";

    echo "<td style = 'width: 200px;' >Quality: {$fireDetails->quality}</td >";


    echo "                                          <td style='width: 200px;'>Upkeep Cost: {$fireDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 200px;'>Yearly Wages: {$jobDetails->totSal} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                </tr>
                                        </table>
                                     </div>
                                    
                        </div>";

    echo"                <div class='individualBusLower'>
                            <div class='individualHouseUpgradeA'>
                                <p style='text-align: center'>{$fireDetails->upgrade_a_name}</p>
                                <p style='text-align: center'>+ {$fireDetails->upgrade_a_quality} Quality</p>";
    $standardQuality = $fireDetails->base_quality;
    if ($fireDetails->upgrade_a_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeALink'><div class='upgradeButt' id='upgradeAButt' cost='{$fireDetails->upgrade_a_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$fireDetails->upgrade_a_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $fireDetails->upgrade_a_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeALink'><div class='upgradeButt' id='upgradeAButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }

     echo "                  </div>
                            <div class='individualHouseUpgradeB'>
                                <p style='text-align: center'>{$fireDetails->upgrade_b_name}</p>
                                <p style='text-align: center'>+ {$fireDetails->upgrade_b_quality} Quality</p>";
    if ($fireDetails->upgrade_b_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeBLink'><div class='upgradeButt' id='upgradeBButt' cost='{$fireDetails->upgrade_b_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$fireDetails->upgrade_b_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $fireDetails->upgrade_b_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeBLink'><div class='upgradeButt' id='upgradeBButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }

    echo "
                            </div>
                            <div class='individualHouseUpgradeC'>
                                <p style='text-align: center'>{$fireDetails->upgrade_c_name}</p>
                                <p style='text-align: center'>+ {$fireDetails->upgrade_c_quality} Quality</p>";
    if ($fireDetails->upgrade_c_bought == 0)
    {
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeCLink'><div class='upgradeButt' id='upgradeCButt' cost='{$fireDetails->upgrade_c_cost}'><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$fireDetails->upgrade_c_cost}</p></div></a>";
    }
    else
    {
        $standardQuality = $standardQuality + $fireDetails->upgrade_c_quality;
        echo "                  <a href='#' class='upgradeButtLink' id='upgradeCLink'><div class='upgradeButt' id='upgradeCButt' cost='bought'><p style='margin-top: 3px; margin-left: 77px'>Bought</p></div></a>";
    }


    echo "                            </div>
                            <div class='individualRepair'>";

    echo "<div class='demolishButt' onclick='displayLoading(\"closeUtilProcess.php?utilType=fire&utilSelected={$fireSelected}\")'><p style='margin-top: 3px; margin-left: 55px'>Close Utility</p></div>
";

    if ($fireDetails->quality < $standardQuality)
    {
        $qualityDifference = $standardQuality - ($fireDetails->quality);
        $repairCost = ($qualityDifference/100)*($fireDetails->price);
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
            var text = 'upgradeUtilBoughtProcess.php?utilType=fire&upgrade=a&utilSelected=';
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
            var text = 'upgradeUtilBoughtProcess.php?utilType=fire&upgrade=b&utilSelected=';
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
            var text = 'upgradeUtilBoughtProcess.php?utilType=fire&upgrade=c&utilSelected=';
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
            var text = 'repairUtilProcess.php?utilType=fire&utilSelected=';
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