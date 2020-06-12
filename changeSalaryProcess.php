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
checkLogin('changeSalaryProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $busSelected = filter_has_var(INPUT_GET, 'busSelected')
        ? $_GET['busSelected'] : null;

    $salAChangeValue = filter_has_var(INPUT_GET, 'salAChangeValue')
        ? $_GET['salAChangeValue'] : null;

    $salBChangeValue = filter_has_var(INPUT_GET, 'salBChangeValue')
        ? $_GET['salBChangeValue'] : null;

    $salCChangeValue = filter_has_var(INPUT_GET, 'salCChangeValue')
        ? $_GET['salCChangeValue'] : null;

    $errors = false;

    $dbConn = getConnection();
    $SQLselect = "SELECT Business.id_pk
                  FROM Business
                  WHERE Business.id_pk =:busID AND Business.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':busID' => $busSelected, ':cityID' => $_SESSION['cityID']));
    $busTest = $stmt->fetchObject();

    if(!(filter_var($salAChangeValue, FILTER_VALIDATE_INT)))
    {
        if (!($salAChangeValue == 0))
        {
            $errors = true;
        }
    }

    if(!(filter_var($salBChangeValue, FILTER_VALIDATE_INT)))
    {
        if (!($salBChangeValue == 0))
        {
            $errors = true;
        }
    }

    if(!(filter_var($salCChangeValue, FILTER_VALIDATE_INT)))
    {
        if (!($salCChangeValue == 0))
        {
            $errors = true;
        }
    }

    if (($salAChangeValue < (0))||($salAChangeValue > (9999)))
    {
        $errors = true;
    }

    if (($salBChangeValue < (0))||($salBChangeValue > (9999)))
    {
        $errors = true;
    }

    if (($salCChangeValue < (0))||($salCChangeValue > (9999)))
    {
        $errors = true;
    }

    if (($busTest) && ($errors == false))
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Business
                      SET job_a_salary =:salA, job_b_salary =:salB, job_c_salary =:salC
                      WHERE Business.id_pk =:busID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':busID' => $busSelected, ':salA' => $salAChangeValue, ':salB' => $salBChangeValue, ':salC' => $salCChangeValue));

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Job
                      SET salary =:salA
                      WHERE Job.job_industry = 3 AND Job.employer_role_type = 'a' AND Job.employer_id_fk =:busID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':busID' => $busSelected, ':salA' => $salAChangeValue));

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Job
                      SET salary =:salB
                      WHERE Job.job_industry = 3 AND Job.employer_role_type = 'b' AND Job.employer_id_fk =:busID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':busID' => $busSelected, ':salB' => $salBChangeValue));

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Job
                      SET salary =:salC
                      WHERE Job.job_industry = 3 AND Job.employer_role_type = 'c' AND Job.employer_id_fk =:busID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':busID' => $busSelected, ':salC' => $salCChangeValue));


        jobAssingment();
        header('Location: individualBusiness.php?busSelected='.$busSelected);
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