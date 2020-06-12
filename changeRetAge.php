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
checkLogin('changeRetAge');
if ($_SESSION['loggedIn'] == 'true')
{
    $retAge = filter_has_var(INPUT_GET, 'RetAgeChangeValue')
        ? $_GET['RetAgeChangeValue'] : null;


    if ((filter_var($retAge, FILTER_VALIDATE_INT)) && ($retAge >= 51) && ($retAge <= 81))
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE City
                      SET retirement_age = :retAge
                      WHERE City.id_pk =:cityID
                              ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':retAge' => $retAge, ':cityID' => $_SESSION['cityID']));

        utilHappiness();
        header('Location: citizensOverview.php');
    }
    else
    {
        echo "A problem occurred and the retirement age was not changed."."<br>";
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