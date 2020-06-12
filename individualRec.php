<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

$recSelected = filter_has_var(INPUT_GET, 'recSelected')
    ? $_GET['recSelected'] : null;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='houseMeta' houseSelected='<?php echo $recSelected?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('individualRec.php?recSelected='.$recSelected);
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');



    recHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLselect = "SELECT RecreationBuilding.id_pk
                  FROM RecreationBuilding
                  WHERE RecreationBuilding.id_pk =:recID AND RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':recID' => $recSelected, ':cityID' => $_SESSION['cityID']));
    $recTest = $stmt->fetchObject();
    if (!$recTest)
    {
        $dbConn = getConnection();
        $SQLselect = "SELECT RecreationBuilding.id_pk
                      FROM RecreationBuilding
                      WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
        $stmt = $dbConn->prepare($SQLselect);
        $stmt->execute(array( ':cityID' => $_SESSION['cityID']));
        $recTemp = $stmt->fetchObject();
        $recSelected = $recTemp->id_pk;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT RecreationBuilding.id_pk, RecreationType.name, RecreationBuilding.rec_type_id_fk, RecreationType.running_cost, RecreationType.base_revenue, RecreationType.workers
                  FROM RecreationBuilding
                  LEFT JOIN RecreationType ON RecreationBuilding.rec_type_id_fk = RecreationType.id_pk
                  WHERE RecreationBuilding.id_pk =:recID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':recID' => $recSelected));
    $recDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter, Job.salary, Job.educ_required, Job.quality, SUM(Job.salary) AS totSal
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 2 AND Job.citizen_employed = 1
                 ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':educID' => $recSelected, ':cityID' => $_SESSION['cityID']));
    $jobDetails = $stmt1->fetchObject();

    $profit = (($recDetails->base_revenue)*($_SESSION['population']))-($recDetails->running_cost)-($jobDetails->totSal);

    $imagePath = getRecImage($recDetails->rec_type_id_fk);
    $jobHappiness = getAverageJobHap(2, $recSelected,0);

    echo "          
                        <div class='individualHouseUpper'>
                            <div class='inHouseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='150px' height='150px' src='$imagePath'></div>
                                <div class='indHouseUpperUpper'>
                                    <div style='font-family: \"Arial Black\", Arial, sans-serif; margin-left: 200px;'>{$recDetails->name}</div>
                                    <div class='closeEducButt' onclick='displayLoading(\"closeRecProcess.php?recSelected={$recSelected}\")'>Close Recreation</div>
                                </div>
                                


    
                                    <div class='inBusMiddle'>
                                        <table class='inBusInfoTable'>
                                                <tr>
                                                    <td style='width: 200px;'></td>
                                                    <td style='width: 200px;'>Workers: {$jobDetails->jobCounter}/{$recDetails->workers}</td>
                                                    <td style='width: 200px;'>Job Quality: {$jobDetails->quality}</td>
                                                    <td style='width: 230px;'>Average Job Happiness: {$jobHappiness}</td>
                                                </tr>
                                                <tr>";

    if (($recDetails->base_revenue) > 0)
    {
        $revenue = ($recDetails->base_revenue)*($_SESSION['population']);
        echo "<td style = 'width: 200px;' > Yearly Revenue: {$revenue} <img src = 'images/coins_icon.png' width = '15px' height = '15px' ></td >";
    }
    else
    {
        echo "<td style = 'width: 200px;' ></td >";
    }
    $educRequired = getEducation($jobDetails->educ_required);

    echo "                                          <td style='width: 200px;'>Upkeep Cost: {$recDetails->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 200px;'>Yearly Wages: {$jobDetails->totSal} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                    <td style='width: 230px;'>Yearly Profit/Loss: {$profit}<img src='images/coins_icon.png' width='15px' height='15px'></td>
                                                </tr>
                                        </table>
                                     </div>
                                    
                        </div>";

    echo"                <div class='individualBusLower'>
                            <form id='changeSal' action='changeSalaryProcess2.php' method='get' oninput='x.value=salChangeValue.value;'>
                                            <span style=' margin-left: 0px; font-family: \"Arial Black\", Arial, sans-serif; margin-right: 10px;' >0<input  style='margin-top: 2px; width: 600px;' type='range' name='salChangeValue' value='{$jobDetails->salary}' id='rentChangeValue' min='0' max='9999'>9999</span>
                                            <input type='submit' value='Change Salary' class='changeRentButt' id='changeRentButt' style='margin-top: 0.001px'>
                                            <br><br><span><div >New Salary: <output form='changeSal' name='x'></output></div></span>
                                            <br><span><div style='margin-top: -10px;'>Education Required: {$educRequired}</div></span>

                                       <input type='hidden' name='empID' value='{$recSelected}'>
                                       <input type='hidden' name='industry' value='2'>
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