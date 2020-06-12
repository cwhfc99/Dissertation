<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$foodSelected = filter_has_var(INPUT_GET, 'foodSelected')
    ? $_GET['foodSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $foodSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualFood.php?foodSelected='.$foodSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT FoodBuilding.id_pk
                  FROM FoodBuilding
                  WHERE FoodBuilding.id_pk =:foodID AND FoodBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':foodID' => $foodSelected, ':cityID' => $_SESSION['cityID']));
    $foodTest = $stmt->fetchObject();
    if (!$foodTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT FoodBuilding.id_pk
                      FROM FoodBuilding
                      WHERE FoodBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $foodTemp = $stmt->fetchObject();
        $foodSelected = $foodTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT FoodBuilding.id_pk, FoodType.name, FoodBuilding.food_type_id_fk, FoodType.running_cost,  FoodType.workers,
                        FoodType.output_a_name, FoodType.output_b_name, FoodType.output_c_name,
                        FoodType.output_a_quantity, FoodType.output_b_quantity, FoodType.output_c_quantity
                  FROM FoodBuilding
                  LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                  WHERE FoodBuilding.id_pk =:elecID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':elecID' => $foodSelected));
    $foodDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality, SUM(Job.salary) AS totSal
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 3
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $foodSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();

    $imagePath = getRecImage($foodDetails->food_type_id_fk);
    $jobHappiness = getAverageJobHap(1, $foodSelected,3);


    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='images/food_icon.png'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$foodDetails->name}</div>
                                    <div class='closeEducButt' onclick='displayLoading(\"closeUtilProcess.php?utilType=food&utilSelected={$foodSelected}\")'>Close Utility</div>
                                </div>
    
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 300px;'>Workers: {$jobDetails->jobCounter}/{$foodDetails->workers}</td>
                                                    <td style='width: 200px;'>Job Quality: {$jobDetails->quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$jobHappiness}</td>
                                                </tr>
                                                <tr>
                                                            ";

    $outputAbool = false;
    $outputBbool = false;
    $outputCbool = false;

    if ($foodDetails->output_a_quantity > 0)
    {
        $outputA = "{$foodDetails->output_a_quantity}x {$foodDetails->output_a_name}";
        $outputAbool = true;
    }
    if ($foodDetails->output_b_quantity > 0)
    {
        $outputB = "{$foodDetails->output_b_quantity}x {$foodDetails->output_b_name}";
        $outputBbool = true;
    }
    if ($foodDetails->output_c_quantity > 0)
    {
        $outputC = "{$foodDetails->output_c_quantity}x {$foodDetails->output_c_name}";
        $outputCbool = true;
    }
    $foodHolder ='';
    if ($outputAbool == true)
    {
        $foodHolder = $foodHolder.$outputA;
    }
    if ($outputBbool == true)
    {
        $foodHolder = $foodHolder.", ".$outputB;
    }
    if ($outputCbool == true)
    {
        $foodHolder = $foodHolder.", ".$outputC;
    }


    echo "<td style = 'width: 200px;' >Food Provided: {$foodHolder}</td >";
    $educRequired = getEducation($jobDetails->educ_required);


    echo "                                          <td style='width: 200px;'>Upkeep Cost: {$foodDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 200px;'>Yearly Wages: {$jobDetails->totSal} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                </tr>
                                        </table>
                                     </div>
                                    
                        </div>";

    echo"                <div class='individualBusLower'>
                            <form id='changeSal' action='changeSalaryProcess2.php' method='get' oninput='x.value=salChangeValue.value;'>
                                            <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif; margin-right: 10px;' >0<input  style='margin-top: 2px; width: 600px;' type='range' name='salChangeValue' value='{$jobDetails->salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                            <input type='submit' value='Change Salary' class='changeRentButt' id='changeRentButt' style='margin-top: 0.001px'>
                                            <br><br><span><div >New Salary: <output form='changeSal' name='x'></output></div></span>
                                            <br><span><div style='margin-top: -10px;'>Education Required: {$educRequired}</div></span>

                                            <input type='hidden' name='empID' value='{$foodSelected}'>
                                            <input type='hidden' name='industry' value='1'>
                                            <input type='hidden' name='utilType' value='3'>
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