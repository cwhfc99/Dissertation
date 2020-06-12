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
checkLogin('citizensOverview');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    citizensHolder();
    turnButtBlack('overview');

    $dbConn = getConnection();
    $querySQL = "SELECT AVG(Citizen.overall_happiness) as overall_happiness, AVG(Citizen.housing_happiness) as housing_happiness, AVG(Citizen.education_happiness) as education_happiness, 
                        AVG(Citizen.job_happiness) as job_happiness, AVG(Citizen.recreation_happiness) as recreation_happiness, 
                        AVG(Citizen.health_happiness) as health_happiness, AVG(Citizen.safety_happiness) as safety_happiness, AVG(Citizen.qol_happiness) as qol_happiness
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID 
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $happ = $stmt->fetchObject();
    $overall = round($happ->overall_happiness,0);
    $housing = round($happ->housing_happiness,0);
    $education = round($happ->education_happiness,0);
    $job = round($happ->job_happiness,0);
    $recreation = round($happ->recreation_happiness,0);
    $health = round($happ->health_happiness,0);
    $qol = round($happ->qol_happiness,0);
    $safety = round($happ->safety_happiness,0);

    echo "<div class='myHousesMain'>
            <h1 style='font-family: \"Arial Black\", Arial, sans-serif; margin-top: -0'>Overview</h1>
            <table class='citizenOverviewTable'>
                <tr>
                    <th style='text-align: left'>Overall Happiness: {$overall}</th>
                    <td>Housing Happiness: {$housing}</td>
                    <td>Job Happiness: {$job}</td>
                    <td>Education Happiness: {$education}</td>
                </tr>
                <tr>
                    <td>Recreation Happiness: {$recreation}</td>
                    <td>Quality of Life Happiness: {$qol}</td>
                    <td>Health Happiness: {$health}</td>";
    if($_SESSION['level'] > 15)
    {
     echo    "<td>Safety Happiness: {$safety}</td>";
    }
    else
    {
        echo "<td></td>";
    }

    $dbConn = getConnection();
    $querySQL = "SELECT City.tax_rate, City.retirement_age
                 FROM City
                 WHERE City.id_pk =:cityID 
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $cityDetails = $stmt->fetchObject();

    echo  "       </tr>
            </table>
            <br>
            <hr>
            <div class='citizenOverviewBottomLeft'>
                <form id='changeTaxRate' onsubmit='changeTaxRate()' action='changeTaxRate.php' method='get' oninput='y.value=taxChangeValue.value'>
                    <span><div style='margin-left: 150px; font-family: \"Arial Black\", Arial, sans-serif'>Tax Rate: <output form='changeTaxRate' name='y'>{$cityDetails->tax_rate}</output>%</div></span>
                    <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif;'>0%<input  style='margin-top: 15px; width: 370px;' type='range' name='TaxChangeValue' value='{$cityDetails->tax_rate}' id='taxChangeValue' min='0' max='45'>45%</span>
                    <input type='submit' value='Change Tax Rate' class='changeTaxButt' id='changeTaxButt'>
                </form>
            </div>
            <div class='citizenOverviewBottomRight'>
                <form id='changeRetAge' onsubmit='changeTaxRate()' action='changeRetAge.php' method='get' oninput='x.value=retireChangeValue.value'>
                    <span><div style='margin-left: 150px; font-family: \"Arial Black\", Arial, sans-serif'>Retirement Age: <output form='changeRetAge' name='x'>{$cityDetails->retirement_age}</output></div></span>
                    <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif;'>51<input  style='margin-top: 15px; width: 370px;' type='range' name='RetAgeChangeValue' value='{$cityDetails->retirement_age}' id='retireChangeValue' min='51' max='81'>81</span>
                    <input type='submit' value='Change Retirement Age' class='changeTaxButt' id='changeTaxButt'>
                </form>
            </div>
          </div>";

    echo "</div>
         </div>";

}
else
{
    notLoggedIn();
}

?>

<script type="text/javascript">

    function changeTaxRate()
    {
        displayLoading("#");
    }

</script>

</body>

</html>