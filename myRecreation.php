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
checkLogin('myRecreation');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    recHolder();
    turnButtBlack('myHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLquery = "SELECT RecreationBuilding.id_pk, RecreationType.id_pk AS recID, RecreationType.name, RecreationType.running_cost, RecreationType.capacity
                        , RecreationType.workers, RecreationType.base_revenue
                 FROM RecreationBuilding
                 LEFT JOIN RecreationType ON RecreationBuilding.rec_type_id_fk = RecreationType.id_pk
                 WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));



    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getRecImage($rowObj->recID);

        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Job.id_pk) AS jobCounter
                 FROM Job
                 WHERE Job.employer_id_fk =:educID AND Job.city_id_fk =:cityID AND Job.job_industry = 2 AND Job.citizen_employed = 1
                 ";
        $stmt1 = $dbConn->prepare($SQLquery);
        $stmt1->execute(array(':educID' => $rowObj->id_pk, ':cityID' => $_SESSION['cityID']));
        $jobDetails = $stmt1->fetchObject();

        echo "
                                <a href='individualRec.php?recSelected={$rowObj->id_pk}' style='color: black; text-decoration: none;' ><div class='individualHouseHolder'>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='$imagePath'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td style='width: 175px; font-family: \"Arial Black\", Arial, sans-serif;'>{$rowObj->name}</td>
                                            <td style='width: 150px;'></td>
                                            <td style='width: 175px;'></td>
                                            <td style='width: 175px;'></td>
                                        </tr>
                                        <tr>
                                            <td style='width: 175px;'>Capacity: {$rowObj->capacity}</td>
                                            <td style='width: 150px;'>Workers: {$jobDetails->jobCounter}/{$rowObj->workers}</td>
                                            <td style='width: 175px;'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>";

        if ($rowObj->base_revenue > 0)
        {
            $revenue = ($rowObj->base_revenue)*($_SESSION['population']);
            echo "<td style='width: 175px;'>Yearly Revenue: {$revenue} <img src='images/coins_icon.png' width='15px' height='15px'></td>";
        }
        else
        {
            echo "<td style='width: 175px; text-align: right'></td>";
        }

        echo"                               
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