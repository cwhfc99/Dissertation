<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$elecSelected = filter_has_var(INPUT_GET, 'elecSelected')
    ? $_GET['elecSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $elecSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualElect.php?elecSelected='.$elecSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT ElectricityBuilding.id_pk
                  FROM ElectricityBuilding
                  WHERE ElectricityBuilding.id_pk =:elecID AND ElectricityBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':elecID' => $elecSelected, ':cityID' => $_SESSION['cityID']));
    $elecTest = $stmt->fetchObject();
    if (!$elecTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT ElectricityBuilding.id_pk
                      FROM ElectricityBuilding
                      WHERE ElectricityBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $elecTemp = $stmt->fetchObject();
        $elecSelected = $elecTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT ElectricityBuilding.id_pk, ElectricityType.name, ElectricityBuilding.elect_type_id_fk, ElectricityType.running_cost,  ElectricityType.workers, ElectricityType.elect_output
                  FROM ElectricityBuilding
                  LEFT JOIN ElectricityType ON ElectricityBuilding.elect_type_id_fk = ElectricityType.id_pk
                  WHERE ElectricityBuilding.id_pk =:elecID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':elecID' => $elecSelected));
    $elecDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality, SUM(Job.salary) AS totSal
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 1
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $elecSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();

    $jobHappiness = getAverageJobHap(1, $elecSelected,1);


    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='images/electricity_icon.png'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$elecDetails->name}</div>
                                    <div class='closeEducButt' onclick='displayLoading(\"closeUtilProcess.php?utilType=electricity&utilSelected={$elecSelected}\")'>Close Utility</div>
                                </div>
    
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 200px;'>Workers: {$jobDetails->jobCounter}/{$elecDetails->workers}</td>
                                                    <td style='width: 200px;'>Job Quality: {$jobDetails->quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$jobHappiness}</td>
                                                </tr>
                                                <tr>
";

        echo "<td style = 'width: 200px;' >Electrical Output: {$elecDetails->elect_output}kW</td >";


    echo "                                          <td style='width: 200px;'>Upkeep Cost: {$elecDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
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
                                            <input type='hidden' name='empID' value='{$elecSelected}'>
                                            <input type='hidden' name='industry' value='1'>
                                            <input type='hidden' name='utilType' value='1'>
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