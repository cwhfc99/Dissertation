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
checkLogin('buyRecProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $recToBuy = filter_has_var(INPUT_GET, 'recToBuy')
        ? $_GET['recToBuy'] : null;

    $recTypes = array(1,2,3,4,5,6,7,8,9,10);

    if (in_array($recToBuy, $recTypes))
    {

        //Get recreation info
        $dbConn = getConnection();
        $SQLselect = "SELECT RecreationType.price, RecreationType.xp_reward, RecreationType.q_coin_reward,
                            RecreationType.name, RecreationType.capacity, RecreationType.workers, RecreationType.worker_education,
                            RecreationType.job_quality
                      FROM RecreationType
                      WHERE RecreationType.id_pk =:recID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':recID' => $recToBuy));
        $recInfo = $stmt->fetchObject();

        //Check user can afford recreation

        if (($recInfo->price)<=($_SESSION['coins']))
        {

            //Add recreation record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO RecreationBuilding(city_id_fk, rec_type_id_fk)
                          VALUES (:cityID, :recID)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':recID' => $recToBuy));

            $dbConn = getConnection();
            $SQLselect = "SELECT RecreationBuilding.id_pk
                          FROM RecreationBuilding
                          WHERE RecreationBuilding.city_id_fk =:cityID AND rec_type_id_fk =:recType
                          ORDER BY RecreationBuilding.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':recType' => $recToBuy));
            $recID = $stmt->fetchObject();

            //ADD JOBS
            for ($i = 0; $i < $recInfo->workers; $i++)
            {
                newJob($recID->id_pk, 'a', 2, $recInfo->worker_education, $recInfo->job_quality, getSalary($recInfo->worker_education),0);
            }

            //Reward user
            rewardXP($recInfo->xp_reward);

            if ($educInfo->q_coin_reward > 0)
            {
                rewardQcoin($recInfo->q_coin_reward);
            }

            //Charge user

            chargeUser($recInfo->price);

            jobAssingment();
            recHappiness();
            header('Location: buyRecreation.php');

        }
        else
        {
            echo "Insufficient funds to buy recreation."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the recreation was not bought."."<br>";
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