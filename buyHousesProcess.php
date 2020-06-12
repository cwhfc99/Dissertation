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
checkLogin('buyHousesProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $houseToBuy = filter_has_var(INPUT_GET, 'houseToBuy')
        ? $_GET['houseToBuy'] : null;

    $houseTypes = array(1,2,3,4,5,6,7,8);

    if (in_array($houseToBuy, $houseTypes))
    {

        //Get house info
        $dbConn = getConnection();
        $SQLselect = "SELECT AccommodationType.price, AccommodationType.xp_reward, AccommodationType.q_coin_reward, AccommodationType.capacity,
                            AccommodationType.name, AccommodationType.base_quality, AccommodationType.lower_rent_limit, AccommodationType.upper_rent_limit
                      FROM AccommodationType
                      WHERE AccommodationType.id_pk =:houseID
                     ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':houseID' => $houseToBuy));
        $houseInfo = $stmt->fetchObject();
        $rent = ((($houseInfo->lower_rent_limit)+($houseInfo->upper_rent_limit))/2);
        $rent = round($rent, 0);

        //Check user can afford upgrade

        if (($houseInfo->price)<=($_SESSION['coins']))
        {

            //Add accommodation record

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO Accommodation(city_id_fk, name, accom_type_id_fk, quality, rent)
                          VALUES (:cityID, :nameT, :accomID, :quality, :rent)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':nameT' => $houseInfo->name, ':accomID' => $houseToBuy, ':quality' => $houseInfo->base_quality, ':rent' => $rent));

            //Add room records

            $capacity = $houseInfo->capacity;

            $dbConn = getConnection();
            $SQLselect = "SELECT Accommodation.id_pk
                          FROM Accommodation
                          WHERE Accommodation.city_id_fk =:cityID AND accom_type_id_fk =:accomType
                          ORDER BY Accommodation.id_pk DESC 
                         ";
            $stmt = $dbConn->prepare($SQLselect);
            $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':accomType' => $houseToBuy));
            $houseID = $stmt->fetchObject();

            for ($i = 0; $i < $capacity; $i++)
            {
                $dbConn = getConnection();
                $SQLinsert = "INSERT INTO Room(city_id_fk, accom_id_fk, inhabited)
                              VALUES (:cityID, :accomID, 0)
                             ";
                $stmt1 = $dbConn->prepare($SQLinsert);
                $stmt1->execute(array(':cityID' => $_SESSION['cityID'], ':accomID' => $houseID->id_pk));
            }

            //Reward user
            rewardXP($houseInfo->xp_reward);

            if ($houseInfo->q_coin_reward > 0)
            {
                rewardQcoin($houseInfo->q_coin_reward);
            }

            //Charge user

            $newCityCoins = ($_SESSION['coins']-($houseInfo->price));
            $_SESSION['coins'] = $newCityCoins;

            $dbConn = getConnection();
            $SQLupdate = "UPDATE City
                          SET coins = :coins
                          WHERE City.id_pk =:cityID
                              ";
            $stmt = $dbConn->prepare($SQLupdate);
            $stmt->execute(array(':coins' => $newCityCoins, ':cityID' => $_SESSION['cityID']));

            houseAssingment();
            header('Location: buyHouses.php');

        }
        else
        {
            echo "Insufficient funds to buy accommodation."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the accommodation was not bought."."<br>";
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