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
checkLogin('buyBusinessProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $busToBuy = filter_has_var(INPUT_GET, 'busToBuy')
        ? $_GET['busToBuy'] : null;

    $busTypes = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15);

    if (in_array($busToBuy, $busTypes))
    {

        //Get house info
        $dbConn = getConnection();
        $SQLselect = "SELECT BusinessType.price, BusinessType.xp_reward, BusinessType.q_coin_reward,
                            BusinessType.name, BusinessType.job_a_education, BusinessType.job_b_education, BusinessType.job_c_education,
                            BusinessType.job_a_quantity, BusinessType.job_b_quantity, BusinessType.job_c_quantity,
                            BusinessType.job_quality
                      FROM BusinessType
                      WHERE BusinessType.id_pk =:busID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':busID' => $busToBuy));
        $busInfo = $stmt->fetchObject();

        //Check user can afford business

        if (($busInfo->price)<=($_SESSION['coins']))
        {
            $salaryA = getSalary($busInfo->job_a_education);
            $salaryB = getSalary($busInfo->job_b_education);
            $salaryC = getSalary($busInfo->job_c_education);

            //Add business record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO Business(city_id_fk, name, bus_type_id_fk, job_a_salary, job_b_salary, job_c_salary)
                          VALUES (:cityID, :nameT, :busID, :salA, :salB, :salC)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':nameT' => $busInfo->name, ':busID' => $busToBuy, 'salA' => $salaryA, 'salB' => $salaryB, 'salC' => $salaryC));

            //Add job records

            $dbConn = getConnection();
            $SQLselect = "SELECT Business.id_pk
                          FROM Business
                          WHERE Business.city_id_fk =:cityID AND bus_type_id_fk =:busType
                          ORDER BY Business.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':busType' => $busToBuy));
            $bussID = $stmt->fetchObject();

            //Add Job A
            for ($i = 0; $i < $busInfo->job_a_quantity; $i++)
            {
                newJob($bussID->id_pk, 'a', 3, $busInfo->job_a_education, $busInfo->job_quality,$salaryA,0);
            }

            for ($i = 0; $i < $busInfo->job_b_quantity; $i++)
            {
                newJob($bussID->id_pk, 'b', 3, $busInfo->job_b_education, $busInfo->job_quality,$salaryB,0);
            }

            for ($i = 0; $i < $busInfo->job_c_quantity; $i++)
            {
                newJob($bussID->id_pk, 'c', 3, $busInfo->job_c_education, $busInfo->job_quality,$salaryC,0);
            }

            //Reward user
            rewardXP($busInfo->xp_reward);

            if ($busInfo->q_coin_reward > 0)
            {
                rewardQcoin($busInfo->q_coin_reward);
            }

            //Charge user

            $reduction = getBusReducation();
            $price = round(($busInfo->price*$reduction),0);

            $newCityCoins = ($_SESSION['coins']-($price));
            $_SESSION['coins'] = $newCityCoins;

            $dbConn = getConnection();
            $SQLupdate = "UPDATE City
                          SET coins = :coins
                          WHERE City.id_pk =:cityID
                              ";
            $stmt = $dbConn->prepare($SQLupdate);
            $stmt->execute(array(':coins' => $newCityCoins, ':cityID' => $_SESSION['cityID']));

            jobAssingment();
            header('Location: buyBusinesses.php');


        }
        else
        {
            echo "Insufficient funds to buy business."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the business was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }
}
else
{
    notLoggedIn();
}


?>

</body>

</html>