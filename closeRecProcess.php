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
checkLogin('closeRecProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $recSelected = filter_has_var(INPUT_GET, 'recSelected')
        ? $_GET['recSelected'] : null;

    $dbConn = getConnection();
    $SQLselect = "SELECT RecreationBuilding.id_pk
                  FROM RecreationBuilding
                  WHERE RecreationBuilding.id_pk =:recID AND RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':recID' => $recSelected, ':cityID' => $_SESSION['cityID']));
    $recTest = $stmt->fetchObject();
    if ($recTest)
    {
        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM RecreationBuilding
                      WHERE RecreationBuilding.id_pk =:recID
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':recID' => $recSelected));


        //Delete Jobs
        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM Job
                      WHERE Job.employer_id_fk =:busID AND Job.job_industry = 2
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':busID' => $recSelected));

        jobAssingment();
        recHappiness();
        header('Location: myRecreation.php');
    }
    else
    {
        echo "A problem occurred and the recreation was not closed."."<br>";
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