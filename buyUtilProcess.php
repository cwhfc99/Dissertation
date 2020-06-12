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
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('buyUtilProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $utilToBuy = filter_has_var(INPUT_GET, 'utilToBuy')
        ? $_GET['utilToBuy'] : null;

    $utilType = filter_has_var(INPUT_GET, 'utilType')
        ? $_GET['utilType'] : null;

    $utilTypes = array('electricity', 'water', 'food', 'fire', 'police', 'healthcare');

    if (in_array($utilType, $utilTypes))
    {
        if ($utilType == 'electricity')
        {
            buyElect($utilToBuy);
        }
        else if ($utilType == 'water')
        {
            buyWater($utilToBuy);
        }
        else if ($utilType == 'food')
        {
            buyFood($utilToBuy);
        }
        else if ($utilType == 'fire')
        {
            buyFire($utilToBuy);
        }
        else if ($utilType == 'police')
        {
            buyPolice($utilToBuy);
        }
        else if ($utilType == 'healthcare')
        {
            buyHealthcare($utilToBuy);
        }

        utilHappiness();

    }
    else
    {
        echo "A problem occurred and the utility was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}
else
{
    notLoggedIn();
}

function buyElect($utilToBuy)
{

    $utilTypes = array(1,2,3,4,5,6,7);

    if (in_array($utilToBuy, $utilTypes))
    {

        //Get utility info
        $dbConn = getConnection();
        $SQLselect = "SELECT ElectricityType.price, ElectricityType.xp_reward, ElectricityType.q_coin_reward, ElectricityType.workers, ElectricityType.worker_education,
                            ElectricityType.job_quality
                      FROM ElectricityType
                      WHERE ElectricityType.id_pk =:utilID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilToBuy));
        $utilInfo = $stmt->fetchObject();

        //Check user can afford business

        if (($utilInfo->price)<=($_SESSION['coins']))
        {
            //Add utility record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO ElectricityBuilding(city_id_fk, elect_type_id_fk)
                          VALUES (:cityID, :utilID)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilID' => $utilToBuy));

            //Reward user
            rewardXP($utilInfo->xp_reward);

            if ($utilInfo->q_coin_reward > 0)
            {
                rewardQcoin($utilInfo->q_coin_reward);
            }

            //Charge user

            chargeUser($utilInfo->price);


            //Add Jobs
            $dbConn = getConnection();
            $SQLselect = "SELECT ElectricityBuilding.id_pk
                          FROM ElectricityBuilding
                          WHERE ElectricityBuilding.city_id_fk =:cityID AND elect_type_id_fk =:utilType
                          ORDER BY ElectricityBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilType' => $utilToBuy));
            $recID = $stmt->fetchObject();

            for ($i = 0; $i < $utilInfo->workers; $i++)
            {
                newJob($recID->id_pk, 'a', 1, $utilInfo->worker_education, $utilInfo->job_quality, getSalary($utilInfo->worker_education),1);
            }

            jobAssingment();
            header('Location: buyUtilities.php?utilSelected=electricity');


        }
        else
        {
            echo "Insufficient funds to buy business."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the utility was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}

function buyWater($utilToBuy)
{

    $utilTypes = array(1,2,3,4,5);

    if (in_array($utilToBuy, $utilTypes))
    {

        //Get utility info
        $dbConn = getConnection();
        $SQLselect = "SELECT WaterType.price, WaterType.xp_reward, WaterType.q_coin_reward, WaterType.workers, WaterType.worker_education,
                            WaterType.job_quality     
                      FROM WaterType
                      WHERE WaterType.id_pk =:utilID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilToBuy));
        $utilInfo = $stmt->fetchObject();

        //Check user can afford business

        if (($utilInfo->price)<=($_SESSION['coins']))
        {
            //Add utility record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO WaterBuilding(city_id_fk, water_type_id_fk)
                          VALUES (:cityID, :utilID)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilID' => $utilToBuy));

            //Reward user
            rewardXP($utilInfo->xp_reward);

            if ($utilInfo->q_coin_reward > 0)
            {
                rewardQcoin($utilInfo->q_coin_reward);
            }

            //Charge user

            chargeUser($utilInfo->price);


            //Add Jobs
            $dbConn = getConnection();
            $SQLselect = "SELECT WaterBuilding.id_pk
                          FROM WaterBuilding
                          WHERE WaterBuilding.city_id_fk =:cityID AND water_type_id_fk =:utilType
                          ORDER BY WaterBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilType' => $utilToBuy));
            $utilID = $stmt->fetchObject();

            for ($i = 0; $i < $utilInfo->workers; $i++)
            {
                newJob($utilID->id_pk, 'a', 1, $utilInfo->worker_education, $utilInfo->job_quality, getSalary($utilInfo->worker_education),2);
            }

            jobAssingment();
            header('Location: buyUtilities.php?utilSelected=water');


        }
        else
        {
            echo "Insufficient funds to buy business."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the utility was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}

function buyFood($utilToBuy)
{

    $utilTypes = array(1,2,3,4,5,6,7);

    if (in_array($utilToBuy, $utilTypes))
    {

        //Get utility info
        $dbConn = getConnection();
        $SQLselect = "SELECT FoodType.price, FoodType.xp_reward, FoodType.q_coin_reward, FoodType.workers, FoodType.worker_education,
                            FoodType.job_quality     
                      FROM FoodType
                      WHERE FoodType.id_pk =:utilID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilToBuy));
        $utilInfo = $stmt->fetchObject();

        //Check user can afford business

        if (($utilInfo->price)<=($_SESSION['coins']))
        {
            //Add utility record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO FoodBuilding(city_id_fk, food_type_id_fk)
                          VALUES (:cityID, :utilID)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilID' => $utilToBuy));

            //Reward user
            rewardXP($utilInfo->xp_reward);

            if ($utilInfo->q_coin_reward > 0)
            {
                rewardQcoin($utilInfo->q_coin_reward);
            }

            //Charge user

            chargeUser($utilInfo->price);


            //Add Jobs
            $dbConn = getConnection();
            $SQLselect = "SELECT FoodBuilding.id_pk
                          FROM FoodBuilding
                          WHERE FoodBuilding.city_id_fk =:cityID AND food_type_id_fk =:utilType
                          ORDER BY FoodBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilType' => $utilToBuy));
            $utilID = $stmt->fetchObject();

            for ($i = 0; $i < $utilInfo->workers; $i++)
            {
                newJob($utilID->id_pk, 'a', 1, $utilInfo->worker_education, $utilInfo->job_quality, getSalary($utilInfo->worker_education),3);
            }

            jobAssingment();
            header('Location: buyUtilities.php?utilSelected=food');


        }
        else
        {
            echo "Insufficient funds to buy business."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the utility was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}

function buyFire($utilToBuy)
{

    $utilTypes = array(1,2,3,4);

    if (in_array($utilToBuy, $utilTypes))
    {

        //Get utility info
        $dbConn = getConnection();
        $SQLselect = "SELECT FireType.price, FireType.xp_reward, FireType.q_coin_reward, FireType.base_quality, FireType.workers, FireType.worker_education,
                            FireType.job_quality     
                      FROM FireType
                      WHERE FireType.id_pk =:utilID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilToBuy));
        $utilInfo = $stmt->fetchObject();

        //Check user can afford business

        if (($utilInfo->price)<=($_SESSION['coins']))
        {
            //Add utility record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO FireBuilding(city_id_fk, fire_type_id_fk, quality, upgrade_a_bought, upgrade_b_bought, upgrade_c_bought)
                          VALUES (:cityID, :utilID, :quality, 0, 0, 0)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilID' => $utilToBuy, ':quality' => $utilInfo->base_quality));

            //Reward user
            rewardXP($utilInfo->xp_reward);

            if ($utilInfo->q_coin_reward > 0)
            {
                rewardQcoin($utilInfo->q_coin_reward);
            }

            //Charge user

            chargeUser($utilInfo->price);


            //Add Jobs
            $dbConn = getConnection();
            $SQLselect = "SELECT FireBuilding.id_pk
                          FROM FireBuilding
                          WHERE FireBuilding.city_id_fk =:cityID AND fire_type_id_fk =:utilType
                          ORDER BY FireBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilType' => $utilToBuy));
            $utilID = $stmt->fetchObject();

            for ($i = 0; $i < $utilInfo->workers; $i++)
            {
                newJob($utilID->id_pk, 'a', 1, $utilInfo->worker_education, $utilInfo->job_quality, getSalary($utilInfo->worker_education),4);
            }

            jobAssingment();
            header('Location: buyUtilities.php?utilSelected=fire');


        }
        else
        {
            echo "Insufficient funds to buy business."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the utility was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}

function buyPolice($utilToBuy)
{

    $utilTypes = array(1,2,3,4,5);

    if (in_array($utilToBuy, $utilTypes))
    {

        //Get utility info
        $dbConn = getConnection();
        $SQLselect = "SELECT PoliceType.price, PoliceType.xp_reward, PoliceType.q_coin_reward, PoliceType.base_quality, PoliceType.workers, PoliceType.worker_education,
                            PoliceType.job_quality     
                      FROM PoliceType
                      WHERE PoliceType.id_pk =:utilID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilToBuy));
        $utilInfo = $stmt->fetchObject();

        //Check user can afford business

        if (($utilInfo->price)<=($_SESSION['coins']))
        {
            //Add utility record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO PoliceBuilding(city_id_fk, police_type_id_fk, quality, upgrade_a_bought, upgrade_b_bought, upgrade_c_bought)
                          VALUES (:cityID, :utilID, :quality, 0, 0, 0)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilID' => $utilToBuy, ':quality' => $utilInfo->base_quality));

            //Reward user
            rewardXP($utilInfo->xp_reward);

            if ($utilInfo->q_coin_reward > 0)
            {
                rewardQcoin($utilInfo->q_coin_reward);
            }

            //Charge user

            chargeUser($utilInfo->price);


            //Add Jobs
            $dbConn = getConnection();
            $SQLselect = "SELECT PoliceBuilding.id_pk
                          FROM PoliceBuilding
                          WHERE PoliceBuilding.city_id_fk =:cityID AND police_type_id_fk =:utilType
                          ORDER BY PoliceBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilType' => $utilToBuy));
            $utilID = $stmt->fetchObject();

            for ($i = 0; $i < $utilInfo->workers; $i++)
            {
                newJob($utilID->id_pk, 'a', 1, $utilInfo->worker_education, $utilInfo->job_quality, getSalary($utilInfo->worker_education),5);
            }

            jobAssingment();
            header('Location: buyUtilities.php?utilSelected=police');


        }
        else
        {
            echo "Insufficient funds to buy business."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the utility was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}

function buyHealthcare($utilToBuy)
{

    $utilTypes = array(1,2,3,4,5,6);

    if (in_array($utilToBuy, $utilTypes))
    {

        //Get utility info
        $dbConn = getConnection();
        $SQLselect = "SELECT HealthType.price, HealthType.xp_reward, HealthType.q_coin_reward, HealthType.base_quality, HealthType.workers, HealthType.worker_education,
                            HealthType.job_quality     
                      FROM HealthType
                      WHERE HealthType.id_pk =:utilID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilToBuy));
        $utilInfo = $stmt->fetchObject();

        //Check user can afford business

        if (($utilInfo->price)<=($_SESSION['coins']))
        {
            //Add utility record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO HealthBuilding(city_id_fk, health_type_id_fk, quality)
                          VALUES (:cityID, :utilID, :quality)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilID' => $utilToBuy, ':quality' => $utilInfo->base_quality));

            //Reward user
            rewardXP($utilInfo->xp_reward);

            if ($utilInfo->q_coin_reward > 0)
            {
                rewardQcoin($utilInfo->q_coin_reward);
            }

            //Charge user

            chargeUser($utilInfo->price);

            //Add Jobs
            $dbConn = getConnection();
            $SQLselect = "SELECT HealthBuilding.id_pk
                          FROM HealthBuilding
                          WHERE HealthBuilding.city_id_fk =:cityID AND health_type_id_fk =:utilType
                          ORDER BY HealthBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':utilType' => $utilToBuy));
            $utilID = $stmt->fetchObject();

            for ($i = 0; $i < $utilInfo->workers; $i++)
            {
                newJob($utilID->id_pk, 'a', 1, $utilInfo->worker_education, $utilInfo->job_quality, getSalary($utilInfo->worker_education),6);
            }

            jobAssingment();
            header('Location: buyUtilities.php?utilSelected=health');


        }
        else
        {
            echo "Insufficient funds to buy business."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the utility was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}

?>

</body>

</html>