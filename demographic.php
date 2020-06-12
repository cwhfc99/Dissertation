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
checkLogin('demographic');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    citizensHolder();
    turnButtBlack('demographic');

    $dbConn = getConnection();
    $querySQL = "SELECT AVG(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID 
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $avgAge = $stmt->fetchObject();
    $avgAge = round($avgAge->age, 0);

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age <= 10
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $zeroToTen = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 11 AND age <= 20
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $tenTo20 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 21 AND age <= 30
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $twentyTo30 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 31 AND age <= 40
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $thirtyTo40 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 41 AND age <= 50
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $fourtyTo50 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 51 AND age <= 60
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $fiftyTo60 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 61 AND age <= 70
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $sixtyTo70 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 71 AND age <= 80
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $seventyTo80 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 81 AND age <= 90
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $eightyTo90 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 91 AND age <= 100
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $ninetyTo100 = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.age) as age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age >= 101
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $hundredPlus = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as wealth
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND wealth_status = 0
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $broke = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as wealth
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND wealth_status = 1
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $poor = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as wealth
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND wealth_status = 2
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $medium = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as wealth
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND wealth_status = 3
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $wealthy = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as wealth
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND wealth_status = 4
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $vWealthy = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as education
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND educational_status = 0
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $none = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as education
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND educational_status = 1
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $school = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as education
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND educational_status = 2
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $college = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as education
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND educational_status = 3
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $uni = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as job
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk > 0 AND Job.job_industry = 0
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $educationWork = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as job
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk > 0 AND Job.job_industry = 1
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $utilityWork = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as job
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk > 0 AND Job.job_industry = 2
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $recWork = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as job
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk > 0 AND Job.job_industry = 3
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $busWork = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as child
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND age < 16
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $child = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as retired
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND retired = 1
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $retired = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as education
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND in_education = 1
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $inEducation = $stmt->fetchObject();

    echo "<div class='myHousesMain'>
            <h1 style='font-family: \"Arial Black\", Arial, sans-serif; margin-top: -0'>Demographic</h1>
            <table class='citizenDemoTable'>
                <tr>
                   <th style='text-align: left'>Age</th>
                   <td></td>
                   <th style='text-align: left'>Wealth Status</th>
                   <td></td>
                   <th style='text-align: left'>Education Status</th>
                   <td></td>
                   <th style='text-align: left'>Job Status</th>
                   <td></td>
                </tr>
                <tr>
                   <td>Average Age:</td>
                   <td>{$avgAge}</td>
                   <td>Broke:</td>
                   <td>{$broke->wealth}</td>
                   <td>None Educated:</td>
                   <td>{$none->education}</td>
                   <td>Working In Education:</td>
                   <td>{$educationWork->job}</td>
                </tr>
                <tr>
                   <td>0 - 10:</td>
                   <td>{$zeroToTen->age}</td>
                   <td>Poor:</td>
                   <td>{$poor->wealth}</td>
                   <td>School Educated:</td>
                   <td>{$school->education}</td>
                   <td>Working In Utility:</td>
                   <td>{$utilityWork->job}</td>
                </tr>
                <tr>
                   <td>11 - 20:</td>
                   <td>{$tenTo20->age}</td>
                   <td>Medium:</td>
                   <td>{$medium->wealth}</td>
                   <td>College Educated:</td>
                   <td>{$college->education}</td>
                   <td>Working In Recreation:</td>
                   <td>{$recWork->job}</td>
                </tr>
                <tr>
                   <td>21 - 30:</td>
                   <td>{$twentyTo30->age}</td>
                   <td>Wealthy:</td>
                   <td>{$wealthy->wealth}</td>
                   <td>University Educated:</td>
                   <td>{$uni->education}</td>
                   <td>Working In Business:</td>
                   <td>{$busWork->job}</td>
                </tr>
                <tr>
                   <td>31 - 40:</td>
                   <td>{$thirtyTo40->age}</td>
                   <td>V. Wealthy:</td>
                   <td>{$vWealthy->wealth}</td>
                   <td></td>
                   <td></td>
                   <td>Child (u16):</td>
                   <td>{$child->child}</td>
                </tr>
                <tr>
                   <td>41 - 50:</td>
                   <td>{$fourtyTo50->age}</td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td>Retired:</td>
                   <td>{$retired->retired}</td>
                </tr>
                <tr>
                   <td>51 - 60:</td>
                   <td>{$fiftyTo60->age}</td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td>In Education:</td>
                   <td>{$inEducation->education}</td>
                </tr>
                <tr>
                   <td>61 - 70:</td>
                   <td>{$sixtyTo70->age}</td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                </tr>
                <tr>
                   <td>71 - 80:</td>
                   <td>{$seventyTo80->age}</td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                </tr>
                <tr>
                   <td>81 - 90:</td>
                   <td>{$eightyTo90->age}</td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                </tr>
                <tr>
                   <td>91 - 100:</td>
                   <td>{$ninetyTo100->age}</td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                </tr>
                <tr>
                   <td>100+:</td>
                   <td>{$hundredPlus->age}</td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                   <td></td>
                </tr>
    
    
    
    
    
    ";


    echo  "       
            </table>";


    echo "</div>
         </div>";

}
else
{
    notLoggedIn();
}

?>

<script type="text/javascript">

    function changeTaxRate()
    {
        displayLoading("#");
    }

</script>

</body>

</html>