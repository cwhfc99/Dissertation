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
checkLogin('closeEducProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $educSelected = filter_has_var(INPUT_GET, 'educSelected')
        ? $_GET['educSelected'] : null;

    $dbConn = getConnection();
    $SQLselect = "SELECT EducationBuilding.id_pk
                  FROM EducationBuilding
                  WHERE EducationBuilding.id_pk =:educID AND EducationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':educID' => $educSelected, ':cityID' => $_SESSION['cityID']));
    $educTest = $stmt->fetchObject();
    if ($educTest)
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      LEFT JOIN EducationSpace ON Citizen.education_id_fk = EducationSpace.id_pk
                      SET education_id_fk = 0, in_education = 0
                      WHERE EducationSpace.educ_id_fk =:educID
                      ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':educID' => $educSelected));

        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM EducationBuilding
                      WHERE EducationBuilding.id_pk =:educID
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':educID' => $educSelected));

        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM EducationSpace
                      WHERE EducationSpace.educ_id_fk =:educID
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':educID' => $educSelected));

        //Delete Jobs
        $dbConn = getConnection();
        $SQLdelete = "DELETE 
                      FROM Job
                      WHERE Job.employer_id_fk =:busID AND Job.job_industry = 0
                      ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':busID' => $educSelected));

        jobAssingment();
        educAssignment();
        header('Location: myEducation.php');
    }
    else
    {
        echo "A problem occurred and the educational building was not closed."."<br>";
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