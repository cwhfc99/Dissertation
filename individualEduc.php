<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$educSelected = filter_has_var(INPUT_GET, 'educSelected')
    ? $_GET['educSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $busSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualEduc.php?educSelected='.$educSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');



    educHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT EducationBuilding.id_pk
                  FROM EducationBuilding
                  WHERE EducationBuilding.id_pk =:educID AND EducationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':educID' => $educSelected, ':cityID' => $_SESSION['cityID']));
    $educTest = $stmt->fetchObject();
    if (!$educTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT EducationBuilding.id_pk
                      FROM EducationBuilding
                      WHERE EducationBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $educTemp = $stmt->fetchObject();
        $educSelected = $educTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT EducationBuilding.id_pk, EducationType.name, EducationBuilding.educ_type_id_fk
                  FROM EducationBuilding
                  LEFT JOIN EducationType ON EducationBuilding.educ_type_id_fk = EducationType.id_pk
                  WHERE EducationBuilding.id_pk =:educID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':educID' => $educSelected));
    $educDetails = $stmt->fetchObject();

    $imagePath = getEducImage($educDetails->educ_type_id_fk);

    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='$imagePath'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$educDetails->name}</div>
                                    <div class='closeEducButt' onclick='displayLoading(\"closeEducProcess.php?educSelected={$educSelected}\")'>Close Education</div>
                                </div>
                                <div class='educSpaceTableHolder'>
                                    <table style='width: 100%'>
                                        <tr>
                                            <th></th>
                                            <th>Space Occupied</th>
                                            <th>Student Age</th>
                                            <th>Year of Course</th>
                                        </tr>";

    $dbConn = getConnection();
    $SQLselect = "SELECT EducationSpace.id_pk, EducationSpace.taken, EducationSpace.year, Citizen.age
                  FROM EducationSpace
                  LEFT JOIN Citizen ON EducationSpace.id_pk = Citizen.education_id_fk
                  WHERE EducationSpace.educ_id_fk =:educID
                  ORDER BY EducationSpace.taken DESC
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':educID' => $educSelected));

    $i = 1;
    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->taken == 1)
        {
            $spaceTaken = '✓';
        }
        else
        {
            $spaceTaken = '✗';
        }
        if ($rowObj->year == 0)
        {
            $year = null;
        }
        else
        {
            $year = $rowObj->year;
        }

        echo "<tr>
                <td style='text-align: center'>{$i}</td>
                <td style='text-align: center'>{$spaceTaken}</td>
                <td style='text-align: center'>{$rowObj->age}</td>
                <td style='text-align: center'>{$year}</td>

              </tr>";
        $i++;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT EducationBuilding.id_pk, EducationType.capacity, EducationType.running_cost, EducationType.workers
                  FROM EducationBuilding
                  LEFT JOIN EducationType ON EducationBuilding.educ_type_id_fk = EducationType.id_pk
                  WHERE EducationBuilding.id_pk =:educID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':educID' => $educSelected));
    $moreInfo = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(EducationSpace.id_pk) AS studentCounter
                 FROM EducationSpace
                 WHERE EducationSpace.educ_id_fk =:educID AND EducationSpace.taken = 1
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $educSelected));
    $studentCounter = $stmt1->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 0 AND Job.citizen_employed = 1
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $educSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();

    $educRequired = getEducation($jobDetails->educ_required);
                                
    echo "                        </table>
                                </div>
                                <div class='individualEducLower'>
                                    <div class='individualEducLowerHalfLeft'>
                                        <table style='width: 100%;'>
                                            <tr>
                                                <td style='text-align: center; height: 60px;'>Occupancy: {$studentCounter->studentCounter}/{$moreInfo->capacity}</td>
                                                <td style='text-align: center'>Upkeep Cost: {$moreInfo->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            </tr>
                                            <tr>
                                                <td style='text-align: center; height: 60px;'>Teaching Staff: {$jobDetails->jobCounter}/{$moreInfo->workers}</td>
                                                <td style='text-align: center'>Job Quality: {$jobDetails->quality}</td>
                                            </tr>
                                        
                                        </table>
                                    </div>
                                    <div class='individualEducLowerHalfRight'>
                                        <form id='changeSal' action='changeSalaryProcess2.php' method='get' oninput='x.value=salChangeValue.value;'>
                                            <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif; margin-right: 10px;' >0<input  style='margin-top: 2px; width: 150px;' type='range' name='salChangeValue' value='{$jobDetails->salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                            <input type='submit' value='Change Salary' class='changeRentButt' id='changeRentButt' style='margin-top: 0.001px'>
                                            <br><br><span><div >New Salary: <output form='changeSal' name='x'></output></div></span>
                                            <br><span><div style='margin-top: -10px;'>Education Required: $educRequired</div></span>

                                            <input type='hidden' name='empID' value='{$educSelected}'>
                                            <input type='hidden' name='industry' value='0'>
                                            <input type='hidden' name='utilType' value='0'>
                                        </form>
                                    </div>
                                </div>
                                                    

                 </div>";

}
else
{
    notLoggedIn();
}

?>

<script type="text/javascript">
    window.addEventListener('load', function() {
        'use strict';
    });

    var bool = false;
    var input = document.getElementById('renameValue');
    var butt = document.getElementById('renameHouseButt');

    input.addEventListener("input", validateInput);

    function validateInput()
    {
        input.style.borderWidth = "2px";
        if ((input.value) == "" || (input.value.length) > 15) //If the first name is empty or over 20 character.
        {
            bool = false;
            input.style.borderColor = "red";
        }
        else
        {
            bool = true;
            input.style.borderColor = "green";
        }
        validateForm();
    }

    function validateForm()
    {
        if (bool == false)
        {
            butt.disabled = true;
        }
        else
        {
            butt.disabled = false;
        }
    }

</script>

</body>

</html>