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
checkLogin('businessesInfo');
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
    turnButtBlack('housesInfo');

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
    }    $avgProfit = round($avgProfit, 0);



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
    echo "<p>Businesses can make your city serious money if managed well, whilst also providing your citizens with a job and, in turn, a salary </p>
          <p>Each business has an investment cost that must be paid up front to start the business. This various depending on the type of the business. They also have an upkeep cost that must be paid every year in order to keep the business running.</p>
          <p>The yearly revenue of each business is dependant on the percentage of workers employed in that business.</p>
          <p>Each business has three roles within the company for your citizens to work. Each will require a different education level from the citizen.</p>
          <p>You can set the salary of each role for each business that you own. Bear in mind that citizens with a better education will expect a better salary:</p>
          <ul>
              <li>None: 500 Coins</li>
              <li>School: 1000 Coins</li>
              <li>College: 2000 Coins</li>
              <li>University: 4000 Coins</li>
          </ul>
          <p>The salary you set for each role will also set the weath status of the citizen within that role:</p>
          <ul>
              <li>Broke: 0 - 750 Coins</li>
              <li>Poor: 751 - 1500 Coins</li>
              <li>Medium: 1501 - 3000 Coins</li>
              <li>Wealthy: 3001 - 5000 Coins</li>
              <li>V. Wealthy: 5001+ Coins</li>
          </ul>
          <p>Whilst making money is that most important thing when managing businesses, your citizens will want to be happy in there job. Job Happiness is determined as a combination of job quality, salary and the unemployment rate in the city.</p>
          <p>Citizens are automatically assigned jobs every year. Citizens are assigned jobs so that there educational status is best optimised for your businesses. The jobs with the best job quality are filled first. Citizens must be over 16, not retired and not in education to work. Please note, jobs in the public sector (education, recreation and utilities) are filled before jobs in the private sector.</p>
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