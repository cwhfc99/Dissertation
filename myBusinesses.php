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
checkLogin('myBusinesses');
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
    $SQLquery = "SELECT Business.id_pk, Business.bus_type_id_fk, Business.name, Business.yearly_revenue, BusinessType.job_a_quantity, BusinessType.job_b_quantity, 
                        BusinessType.job_c_quantity, BusinessType.job_quality, BusinessType.running_cost, Business.job_a_salary, Business.job_b_salary, Business.job_c_salary
                 FROM Business
                 LEFT JOIN BusinessType ON Business.bus_type_id_fk = BusinessType.id_pk
                 WHERE Business.city_id_fk =:cityID
                 ORDER BY Business.yearly_revenue DESC
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS workersCount
                     FROM Job
                     WHERE Job.employer_id_fk =:busID AND Job.citizen_employed = 1 AND Job.job_industry = 3
                     ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':busID' => $rowObj->id_pk));
        $businessDetails = $stmt1->fetchObject();

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

        $imagePath = getBusImage($rowObj->bus_type_id_fk);
        $type = getBusType($rowObj->bus_type_id_fk);
        $totalWorkers = ($rowObj->job_a_quantity)+($rowObj->job_b_quantity)+($rowObj->job_c_quantity);
        $yearlyWages = ($countA->countA * $rowObj->job_a_salary)+($countB->countB * $rowObj->job_b_salary)+($countC->countC * $rowObj->job_c_salary);

        $profitLoss = ($rowObj->yearly_revenue)-($rowObj->running_cost)-($yearlyWages);

        echo "
                                <a href='individualBusiness.php?busSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='$imagePath'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td class='houseDetailsName'>{$rowObj->name}</td>
                                            <td class='houseDetailsType'>Type: {$type}</td>
                                            <td class='houseDetailsUpCost'>Workers: {$businessDetails->workersCount}/{$totalWorkers}</td>
                                            <td class='houseDetailsUpgrades'>Job Quality: {$rowObj->job_quality}</td>
                                        </tr>
                                        <tr>
                                            <td class='houseDetailsOccupancy'>Last Year Revenue: {$rowObj->yearly_revenue} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td class='houseDetailsType'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td class='houseDetailsUpCost'>Yearly Wages: {$yearlyWages} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td class='houseDetailsUpgrades'>Last Year Profit/Loss: {$profitLoss} <img src='images/coins_icon.png' width='15px' height='15px'></td>
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