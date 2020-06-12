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
    <meta id='coinsMeta' coins='<?php echo $_SESSION['coins']?>'>
    <meta id='levelMeta' level='<?php echo $_SESSION['level']?>'>
    <title>Home Page</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('lastYearPeople');
if ($_SESSION['loggedIn'] == 'true')
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET citizens_seen = 1
                  WHERE LastYear.city_id_fk =:cityID
                              ";
    $stmt4 = $dbConn->prepare($SQLupdate);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID']));

    buildBanner();
    turnButtBlack('gameButt');

    lastYearHolder();
    turnButtBlack('citizens');


    $dbConn = getConnection();
    $SQLquery = "SELECT year
                 FROM City
                 WHERE City.id_pk =:cityID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $year = $stmt->fetchObject();
    $reportYear = $year->year - 1;
    if ($reportYear < 0)
    {
        $reportYear = 0;
    }

    if ($year->year == 0)
    {
        $dbConn = getConnection();
        $querySQL = "SELECT AVG(Citizen.overall_happiness) as overall_happiness, AVG(Citizen.housing_happiness) as housing_happiness, AVG(Citizen.education_happiness) as education_happiness, 
                        AVG(Citizen.job_happiness) as job_happiness, AVG(Citizen.recreation_happiness) as recreation_happiness, 
                        AVG(Citizen.health_happiness) as health_happiness, AVG(Citizen.safety_happiness) as safety_happiness, AVG(Citizen.qol_happiness) as qol_happiness
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID 
                 ";
        $stmt = $dbConn->prepare($querySQL);
        $stmt->execute(array(':cityID' => $_SESSION['cityID']));
        $happ = $stmt->fetchObject();
    }
    else
    {
        $dbConn = getConnection();
        $querySQL = "SELECT overall_happiness, housing_happiness, education_happiness,
                        job_happiness, recreation_happiness, 
                        health_happiness, safety_happiness, qol_happiness
                 FROM LastYear
                 WHERE LastYear.city_id_fk =:cityID 
                 ";
        $stmt = $dbConn->prepare($querySQL);
        $stmt->execute(array(':cityID' => $_SESSION['cityID']));
        $happ = $stmt->fetchObject();
    }

    $dbConn = getConnection();
    $querySQL = "SELECT born, arrived, starting_pop, homeless, leaving, die, retired_with_fund, retired,
                        started_school, started_college, started_uni, school_grad, college_grad, uni_grad
                 FROM LastYear
                 WHERE LastYear.city_id_fk =:cityID 
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $people = $stmt->fetchObject();


    if ($_SESSION['level'] > 14)
    {
        $safetyName = 'Safety Happiness: ';
        $safetyNum = $happ->safety_happiness;
    }
    else
    {
        $safetyName = null;
        $safetyNum = null;
    }

    $leftTot = $people->leaving + $people->homeless;
    $difference = $people->born + $people->arrived - $people->die - $leftTot;
    if ($difference > 0)
    {
        $difference = "+".$difference;
    }


    echo    "<h1 style='font-family: \"Arial Black\", Arial, sans-serif; margin-left:  10px; margin-top: 0px'>Citizens Report for Year {$reportYear}</h1>
               <div class='lastYearCitizensTableHolder'>
                <table style='width: 100%'>
                    <tr>
                        <th style='text-align: left;text-decoration: underline'>Happiness</th>
                        <td></td>
                        <th style='text-align: left;text-decoration: underline'>Population Difference</th>
                        <td></td>
                        <th style='text-align: left;text-decoration: underline'>Miscellaneous</th>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Overall Happiness: </td>
                        <td>{$happ->overall_happiness}</td>
                        <td>Starting Population:</td>
                        <td>{$people->starting_pop}</td>
                        <td>Retired This Year (with Retirement Fund): </td>
                        <td>{$people->retired} ({$people->retired_with_fund})</td>
                    </tr>
                    <tr>
                        <td style='color: white;'>|</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Housing Happiness: </td>
                        <td>{$happ->housing_happiness}</td>
                        <th style='text-align: left'>New Citizens</th>
                        <td></td>
                        <td>Started School: </td>
                        <td>{$people->started_school}</td>
                    </tr>
                    <tr>
                        <td>Educational Happiness: </td>
                        <td>{$happ->education_happiness}</td>
                        <td>Births:</td>
                        <td>{$people->born}</td>
                        <td>Started College: </td>
                        <td>{$people->started_college}</td>
                    </tr>
                    <tr>
                        <td>Job Happiness: </td>
                        <td>{$happ->job_happiness}</td>
                        <td>Arrivals:</td>
                        <td>{$people->arrived}</td>
                        <td>Started University: </td>
                        <td>{$people->started_uni}</td>
                    </tr>
                    <tr>
                        <td>Recreational Happiness: </td>
                        <td>{$happ->recreation_happiness}</td>
                        <th style='text-align: left'>Citizens Gone</th>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Health Happiness: </td>
                        <td>{$happ->health_happiness}</td>
                        <td>Deaths:</td>
                        <td>{$people->die}</td>
                        <td>Graduated School: </td>
                        <td>{$people->school_grad}</td>
                    </tr>
                    <tr>
                        <td>Quality of Life Happiness: </td>
                        <td>{$happ->qol_happiness}</td>
                        <td>Leaving (due to homelessness):</td>
                        <td>{$leftTot} ({$people->homeless})</td>
                        <td>Graduated College: </td>
                        <td>{$people->college_grad}</td>
                    </tr>
                    <tr>
                        <td>$safetyName</td>
                        <td>$safetyNum</td>
                        <td></td>
                        <td></td>
                        <td>Graduated University: </td>
                        <td>{$people->uni_grad}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>Difference: </td>
                        <td>{$difference}</td>
                    </tr>
                    <tr>
                        <td style='color: white;'>|</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>New Population: </td>
                        <td>{$_SESSION['population']}</td>
                    </tr>
                
                </table>
            </div>

            </div>
        </div>";

}
else
{
    notLoggedIn();
}

?>



</body>

</html>