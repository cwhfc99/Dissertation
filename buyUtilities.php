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
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='levelMeta' level='<?php echo $_SESSION['level']?>'>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('MyUtilities');
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
    turnButtBlack('buyHouses');

    echo "          <div class='myHousesMain'>
                        <form id='classSelector' action={$_SESSION['redirect']}.php method='get'>
                            <select id='utilSelect' name='classSelected' onchange='reloadBuyUtilFunc()'>";
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
    $SQLquery = "SELECT ElectricityType.id_pk, ElectricityType.name, ElectricityType.price, ElectricityType.running_cost,
                        ElectricityType.workers, ElectricityType.level_unlocked, ElectricityType.xp_reward, ElectricityType.elect_output
                        ,ElectricityType.q_coin_reward
                 FROM ElectricityType
                 ORDER BY ElectricityType.id_pk ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getRecImage($rowObj->id_pk);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price, \"electricity\") 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/electricity_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 200px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'>Cost: {$rowObj->price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px; text-align: right'>Workers: {$rowObj->workers} </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 200px;'>Output: {$rowObj->elect_output}kW</td>
                                            <td style='width: 150px;'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td style='width: 150px;'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td style='width: 150px;text-align: right'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
            echo "<td style='width: 150px;'></td>";
        }


        echo "                           
                                        </tr>
                                    </table>
                                </div></a>
                             ";


    }
    echo "            
                    </div>
                   </div>                
               </div>";
}

function selectWater()
{

    $dbConn = getConnection();
    $SQLquery = "SELECT WaterType.id_pk, WaterType.name, WaterType.price, WaterType.running_cost,
                        WaterType.workers, WaterType.level_unlocked, WaterType.xp_reward, WaterType.water_output
                        ,WaterType.q_coin_reward
                 FROM WaterType
                 ORDER BY WaterType.id_pk ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getRecImage($rowObj->id_pk);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price, \"water\") 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/water_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 200px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'>Cost: {$rowObj->price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px; text-align: right'>Workers: {$rowObj->workers} </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 200px;'>Output: {$rowObj->water_output}L</td>
                                            <td style='width: 150px;'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td style='width: 150px;'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td style='width: 150px;text-align: right'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
            echo "<td style='width: 150px;'></td>";
        }


        echo "                           
                                        </tr>
                                    </table>
                                </div></a>
                             ";


    }
    echo "            
                    </div>
                   </div>                
               </div>";
}

function selectFood()
{

    $dbConn = getConnection();
    $SQLquery = "SELECT FoodType.id_pk, FoodType.name, FoodType.price, FoodType.running_cost,
                        FoodType.workers, FoodType.level_unlocked, FoodType.xp_reward, FoodType.q_coin_reward,
                        FoodType.output_a_name, FoodType.output_b_name, FoodType.output_c_name,
                        FoodType.output_a_quantity, FoodType.output_b_quantity, FoodType.output_c_quantity
                 FROM FoodType
                 ORDER BY FoodType.id_pk ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));




    while($rowObj = $stmt->fetchObject())
    {
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

        $imagePath = getRecImage($rowObj->id_pk);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price, \"food\") 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/food_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 250px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'>Cost: {$rowObj->price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px; text-align: right'>Workers: {$rowObj->workers} </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 250px;'>Food Provided: {$foodHolder}</td>
                                            <td style='width: 150px;'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td style='width: 150px;'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td style='width: 150px; text-align: right'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
            echo "<td style='width: 150px;'></td>";
        }


        echo "                           
                                        </tr>
                                    </table>
                                </div></a>
                             ";


    }
    echo "            
                    </div>
                   </div>                
               </div>";
}

