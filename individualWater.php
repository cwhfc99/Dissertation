<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$waterSelected = filter_has_var(INPUT_GET, 'waterSelected')
    ? $_GET['waterSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $waterSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualWater.php?waterSelected='.$waterSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT WaterBuilding.id_pk
                  FROM WaterBuilding
                  WHERE WaterBuilding.id_pk =:waterID AND WaterBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':waterID' => $waterSelected, ':cityID' => $_SESSION['cityID']));
    $waterTest = $stmt->fetchObject();
    if (!$waterTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT WaterBuilding.id_pk
                      FROM WaterBuilding
                      WHERE WaterBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $waterTemp = $stmt->fetchObject();
        $waterSelected = $waterTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT WaterBuilding.id_pk, WaterType.name, WaterBuilding.water_type_id_fk, WaterType.running_cost,  WaterType.workers, WaterType.water_output
                  FROM WaterBuilding
                  LEFT JOIN WaterType ON WaterBuilding.water_type_id_fk = WaterType.id_pk
                  WHERE WaterBuilding.id_pk =:elecID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':elecID' => $waterSelected));
    $waterDetails = $stmt->fetchObject();

    $imagePath = getRecImage($waterDetails->water_type_id_fk);

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality, SUM(Job.salary) AS totSal
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 2
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $waterSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();
    $jobHappiness = getAverageJobHap(1, $waterSelected,2);


    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='images/water_icon.png'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$waterDetails->name}</div>
                                    <div class='closeEducButt' onclick='displayLoading(\"closeUtilProcess.php?utilType=water&utilSelected={$waterSelected}\")'>Close Utility</div>
                                </div>
    
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 200px;'>Workers: {$jobDetails->jobCounter}/{$waterDetails->workers}</td>
                                                    <td style='width: 200px;'>Job Quality: {$jobDetails->quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$jobHappiness}</td>
                                                </tr>
                                                <tr>
                                                            ";

    echo "<td style = 'width: 200px;' >Electrical Output: {$waterDetails->water_output}L</td >";


    echo "                                          <td style='width: 200px;'>Upkeep Cost: {$waterDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
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

                                            <input type='hidden' name='empID' value='{$waterSelected}'>
                                            <input type='hidden' name='industry' value='1'>
                                            <input type='hidden' name='utilType' value='2'>
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


</body>

</html>