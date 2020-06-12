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
checkLogin('myUtilities');
if ($_SESSION['loggedIn'] == 'true')
{
    $utilSelected = filter_has_var(INPUT_GET, 'utilSelected')
        ? $_GET['utilSelected'] : null;

    $utilsArray = array('electricity', 'water', 'food', 'fire', 'police', 'healthcare');

    if (!(in_array($utilSelected, $utilsArray)))
    {
        $utilSelected = 'electricity';
    }


    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>
                        <form id='classSelector' action={$_SESSION['redirect']}.php method='get'>
                            <select id='utilSelect' name='classSelected' onchange='reloadUtilFunc()'>";
    foreach($utilsArray as $i)
    {
        $upperWord = ucfirst($i);
        if ($i == $utilSelected)
        {
            echo "<option selected value='$i'>{$upperWord}</option>";
        }
        else
        {
            echo "<option value='$i'>{$upperWord}</option>";
        }
    }
   echo "                   </select>
                        </form>
                        <div class='utilsHolder'>";

    if ($utilSelected == 'electricity')
    {
        selectElect();
    }
    else if ($utilSelected == 'water')
    {
        selectWater();
    }
    else if ($utilSelected == 'food')
    {
        selectFood();
    }
    else if ($utilSelected == 'fire')
    {
        selectFire();
    }
    else if ($utilSelected == 'police')
    {
        selectPolice();
    }
    else if ($utilSelected == 'healthcare')
    {
        selectHealthcare();
    }




    echo "            
                    </div>
                   </div>                
               </div>";



}
else
{
    notLoggedIn();
}

function selectElect()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT ElectricityBuilding.id_pk, ElectricityType.id_pk AS recID, ElectricityType.name, ElectricityType.running_cost, ElectricityType.elect_output
                        , ElectricityType.workers
                 FROM ElectricityBuilding
                 LEFT JOIN ElectricityType ON ElectricityBuilding.elect_type_id_fk = ElectricityType.id_pk
                 WHERE ElectricityBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    while($rowObj = $stmt->fetchObject())
    {

        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 1
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        echo "
                                <a href='individualElect.php?elecSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/electricity_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 200px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 175px;'></td>
                                            <td style='width: 10px;'></td>
                                        </tr>
                                        <tr>
                                            <td style='width: 200px;'>Electrical Output: {$rowObj->elect_output}kW</td>
                                            <td style='width: 150px;'>Workers: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td style='width: 175px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 10px;'></td>   
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }
}

function selectWater()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT WaterBuilding.id_pk, WaterType.id_pk AS recID, WaterType.name, WaterType.running_cost, WaterType.water_output
                        , WaterType.workers
                 FROM WaterBuilding
                 LEFT JOIN WaterType ON WaterBuilding.water_type_id_fk = WaterType.id_pk
                 WHERE WaterBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 2
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        $imagePath = getRecImage($rowObj->recID);

        echo "
                                <a href='individualWater.php?waterSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/water_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 200px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 175px;'></td>
                                            <td style='width: 10px;'></td>
                                        </tr>
                                        <tr>
                                            <td style='width: 200px;'>Water Output: {$rowObj->water_output}L</td>
                                            <td style='width: 150px;'>Workers: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td style='width: 175px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 10px;'></td>   
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }
}

function selectFood()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT FoodBuilding.id_pk, FoodType.id_pk AS recID, FoodType.name, FoodType.running_cost
                        , FoodType.workers, FoodType.output_a_name, FoodType.output_b_name, FoodType.output_c_name,
                        FoodType.output_a_quantity, FoodType.output_b_quantity, FoodType.output_c_quantity
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 3
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        $imagePath = getRecImage($rowObj->recID);

        $outputAbool = false;
        $outputBbool = false;
        $outputCbool = false;

        if ($rowObj->output_a_quantity > 0)
        {
            $outputA = "{$rowObj->output_a_quantity}x {$rowObj->output_a_name}";
            $outputAbool = true;
        }
        if ($rowObj->output_b_quantity > 0)
        {
            $outputB = "{$rowObj->output_b_quantity}x {$rowObj->output_b_name}";
            $outputBbool = true;
        }
        if ($rowObj->output_c_quantity > 0)
        {
            $outputC = "{$rowObj->output_c_quantity}x {$rowObj->output_c_name}";
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

        echo "
                                <a href='individualFood.php?foodSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/food_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 200px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 175px;'></td>
                                            <td style='width: 10px;'></td>
                                        </tr>
                                        <tr>
                                            <td style='width: 200px;'>Food Provided: {$foodHolder}</td>
                                            <td style='width: 150px;'>Workers: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td style='width: 175px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 10px;'></td>   
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }
}

