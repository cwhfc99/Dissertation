<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$healthSelected = filter_has_var(INPUT_GET, 'healthSelected')
    ? $_GET['healthSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $healthSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualHealth.php?healthSelected='.$healthSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT HealthBuilding.id_pk
                  FROM HealthBuilding
                  WHERE HealthBuilding.id_pk =:healthID AND HealthBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':healthID' => $healthSelected, ':cityID' => $_SESSION['cityID']));
    $healthTest = $stmt->fetchObject();
    if (!$healthTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT HealthBuilding.id_pk
                      FROM HealthBuilding
                      WHERE HealthBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $healthTemp = $stmt->fetchObject();
        $healthSelected = $healthTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT HealthBuilding.id_pk, HealthType.name, HealthBuilding.health_type_id_fk, HealthType.running_cost,  HealthType.workers, HealthBuilding.quality, HealthType.base_quality, HealthType.price
                  FROM HealthBuilding
                  LEFT JOIN HealthType ON HealthBuilding.health_type_id_fk = HealthType.id_pk
                  WHERE HealthBuilding.id_pk =:healthID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':healthID' => $healthSelected));
    $healthDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality, SUM(Job.salary) AS totSal
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 6
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $healthSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();
    $jobHappiness = getAverageJobHap(1, $healthSelected,6);

    $imagePath = getRecImage($healthDetails->health_type_id_fk);

    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='images/health_icon.png'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$healthDetails->name}</div>
                                    <div class='closeEducButt' onclick='displayLoading(\"closeUtilProcess.php?utilType=healthcare&utilSelected={$healthSelected}\")'>Close Utility</div></a>
                                </div>
    
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 200px;'>Doctors: {$jobDetails->jobCounter}/{$healthDetails->workers}</td>
                                                    <td style='width: 200px;'>Job Quality: {$jobDetails->quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$jobHappiness}</td>
                                                </tr>
                                                <tr>
";

    echo "<td style = 'width: 200px;' >Quality: {$healthDetails->quality}</td >";


    echo "                                          <td style='width: 200px;'>Upkeep Cost: {$healthDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 200px;'>Yearly Wages: {$jobDetails->totSal} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                </tr>
                                        </table>
                                     </div>
                                    
                        </div>";
    $educRequired = getEducation($jobDetails->educ_required);


    echo"                <div class='individualBusLower'>
                            <form id='changeSal' action='changeSalaryProcess2.php' method='get' oninput='x.value=salChangeValue.value;'>
                                            <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif; margin-right: 10px;' >0<input  style='margin-top: 2px; width: 600px;' type='range' name='salChangeValue' value='{$jobDetails->salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                            <input type='submit' value='Change Salary' class='changeRentButt' id='changeRentButt' style='margin-top: 0.001px'>
                                            <br><br><span><div >New Salary: <output form='changeSal' name='x'></output></div></span>
                                            <br><span><div style='margin-top: -10px;'>Education Required: {$educRequired}</div></span>

                                       <input type='hidden' name='empID' value='{$healthSelected}'>
                                       <input type='hidden' name='industry' value='1'>
                                       <input type='hidden' name='utilType' value='6'>
                               </form>";

    if ($healthDetails->quality < $healthDetails->base_quality)
    {
        $qualityDifference = ($healthDetails->base_quality) - ($healthDetails->quality);
        $repairCost = ($qualityDifference/100)*($healthDetails->price);
        $repairCost = round($repairCost, 0);
        echo "                  <div style='float: right;width: 215px; margin-top: -35px'><p style='margin-top: -1px;'>Repair quality points lost to deterioration: +{$qualityDifference} Quality</p>
        <a href='#' class='repairButtLink' id='repairLink'><div class='repairButt' id='repairButt' cost={$repairCost}><img src='images/coins_icon.png' width='20px' height='20px' style='margin-left: 70px; margin-top: 5px;'><p style='margin-top: -27px; margin-left: 95px'>{$repairCost}</p></div></a></div>";

    }
     echo "                  </div>   
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

    var repairButt = document.getElementById('repairButt');

    repairButt.addEventListener("mouseover", repairValidation);
    repairButt.addEventListener("mouseout", repairMouseOff);

    function repairValidation()
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var houseSelected = document.getElementById('houseMeta').getAttribute('houseSelected');
        coins = parseInt(coins);
        var upgradeCost = document.getElementById('repairButt').getAttribute('cost');
        var link = document.getElementById('repairLink');
        if (upgradeCost <= coins)
        {
            var text = 'repairUtilProcess.php?utilType=healthcare&utilSelected=';
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