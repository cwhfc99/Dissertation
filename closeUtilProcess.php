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
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='levelMeta' level='<?php echo $_SESSION['level']?>'>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('MyUtilities');
if ($_SESSION['loggedIn'] == 'true')
{
    $utilType = filter_has_var(INPUT_GET, 'utilType')
        ? $_GET['utilType'] : null;

    $utilSelected = filter_has_var(INPUT_GET, 'utilSelected')
        ? $_GET['utilSelected'] : null;

    $utilsArray = array('electricity', 'water', 'food', 'fire', 'police', 'healthcare');

    if ((in_array($utilType, $utilsArray)))
    {
        if ($utilType == 'electricity')
        {
            $tableName = 'ElectricityBuilding';
            $utilJobDelete = 1;
        }
        else if ($utilType == 'water')
        {
            $tableName = 'WaterBuilding';
            $utilJobDelete = 2;
        }
        else if ($utilType == 'food')
        {
            $tableName = 'FoodBuilding';
            $utilJobDelete = 3;
        }
        else if ($utilType == 'fire')
        {
            $tableName = 'FireBuilding';
            $utilJobDelete = 4;
        }
        else if ($utilType == 'police')
        {
            $tableName = 'PoliceBuilding';
            $utilJobDelete = 5;
        }
        else if ($utilType == 'healthcare')
        {
            $tableName = 'HealthBuilding';
            $utilJobDelete = 6;
        }

        $dbConn = getConnection();
        $SQLselect = "SELECT id_pk
                      FROM $tableName
                      WHERE id_pk =:utilID AND city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':utilID' => $utilSelected, ':cityID' => $_SESSION['cityID']));
        $recTest = $stmt->fetchObject();
        if ($recTest)
        {
            $dbConn = getConnection();
            $SQLdelete = "DELETE
                          FROM $tableName
                          WHERE id_pk =:utilID
                         ";
            $stmt = $dbConn->prepare($SQLdelete);
            $stmt->execute(array(':utilID' => $utilSelected));


            //Delete Jobs
            $dbConn = getConnection();
            $SQLdelete = "DELETE 
                      FROM Job
                      WHERE Job.employer_id_fk =:busID AND Job.job_industry = 1 AND Job.util_type =:utilType
                      ";
            $stmt = $dbConn->prepare($SQLdelete);
            $stmt->execute(array(':busID' => $utilSelected, ':utilType' => $utilJobDelete));

            jobAssingment();
            utilHappiness();
            header('Location: myUtilities.php?utilSelected='.$utilType);
        }
        else
        {
            echo "A problem occurred and the utility was not closed."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }

    }
    else
    {
        echo "A problem occurred and the utility was not closed."."<br>";
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