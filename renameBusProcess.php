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
checkLogin('renameBusProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $busSelected = filter_has_var(INPUT_GET, 'busSelected')
        ? $_GET['busSelected'] : null;

    $renameValue = filter_has_var(INPUT_GET, 'renameValue')
        ? $_GET['renameValue'] : null;

    $errors = false;

    if ((strlen($renameValue)>15)||(strlen($renameValue)==0))
    {
        $errors = true;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT Business.id_pk
                  FROM Business
                  WHERE Business.id_pk =:houseID AND Business.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':houseID' => $busSelected, ':cityID' => $_SESSION['cityID']));
    $busTest = $stmt->fetchObject();
    if (($busTest) && ($errors == false))
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Business
                      SET name =:nameT
                      WHERE Business.id_pk =:busID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':nameT' => $renameValue, ':busID' => $busSelected));

        header('Location: individualBusiness.php?busSelected='.$busSelected);
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