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
checkLogin('myEducation');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    educHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLquery = "SELECT EducationBuilding.id_pk, EducationType.name, EducationType.id_pk AS educID, EducationType.capacity, EducationType.running_cost, EducationType.workers
                 FROM EducationBuilding
                 LEFT JOIN EducationType ON EducationBuilding.educ_type_id_fk = EducationType.id_pk
                 WHERE EducationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    while($rowObj = $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(EducationSpace.id_pk) AS studentCounter
                 FROM EducationSpace
                 WHERE EducationSpace.educ_id_fk =:educID AND EducationSpace.taken = 1
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk));
        $studentCounter = $stmt1->fetchObject();

        $imagePath = getEducImage($rowObj->educID);

        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:empID AND Job.city_id_fk =:cityID AND Job.job_industry = 0 AND Job.citizen_employed = 1
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':empID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        echo "
                                <a href='individualEduc.php?educSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='$imagePath'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td class='houseDetailsName'>{$rowObj->name}</td>
                                            <td class='houseDetailsType'></td>
                                            <td class='houseDetailsUpCost'></td>
                                        </tr>
                                        <tr>
                                            <td class='houseDetailsOccupancy'>Students: {$studentCounter->studentCounter}/{$rowObj->capacity}</td>
                                            <td class='houseDetailsType'>Teaching Staff: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td class='houseDetailsUpCost'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }

    echo "          </div>                
               </div>";



}
else
{
    notLoggedIn();
}

?>

</body>

</html>