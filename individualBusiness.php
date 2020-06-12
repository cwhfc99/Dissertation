<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$busSelected = filter_has_var(INPUT_GET, 'busSelected')
    ? $_GET['busSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $busSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualBusiness.php?busSelected='.$busSelected);
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
    turnButtBlack('myHouses');

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
                     WHERE Job.employer_id_fk =:busID AND Job.citizen_employed = 1 AND Job.employer_role_type = 'a' AND Job.job_industry = 3
                     ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':busID' => $rowObj->id_pk));
        $countA = $stmt1->fetchObject();

        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS countB
                     FROM Job
                     WHERE Job.employer_id_fk =:busID AND Job.citizen_employed = 1 AND Job.employer_role_type = 'b' AND Job.job_industry = 3
                     ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':busID' => $rowObj->id_pk));
        $countB = $stmt1->fetchObject();

        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS countC
                     FROM Job
                     WHERE Job.employer_id_fk =:busID AND Job.citizen_employed = 1 AND Job.employer_role_type = 'c' AND Job.job_industry = 3
                     ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':busID' => $rowObj->id_pk));
        $countC = $stmt1->fetchObject();

        $yearlyWages = ($countA->countA * $rowObj->job_a_salary)+($countB->countB * $rowObj->job_b_salary)+($countC->countC * $rowObj->job_c_salary);
        $profitLoss = ($rowObj->yearly_revenue)-($rowObj->running_cost)-($yearlyWages);

        $totalProfit = $totalProfit + $profitLoss;
    }

    $avgProfit = $totalProfit/$businessCount;
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

    $dbConn = getConnection();
    $SQLselect = "SELECT Business.id_pk
                  FROM Business
                  WHERE Business.id_pk =:busID AND Business.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':busID' => $busSelected, ':cityID' => $_SESSION['cityID']));
    $busTest = $stmt->fetchObject();
    if (!$busTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT Business.id_pk
                      FROM Business
                      WHERE Business.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $busTemp = $stmt->fetchObject();
        $busSelected = $busTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT Business.id_pk, Business.name, Business.bus_type_id_fk, Business.name, Business.job_a_salary, Business.job_b_salary, Business.job_c_salary,
                         BusinessType.job_a_title, BusinessType.job_c_title, BusinessType.job_b_title,
                         BusinessType.job_a_education, BusinessType.job_b_education, BusinessType.job_c_education,
                         BusinessType.job_a_quantity, BusinessType.job_b_quantity, BusinessType.job_c_quantity,
                         Business.yearly_revenue, BusinessType.running_cost, BusinessType.job_quality
                  FROM Business
                  LEFT JOIN BusinessType ON Business.bus_type_id_fk = BusinessType.id_pk
                  WHERE Business.id_pk =:busID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':busID' => $busSelected));
    $busDetails = $stmt->fetchObject();

    $jobAEduc = getEducation($busDetails->job_a_education);
    $jobBEduc = getEducation($busDetails->job_b_education);
    $jobCEduc = getEducation($busDetails->job_c_education);


    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Job.id_pk) AS aWorkers
                  FROM Job
                  WHERE Job.employer_id_fk =:busID AND Job.employer_role_type = 'a' AND Job.citizen_employed = 1 AND Job.job_industry = 3
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':busID' => $busSelected));
    $aWorkers = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Job.id_pk) AS bWorkers
                  FROM Job
                  WHERE Job.employer_id_fk =:busID AND Job.employer_role_type = 'b' AND Job.citizen_employed = 1 AND Job.job_industry = 3
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':busID' => $busSelected));
    $bWorkers = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Job.id_pk) AS cWorkers
                  FROM Job
                  WHERE Job.employer_id_fk =:busID AND Job.employer_role_type = 'c' AND Job.citizen_employed = 1 AND Job.job_industry = 3
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':busID' => $busSelected));
    $cWorkers = $stmt->fetchObject();

    $yearlyWages = ($aWorkers->aWorkers * $busDetails->job_a_salary)+($bWorkers->bWorkers * $busDetails->job_b_salary)+($cWorkers->cWorkers * $busDetails->job_c_salary);
    $profitLoss = ($busDetails->yearly_revenue)-($busDetails->running_cost)-($yearlyWages);


    $dbConn = getConnection();
    $SQLquery = "SELECT AVG(Citizen.job_happiness) AS jobHappiness
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Job.employer_id_fk =:busID AND Job.job_industry = 3
                ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':busID' => $busSelected));
    $occupantsDetails = $stmt1->fetchObject();
    $occupantsHappiness = round($occupantsDetails->jobHappiness, 0);



    $imagePath = getBusImage($busDetails->bus_type_id_fk);

    echo "          <div class='myHousesMain'>
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='$imagePath'></div>
                                <div class='indHouseUpperUpper'>
                                    <form id='renameHouse' action='renameBusProcess.php' method='get'>
                                       <input type='text' name='renameValue' value=' {$busDetails->name}' id='renameValue'>
                                       <input type='submit' value='Rename' class='renameHouseButt' id='renameHouseButt'>
                                       <input type='hidden' name='busSelected' value='{$busSelected}'>
                                    </form>
                                    <div class='closeBusButt' onclick='displayLoading(\"closeBusProcess.php?busSelected={$busSelected}\")'>Close Business</div>
                                </div>
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 200px;'></td>
                                                    <td style='width: 200px;'></td>
                                                    <td style='width: 200px;'>Job Quality: {$busDetails->job_quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$occupantsHappiness}</td>
                                                </tr>
                                                <tr>
                                                    <td style='width: 200px;'>Last Year Revenue: {$busDetails->yearly_revenue} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 200px;'>Upkeep Cost: {$busDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 200px;'>Yearly Wages: {$yearlyWages} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 230px;'>Last Year Profit/Loss: {$profitLoss}<img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                </tr>
                                        </table>
                                     </div>
                                    
                        </div>";

    echo"                <div class='individualBusLower'>
                            <form id='changeRent' action='changeSalaryProcess.php' method='get' oninput='x.value=salAChangeValue.value; y.value=salBChangeValue.value; z.value=salCChangeValue.value'>
                                <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif; margin-right: 10px;' >0<input  style='margin-top: 2px; width: 150px;' type='range' name='salAChangeValue' value='{$busDetails->job_a_salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif; margin-right: 10px;' >0<input  style='margin-top: 2px; width: 150px;' type='range' name='salBChangeValue' value='{$busDetails->job_b_salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif; margin-right: 10px;' >0<input  style='margin-top: 2px; width: 150px;' type='range' name='salCChangeValue' value='{$busDetails->job_c_salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                <input type='submit' value='Change Salaries' class='changeRentButt' id='changeRentButt' style='margin-top: 0.001px'>
                                <br><span><div >New Salary: <output form='changeRent' name='x'></output></div><div style='margin-top: -18px; margin-left: 222px;'>New Salary: <output form='changeRent' name='y'></output></div><div style='margin-top: -18px; margin-left: 444px;'>New Salary: <output form='changeRent' name='z'></output></div></span>
                                <br><span><div style='margin-top: -10px;'>Job: {$busDetails->job_a_title}</div><div style='margin-top: -18px; margin-left: 222px;'>Job: {$busDetails->job_b_title}</div><div style='margin-top: -18px; margin-left: 444px;'>Job: {$busDetails->job_c_title}</div></span>
                                <br><span><div >Education Required: {$jobAEduc}</div><div style='margin-top: -18px; margin-left: 222px;'>Education Required: {$jobBEduc}</div><div style='margin-top: -18px; margin-left: 444px;'>Education Required: {$jobCEduc}</div></span>
                                <br><span><div >Employed: {$aWorkers->aWorkers}/{$busDetails->job_a_quantity}</div><div style='margin-top: -18px; margin-left: 222px;'>Employed: {$bWorkers->bWorkers}/{$busDetails->job_b_quantity}</div><div style='margin-top: -18px; margin-left: 444px;'>Employed: {$cWorkers->cWorkers}/{$busDetails->job_c_quantity}</div></span>
                                <input type='hidden' name='busSelected' value='{$busSelected}'>
                            </form>
                    
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

    input.addEventListener("input", validateInput);

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