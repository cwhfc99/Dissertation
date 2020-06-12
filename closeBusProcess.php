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
checkLogin('closeBusProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $busSelected = filter_has_var(INPUT_GET, 'busSelected')
        ? $_GET['busSelected'] : null;

    $dbConn = getConnection();
    $SQLselect = "SELECT Business.id_pk
                  FROM Business
                  WHERE Business.id_pk =:busID AND Business.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':busID' => $busSelected, ':cityID' => $_SESSION['cityID']));
    $busTest = $stmt->fetchObject();
    if ($busTest)
    {
        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM Business
                      WHERE Business.id_pk =:busID
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':busID' => $busSelected));

        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM Job
                      WHERE Job.employer_id_fk =:busID AND Job.job_industry = 3
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':busID' => $busSelected));

        jobAssingment();
        header('Location: myBusinesses.php');
    }
    else
    {
        echo "A problem occurred and the business was not closed."."<br>";
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