function selectFire()
{

    $dbConn = getConnection();
    $SQLquery = "SELECT FireType.id_pk, FireType.name, FireType.price, FireType.running_cost,
                        FireType.workers, FireType.level_unlocked, FireType.xp_reward, FireType.base_quality
                        ,FireType.q_coin_reward
                 FROM FireType
                 ORDER BY FireType.id_pk ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getRecImage($rowObj->id_pk);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price, \"fire\") 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/fire_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 200px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'>Cost: {$rowObj->price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px; text-align: right'>Firefighters: {$rowObj->workers} </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 200px;'>Quality: {$rowObj->base_quality}</td>
                                            <td style='width: 150px;'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td style='width: 150px;'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td style='width: 150px;text-align: right'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
            echo "<td style='width: 150px;'></td>";
        }


        echo "                           
                                        </tr>
                                    </table>
                                </div></a>
                             ";


    }
    echo "            
                    </div>
                   </div>                
               </div>";
}

function selectPolice()
{

    $dbConn = getConnection();
    $SQLquery = "SELECT PoliceType.id_pk, PoliceType.name, PoliceType.price, PoliceType.running_cost,
                        PoliceType.workers, PoliceType.level_unlocked, PoliceType.xp_reward, PoliceType.base_quality
                        ,PoliceType.q_coin_reward
                 FROM PoliceType
                 ORDER BY PoliceType.id_pk ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getRecImage($rowObj->id_pk);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price, \"police\") 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/police_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 200px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'>Cost: {$rowObj->price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px; text-align: right'>Police Officer: {$rowObj->workers} </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 200px;'>Quality: {$rowObj->base_quality}</td>
                                            <td style='width: 150px;'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td style='width: 150px;'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td style='width: 150px;text-align: right'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
            echo "<td style='width: 150px;'></td>";
        }


        echo "                           
                                        </tr>
                                    </table>
                                </div></a>
                             ";


    }
    echo "            
                    </div>
                   </div>                
               </div>";
}

function selectHealthcare()
{

    $dbConn = getConnection();
    $SQLquery = "SELECT HealthType.id_pk, HealthType.name, HealthType.price, HealthType.running_cost,
                        HealthType.workers, HealthType.level_unlocked, HealthType.xp_reward, HealthType.base_quality
                        ,HealthType.q_coin_reward, HealthType.capacity
                 FROM HealthType
                 ORDER BY HealthType.id_pk ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getRecImage($rowObj->id_pk);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price, \"healthcare\") 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='images/health_icon.png'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 150px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 150px;'>Cost: {$rowObj->price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td style='width: 150px; text-align: right'>Doctors: {$rowObj->workers} </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 150px;'>Quality: {$rowObj->base_quality}</td>
                                            <td style='width: 150px;'>Capacity: {$rowObj->capacity}</td>
                                            <td style='width: 150px;'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td style='width: 150px;'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td style='width: 150px;text-align: right'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
            echo "<td style='width: 150px;'></td>";
        }


        echo "                           
                                        </tr>
                                    </table>
                                </div></a>
                             ";


    }
    echo "            
                    </div>
                   </div>                
               </div>";
}

?>

<script type="text/javascript">
    window.addEventListener('load', function() {
        'use strict';

    });


    function reloadBuyUtilFunc()
    {
        var utilSelected = document.getElementById('utilSelect').value;
        const page = 'buyUtilities.php?utilSelected=';
        var holder = page.concat(utilSelected);
        document.location = holder;
    }

    function validateBus(accomType, accomLevel, accomPrice, utilType)
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var level = document.getElementById('levelMeta').getAttribute('level');
        var container = document.getElementById(accomType);
        const temp1 = 'link';
        const temp = temp1.concat(accomType);
        var link = document.getElementById(temp);

        coins = parseInt(coins);
        level = parseInt(level);

        var canBuy = false;

        if (coins >= accomPrice)
        {
            if (level >= accomLevel)
            {
                canBuy = true;
            }
        }

        if (canBuy == false)
        {
            link.href='#';
            container.style.backgroundColor = '#db4b12';
        }
        else
        {
            var text = 'buyUtilProcess.php?utilToBuy=';
            var text2 = '&utilType='
            var textVar = text.concat(accomType,text2, utilType);
            link.href = textVar;
            container.style.backgroundColor = '#55e67b';
        }
    }

    function mouseOff(accomType)
    {
        var container = document.getElementById(accomType);
        container.style.backgroundColor = '#ffffff';

    }

</script>


</body>

</html>