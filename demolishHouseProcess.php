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
checkLogin('demolishHouseProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $houseSelected = filter_has_var(INPUT_GET, 'houseSelected')
        ? $_GET['houseSelected'] : null;

    $dbConn = getConnection();
    $SQLselect = "SELECT Accommodation.id_pk
                  FROM Accommodation
                  WHERE Accommodation.id_pk =:houseID AND Accommodation.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':houseID' => $houseSelected, ':cityID' => $_SESSION['cityID']));
    $houseTest = $stmt->fetchObject();
    if ($houseTest)
    {
        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM Accommodation
                      WHERE Accommodation.id_pk =:houseID
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':houseID' => $houseSelected));

        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM Room
                      WHERE Room.accom_id_fk =:houseID
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':houseID' => $houseSelected));

        houseAssingment();
        header('Location: myHouses.php');
    }
    else
    {
        echo "A problem occurred and the accommodation was not demolished."."<br>";
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