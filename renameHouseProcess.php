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
checkLogin('renameHouseProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $houseSelected = filter_has_var(INPUT_GET, 'houseSelected')
        ? $_GET['houseSelected'] : null;

    $renameValue = filter_has_var(INPUT_GET, 'renameValue')
        ? $_GET['renameValue'] : null;

    $errors = false;

    if ((strlen($renameValue)>15)||(strlen($renameValue)==0))
    {
        $errors = true;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT Accommodation.id_pk
                  FROM Accommodation
                  WHERE Accommodation.id_pk =:houseID AND Accommodation.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':houseID' => $houseSelected, ':cityID' => $_SESSION['cityID']));
    $houseTest = $stmt->fetchObject();
    if (($houseTest) && ($errors == false))
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Accommodation
                      SET name =:nameT
                      WHERE Accommodation.id_pk =:houseID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':nameT' => $renameValue, ':houseID' => $houseSelected));

        header('Location: individualHouse.php?houseSelected='.$houseSelected);
    }
    else
    {
        echo "A problem occurred and the property was not rename."."<br>";
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