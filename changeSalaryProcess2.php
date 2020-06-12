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
checkLogin('changeSalaryProcess2');
if ($_SESSION['loggedIn'] == 'true')
{
    $industry = filter_has_var(INPUT_GET, 'industry')
        ? $_GET['industry'] : null;

    $salChangeValue = filter_has_var(INPUT_GET, 'salChangeValue')
        ? $_GET['salChangeValue'] : null;

    $empID = filter_has_var(INPUT_GET, 'empID')
        ? $_GET['empID'] : null;

    $utilType = filter_has_var(INPUT_GET, 'utilType')
        ? $_GET['utilType'] : null;

    $errors = false;

    $industryTypes = array(0,1,2,3);

    if (!(in_array($industry, $industryTypes)))
    {
        $errors = true;
    }

    if ($utilType == null)
    {
        $utilType = 0;
    }

    $utilTypes = array(0,1,2,3,4,5,6);

    if (!(in_array($utilType, $utilTypes)))
    {
        $errors = true;
    }

    if(!(filter_var($salChangeValue, FILTER_VALIDATE_INT)))
    {
        if (!($salChangeValue == 0))
        {
            $errors = true;
        }
    }

    if (($salChangeValue < (0))||($salChangeValue > (9999)))
    {
        $errors = true;
    }

    if ($errors == false)
    {
        if ($industry == 0)
        {
            $tableName = 'EducationBuilding';
            $redirect = 'individualEduc.php?educSelected=';
        }
        else if ($industry == 1)
        {
            if ($utilType == 1)
            {
                $tableName = 'ElectricityBuilding';
                $redirect = 'individualElect.php?elecSelected=';

            }
            else if ($utilType == 2)
            {
                $tableName = 'WaterBuilding';
                $redirect = 'individualWater.php?waterSelected=';

            }
            else if ($utilType == 3)
            {
                $tableName = 'FoodBuilding';
                $redirect = 'individualFood.php?foodSelected=';
            }
            else if ($utilType == 4)
            {
                $tableName = 'FireBuilding';
                $redirect = 'individualFire.php?fireSelected=';
            }
            else if ($utilType == 5)
            {
                $tableName = 'PoliceBuilding';
                $redirect = 'individualPolice.php?policeSelected=';
            }
            else if ($utilType == 6)
            {
                $tableName = 'HealthBuilding';
                $redirect = 'individualHealth.php?healthSelected=';
            }
        }
        else if ($industry == 2)
        {
            $tableName = 'RecreationBuilding';
            $redirect = 'individualRec.php?recSelected=';

        }
        else if ($industry == 3)
        {
            $tableName = 'Business';
            $redirect = 'individualBusiness.php?busSelected=';
        }

        $dbConn = getConnection();
        $SQLselect = "SELECT id_pk
                  FROM $tableName
                  WHERE id_pk =:empID AND city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array(':empID' => $empID, ':cityID' => $_SESSION['cityID']));
        $empTest = $stmt->fetchObject();

        if ($empTest)
        {
            updateSalary($salChangeValue, $industry, $empID, $utilType);

            jobAssingment();
            header('Location: '.$redirect.$empID);
        }
        else
        {
            echo "A problem occurred and the salaries were not changed."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }
    }
    else
    {
        echo "A problem occurred and the salaries were not changed."."<br>";
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