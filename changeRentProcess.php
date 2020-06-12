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
checkLogin('changeRentProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $houseSelected = filter_has_var(INPUT_GET, 'houseSelected')
        ? $_GET['houseSelected'] : null;

    $rentChangeValue = filter_has_var(INPUT_GET, 'rentChangeValue')
        ? $_GET['rentChangeValue'] : null;

    $errors = false;

    $dbConn = getConnection();
    $SQLselect = "SELECT Accommodation.id_pk, AccommodationType.lower_rent_limit, AccommodationType.upper_rent_limit
                  FROM Accommodation
                  LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                  WHERE Accommodation.id_pk =:houseID AND Accommodation.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':houseID' => $houseSelected, ':cityID' => $_SESSION['cityID']));
    $houseTest = $stmt->fetchObject();

    if(!(filter_var($rentChangeValue, FILTER_VALIDATE_INT)))
    {
        if (!($rentChangeValue == 0))
        {
            $errors = true;
        }
    }

    if (($rentChangeValue < ($houseTest->lower_rent_limit))||($rentChangeValue > ($houseTest->upper_rent_limit)))
    {
        $errors = true;
    }

    if (($houseTest) && ($errors == false))
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Accommodation
                      SET rent =:rent
                      WHERE Accommodation.id_pk =:houseID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':rent' => $rentChangeValue, ':houseID' => $houseSelected));

        houseAssingment();

        header('Location: individualHouse.php?houseSelected='.$houseSelected);
    }
    else
    {
        echo "A problem occurred and the rent was not changed."."<br>";
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