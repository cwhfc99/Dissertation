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
checkLogin('buyEducationProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $educToBuy = filter_has_var(INPUT_GET, 'educToBuy')
        ? $_GET['educToBuy'] : null;

    $educTypes = array(1,2,3,4,5,6,7,8,9,10,11,12);

    if (in_array($educToBuy, $educTypes))
    {

        //Get education info
        $dbConn = getConnection();
        $SQLselect = "SELECT EducationType.price, EducationType.xp_reward, EducationType.q_coin_reward,
                            EducationType.name, EducationType.capacity, EducationType.workers, EducationType.worker_education, 
                            EducationType.job_quality
                      FROM EducationType
                      WHERE EducationType.id_pk =:educID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':educID' => $educToBuy));
        $educInfo = $stmt->fetchObject();

        //Check user can afford education

        if (($educInfo->price)<=($_SESSION['coins']))
        {
            if ($educToBuy <= 4)
            {
                $educType = 0;
            }
            else if (($educToBuy > 4) && ($educToBuy <= 8))
            {
                $educType = 1;
            }
            else
            {
                $educType = 2;
            }



            //Add eduction record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO EducationBuilding(city_id_fk, educ_type_id_fk)
                          VALUES (:cityID, :educID)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':educID' => $educToBuy));

            //Add educational spaces records

            $dbConn = getConnection();
            $SQLselect = "SELECT EducationBuilding.id_pk
                          FROM EducationBuilding
                          WHERE EducationBuilding.city_id_fk =:cityID AND educ_type_id_fk =:educType
                          ORDER BY EducationBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':educType' => $educToBuy));
            $educID = $stmt->fetchObject();

            for ($i = 0; $i < $educInfo->capacity; $i++)
            {
                $dbConn = getConnection();
                $SQLinsert = "INSERT INTO EducationSpace( city_id_fk, educ_id_fk, educ_type, taken, year)
                          VALUES (:cityID, :educID, :educType, 0, 0)
                     ";
                $stmt1 = $dbConn->prepare($SQLinsert);
                $stmt1->execute(array(':cityID' => $_SESSION['cityID'], ':educID' => $educID->id_pk, ':educType' => $educType));
            }

            for ($i = 0; $i < $educInfo->workers; $i++)
            {
                newJob($educID->id_pk, 'a', 0, $educInfo->worker_education, $educInfo->job_quality, getSalary($educInfo->worker_education),0);
            }


            //Reward user
            rewardXP($educInfo->xp_reward);

            if ($educInfo->q_coin_reward > 0)
            {
                rewardQcoin($educInfo->q_coin_reward);
            }

            //Charge user

            $newCityCoins = ($_SESSION['coins']-($educInfo->price));
            $_SESSION['coins'] = $newCityCoins;

            $dbConn = getConnection();
            $SQLupdate = "UPDATE City
                          SET coins = :coins
                          WHERE City.id_pk =:cityID
                              ";
            $stmt = $dbConn->prepare($SQLupdate);
            $stmt->execute(array(':coins' => $newCityCoins, ':cityID' => $_SESSION['cityID']));

            jobAssingment();
            educAssignment();
            header('Location: buyEducation.php');

        }
        else
        {
            echo "Insufficient funds to buy education."."<br>";
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