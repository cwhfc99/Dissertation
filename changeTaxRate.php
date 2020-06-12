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
checkLogin('changeTaxRate');
if ($_SESSION['loggedIn'] == 'true')
{
    $taxRate = filter_has_var(INPUT_GET, 'TaxChangeValue')
        ? $_GET['TaxChangeValue'] : null;


    if ((filter_var($taxRate, FILTER_VALIDATE_INT)) && ($taxRate >= 0) && ($taxRate <= 45))
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE City
                      SET tax_rate = :taxRate
                      WHERE City.id_pk =:cityID
                              ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':taxRate' => $taxRate, ':cityID' => $_SESSION['cityID']));

        utilHappiness();
        header('Location: citizensOverview.php');
    }
    else
    {
        echo "A problem occurred and the tax rate was not changed."."<br>";
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