function selectFire()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT FireBuilding.id_pk, FireType.id_pk AS recID, FireType.name, FireType.running_cost, FireType.workers,
                        FireBuilding.quality, FireBuilding.upgrade_a_bought, FireBuilding.upgrade_b_bought, FireBuilding.upgrade_c_bought
                 FROM FireBuilding
                 LEFT JOIN FireType ON FireBuilding.fire_type_id_fk = FireType.id_pk
                 WHERE FireBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 4
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        $imagePath = getRecImage($rowObj->recID);

        $upgradesBought = 0;
        if ($rowObj->upgrade_a_bought == 1)
        {
            $upgradesBought++;
        }
        if ($rowObj->upgrade_b_bought == 1)
        {
            $upgradesBought++;
        }
        if ($rowObj->upgrade_c_bought == 1)
        {
            $upgradesBought++;
        }

        echo "
                                <a href='individualFire.php?fireSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/fire_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 150px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 175px;'></td>
                                        </tr>
                                        <tr>
                                            <td style='width: 150px;'>Quality: {$rowObj->quality}</td>
                                            <td  style='width: 150px;'>Upgrades Bought: {$upgradesBought}/3</td>
                                            <td style='width: 150px;'>Firefighters: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td style='width: 175px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }
}

function selectPolice()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT PoliceBuilding.id_pk, PoliceType.id_pk AS recID, PoliceType.name, PoliceType.running_cost, PoliceType.workers,
                        PoliceBuilding.quality, PoliceBuilding.upgrade_a_bought, PoliceBuilding.upgrade_b_bought, PoliceBuilding.upgrade_c_bought
                 FROM PoliceBuilding
                 LEFT JOIN PoliceType ON PoliceBuilding.police_type_id_fk = PoliceType.id_pk
                 WHERE PoliceBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 5
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        $imagePath = getRecImage($rowObj->recID);

        $upgradesBought = 0;
        if ($rowObj->upgrade_a_bought == 1)
        {
            $upgradesBought++;
        }
        if ($rowObj->upgrade_b_bought == 1)
        {
            $upgradesBought++;
        }
        if ($rowObj->upgrade_c_bought == 1)
        {
            $upgradesBought++;
        }

        echo "
                                <a href='individualPolice.php?policeSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/police_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 150px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 175px;'></td>
                                        </tr>
                                        <tr>
                                            <td style='width: 150px;'>Quality: {$rowObj->quality}</td>
                                            <td  style='width: 150px;'>Upgrades Bought: {$upgradesBought}/3</td>
                                            <td style='width: 150px;'>Police Officers: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td style='width: 175px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }
}

function selectHealthcare()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT HealthBuilding.id_pk, HealthType.id_pk AS recID, HealthType.name, HealthType.running_cost, HealthType.workers,
                        HealthBuilding.quality, HealthType.capacity
                 FROM HealthBuilding
                 LEFT JOIN HealthType ON HealthBuilding.health_type_id_fk = HealthType.id_pk
                 WHERE HealthBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 1 AND Job.citizen_employed = 1 AND Job.util_type = 6
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        $imagePath = getRecImage($rowObj->recID);

        echo "
                                <a href='individualHealth.php?healthSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/health_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 150px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 175px;'></td>
                                        </tr>
                                        <tr>
                                            <td style='width: 150px;'>Quality: {$rowObj->quality}</td>
                                            <td  style='width: 150px;'>Capacity: {$rowObj->capacity}</td>
                                            <td style='width: 150px;'>Doctors: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td style='width: 175px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }
}

?>

<script type="text/javascript">
        window.addEventListener('load', function() {
            'use strict';

        });


    function reloadUtilFunc()
    {
        var utilSelected = document.getElementById('utilSelect').value;
        const page = 'myUtilities.php?utilSelected=';
        var holder = page.concat(utilSelected);
        document.location = holder;
    }

</script>


</body>

</html>