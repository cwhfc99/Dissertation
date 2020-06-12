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
checkLogin('newCityRecord');
if ($_SESSION['loggedIn'] == 'true')
{
    $dbConn = getConnection();
    $SQLquery = "SELECT City.id_pk
                 FROM City
                 WHERE City.player_id_fk =:userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $cityCheck = $stmt->fetchObject();
    if (!$cityCheck)
    {
        $cityName = filter_has_var(INPUT_GET, 'newCityInput')
            ? $_GET['newCityInput'] : null;

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO City(name, player_id_fk, coins, q_coins, xp, level, year, retirement_age, tax_rate)
                      VALUES (:cityName, :userID, :coins, :qCoins, :xp, :levelT, :yearT, :retirementAge, 20)
                      ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityName' => $cityName, ':userID' => $_SESSION['userID'], ':coins' => 10000, ':qCoins' => 2, ':xp' => 0, ':levelT' => 0, ':yearT' => 0, ':retirementAge' => 66));

        $dbConn = getConnection();
        $SQLquery = "SELECT City.id_pk
                     FROM City
                     WHERE City.player_id_fk =:userID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':userID' => $_SESSION['userID']));
        $cityID = $stmt->fetchObject();
        $_SESSION['cityID'] = $cityID->id_pk;

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO LastYear(city_id_fk, arrived)
                      VALUES (:cityID, 100)
                      ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityID' => $_SESSION['cityID']));


        $dbConn = getConnection();
        $SQLinsert = "UPDATE Account
                      SET last_log_in =:dateT
                      WHERE Account.id_pk =:userID
                     ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':dateT' => date('Y/m/d'), ':userID' => $_SESSION['userID']));

        //Initialise Game variables.

        for ($i = 0; $i < 100; $i++) //Adds 100 records to the citizen table.
        {
            $age = rand(0,100); //Sets a random age between 0-100 for the citizen.

            //Sets the wealth of the new citizens
            if ($i < 5)
            {
                $wealth = 0;
            }
            else if (($i > 4) && ($i <25))
            {
                $wealth = 1;
            }
            else if (($i > 24) && ($i <75))
            {
                $wealth = 2;
            }
            else if (($i > 74) && ($i <95))
            {
                $wealth = 3;
            }
            else
            {
                $wealth = 4;
            }

            //Sets the educational status of the citizen.
            $educRand = rand(1,20);
            if ($age > 18)
            {
                if ($age > 20)
                {
                    if ($age > 23)
                    {
                        //For over 23s
                        if ($educRand < 3)
                        {
                            $educStatus = 3;
                        }
                        else if (($educRand > 2)&& ($educRand < 6))
                        {
                            $educStatus = 2;
                        }
                        else if (($educRand > 5)&& ($educRand < 16))
                        {
                            $educStatus = 1;
                        }
                        else
                        {
                            $educStatus = 0;
                        }
                    }
                    else
                    {
                        //For over 20 - 23s
                        if ($educRand < 4)
                        {
                            $educStatus = 2;
                        }
                        else if (($educRand > 3)&& ($educRand < 13))
                        {
                            $educStatus = 1;
                        }
                        else
                        {
                            $educStatus = 0;
                        }
                    }
                }
                else
                {
                    //For over 18 - 20s
                    if ($educRand < 10)
                    {
                        $educStatus = 1;
                    }
                    else
                    {
                        $educStatus = 0;
                    }
                }
            }
            else
            {
                //for <=18s
                $educStatus = 0;
            }

            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO Citizen(city_id_fk, age, wealth_status, educational_status)
                          VALUES (:cityID, :age, :wealth, :educStatus)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $cityID->id_pk, ':age' => $age, ':wealth' => $wealth, ':educStatus' => $educStatus));
        }

        $name = "Block of Flats";
        $quality = 30;
        $rent = 500;
        $accomID = 1;

        for ($i = 0; $i < 2; $i++)
        {
            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO Accommodation(city_id_fk, name, accom_type_id_fk, quality, rent)
                          VALUES (:cityID, :nameT, :accomID, :quality, :rent)
                         ";
            $stmt = $dbConn->prepare($SQLinsert);
            $stmt->execute(array(':cityID' => $cityID->id_pk, ':nameT' => $name, ':accomID' => $accomID, ':quality' => $quality, ':rent' => $rent));

            $dbConn = getConnection();
            $SQLquery = "SELECT MAX(Accommodation.id_pk) as ID
                         FROM Accommodation
                         ";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array());
            $accomID2 = $stmt->fetchObject();

            for ($j = 0; $j < 50; $j++)
            {
                $dbConn = getConnection();
                $SQLinsert = "INSERT INTO Room(city_id_fk, accom_id_fk, inhabited)
                              VALUES (:cityID, :accomID, 0)
                             ";
                $stmt1 = $dbConn->prepare($SQLinsert);
                $stmt1->execute(array(':cityID' => $cityID->id_pk, ':accomID' => $accomID2->ID));
            }
        }
        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO EducationBuilding(city_id_fk, educ_type_id_fk)
                      VALUES (:cityID, :educID)
                     ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityID' => $cityID->id_pk, ':educID' => 1));

        $dbConn = getConnection();
        $SQLquery = "SELECT MAX(EducationBuilding.id_pk) as ID
                     FROM EducationBuilding
                     ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array());
        $educID = $stmt->fetchObject();

        for ($i = 0; $i < 20; $i++)
        {
            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO EducationSpace(city_id_fk, educ_id_fk, educ_type, taken, year)
                              VALUES (:cityID, :educID, 0, 0, 0)
                             ";
            $stmt1 = $dbConn->prepare($SQLinsert);
            $stmt1->execute(array(':cityID' => $cityID->id_pk, ':educID' => $educID->ID));
        }

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary)
                          VALUES (:cityID, :empID, 'a', 0, 2, 40, 1000)
                     ";
        $stmt1 = $dbConn->prepare($SQLinsert);
        $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $educID->ID));

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO ElectricityBuilding(city_id_fk, elect_type_id_fk)
                      VALUES (:cityID, :electID)
                     ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityID' => $cityID->id_pk, ':electID' => 1));

        $dbConn = getConnection();
        $SQLquery = "SELECT MAX(ElectricityBuilding.id_pk) as ID
                     FROM ElectricityBuilding
                     ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array());
        $electID = $stmt->fetchObject();

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary, util_type)
                          VALUES (:cityID, :empID, 'a', 1, 1, 30, 500,1)
                     ";
        $stmt1 = $dbConn->prepare($SQLinsert);
        $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $electID->ID));

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO WaterBuilding(city_id_fk, water_type_id_fk)
                      VALUES (:cityID, :waterID)
                     ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityID' => $cityID->id_pk, ':waterID' => 1));

        $dbConn = getConnection();
        $SQLquery = "SELECT MAX(WaterBuilding.id_pk) as ID
                     FROM WaterBuilding
                     ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array());
        $waterID = $stmt->fetchObject();

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary, util_type)
                          VALUES (:cityID, :empID, 'a', 1, 0, 30, 250,2)
                     ";
        $stmt1 = $dbConn->prepare($SQLinsert);
        $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $waterID->ID));

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO FoodBuilding(city_id_fk, food_type_id_fk)
                      VALUES (:cityID, :foodID)
                     ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityID' => $cityID->id_pk, ':foodID' => 1));

        $dbConn = getConnection();
        $SQLquery = "SELECT MAX(FoodBuilding.id_pk) as ID
                     FROM FoodBuilding
                     ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array());
        $foodID = $stmt->fetchObject();

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary, util_type)
                          VALUES (:cityID, :empID, 'a', 1, 0, 45, 250, 3)
                     ";
        $stmt1 = $dbConn->prepare($SQLinsert);
        $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $foodID->ID));

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary, util_type)
                          VALUES (:cityID, :empID, 'a', 1, 0, 45, 250, 3)
                     ";
        $stmt1 = $dbConn->prepare($SQLinsert);
        $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $foodID->ID));

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO RecreationBuilding(city_id_fk, rec_type_id_fk)
                      VALUES (:cityID, :recID)
                     ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityID' => $cityID->id_pk, ':recID' => 1));

        $dbConn = getConnection();
        $SQLquery = "SELECT MAX(RecreationBuilding.id_pk) as ID
                     FROM RecreationBuilding
                     ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array());
        $recID = $stmt->fetchObject();

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary)
                          VALUES (:cityID, :empID, 'a', 2, 0, 20, 250)
                     ";
        $stmt1 = $dbConn->prepare($SQLinsert);
        $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $recID->ID));

        $dbConn = getConnection();
        $SQLinsert = "INSERT INTO Business(city_id_fk, bus_type_id_fk, name, job_a_salary, job_b_salary, job_c_salary)
                      VALUES (:cityID, :busID, :nameT, 2000, 1000, 500)
                     ";
        $stmt = $dbConn->prepare($SQLinsert);
        $stmt->execute(array(':cityID' => $cityID->id_pk, ':busID' => 1, ':nameT' => 'Steel Manufacturer'));

        $dbConn = getConnection();
        $SQLquery = "SELECT MAX(Business.id_pk) as ID
                      FROM Business
                         ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array());
        $bussID = $stmt->fetchObject();

        $dbConn = getConnection();
        $SQLquery = "SELECT BusinessType.job_a_quantity, Business.job_a_salary, BusinessType.job_b_quantity, Business.job_b_salary, BusinessType.job_c_quantity, Business.job_c_salary
                     FROM BusinessType
                     LEFT JOIN Business ON BusinessType.id_pk = Business.bus_type_id_fk
                     WHERE Business.id_pk =:businessID
                         ";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':businessID' => $bussID->ID));
        $bussInfo = $stmt->fetchObject();

        for ($i = 0; $i < $bussInfo->job_a_quantity; $i++)
        {
            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary)
                          VALUES (:cityID, :empID, 'a', 3, 2, 20, 2000)
                     ";
            $stmt1 = $dbConn->prepare($SQLinsert);
            $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $bussID->ID));
        }

        for ($i = 0; $i < $bussInfo->job_b_quantity; $i++)
        {
            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary)
                          VALUES ( :cityID, :empID, 'b', 3, 1, 20, 1000)
                     ";
            $stmt1 = $dbConn->prepare($SQLinsert);
            $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $bussID->ID));
        }

        for ($i = 0; $i < $bussInfo->job_c_quantity; $i++)
        {
            $dbConn = getConnection();
            $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary)
                          VALUES ( :cityID, :empID, 'c', 3, 0, 20, 500)
                     ";
            $stmt1 = $dbConn->prepare($SQLinsert);
            $stmt1->execute(array(':cityID' => $cityID->id_pk, ':empID' => $bussID->ID));
        }
        educAssignment();
        checkRetired();
        canWork();
        jobAssingment();
        houseAssingment();
        utilHappiness();
        recHappiness();
        overallHappiness();

    }
    header('Location: menu.php');
}
else
{
    notLoggedIn();
}

?>

</body>

</html>