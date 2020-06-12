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
checkLogin('leaderboard');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');
    turnButtBlack('leaderboardButt');

    $classSelected = filter_has_var(INPUT_GET, 'classSelected')
        ? $_GET['classSelected'] : null;

    $dbConn = getConnection();
    $SQLquery = "SELECT Class.id_pk
                 FROM Account
                 LEFT JOIN AccountClasses ON Account.id_pk = AccountClasses.user_id_fk
                 JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                 WHERE Account.id_pk =:userID AND Class.id_pk =:class";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array('userID' => $_SESSION['userID'], ':class' => $classSelected));
    $classCheck = $stmt->fetchObject();

    if (!($classCheck))
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT Class.id_pk
                 FROM Account
                 LEFT JOIN AccountClasses ON Account.id_pk = AccountClasses.user_id_fk
                 JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                 WHERE Account.id_pk =:userID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('userID' => $_SESSION['userID']));
        $classHoldVar = $stmt->fetchObject();
        if ($classHoldVar)
        {
            $classSelected = $classHoldVar->id_pk;
        }
        else
        {
            $classSelected = null;
        }
    }

    echo "
    
    <div class='mainGameContainer'>
        <div class='leaderboard'>
            <div class='leaderboardClassSelector'>
                <div class='leaderboardSelectContainer'>
                    <form id='leaderboardClassSelector' action={$_SESSION['redirect']}.php method='get'>
                        <select id='classSelect' name='classSelected' onchange='reloadFunc()'>";

    $dbConn = getConnection();
    $SQLquery = "SELECT Class.id_pk
                 FROM Account
                 LEFT JOIN AccountClasses ON Account.id_pk = AccountClasses.user_id_fk
                 JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                 WHERE Account.id_pk =:userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array('userID' => $_SESSION['userID']));
    if ($stmt->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT Class.id_pk, Class.name
                 FROM Account
                 LEFT JOIN AccountClasses ON Account.id_pk = AccountClasses.user_id_fk
                 JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                 WHERE Account.id_pk =:userID";
        $stmt2 = $dbConn->prepare($SQLquery);
        $stmt2->execute(array('userID' => $_SESSION['userID']));


        while ($rowObj = $stmt2->fetchObject())
        {
            if ($classSelected == $rowObj->id_pk)
            {
                $temp = "<option selected value='" .$rowObj->id_pk. "'>";
            }
            else
            {
                $temp = "<option value='" .$rowObj->id_pk. "'>";
            }

            echo $temp;
            echo $rowObj->name;
            echo "</option>";
        }
    }
    else
    {
        echo "<option value=null>You need to be a member of a class to access this feature.</option>";
    }

    echo "        </select>
                </form>  
                </div>
            </div>";



    echo" <div class='leaderboardHolder'>
              <table style='width: 95%; margin-left: 100px; margin-top: 20px'>
                <tr>
                    <th style='font-family: \"Arial Black\", Arial, sans-serif; text-align: left'>Username</th>
                    <th style='font-family: \"Arial Black\", Arial, sans-serif; text-align: left'>Name</th>
                    <th style='font-family: \"Arial Black\", Arial, sans-serif; text-align: left'>Level</th>
                    <th style='font-family: \"Arial Black\", Arial, sans-serif; text-align: left'>XP</th>
                    <th style='font-family: \"Arial Black\", Arial, sans-serif; text-align: left'>Coins</th>
                    <th style='font-family: \"Arial Black\", Arial, sans-serif; text-align: left'>Population</th>
                </tr>
                <tr>
                    <td style='color: white;'>|</td>                
                </tr>";

    $dbConn = getConnection();
    $SQLquery = "SELECT City.name, City.id_pk, Account.username, Account.first_name, Account.last_name, City.level, City.xp, City.coins 
                 FROM City
                 LEFT JOIN Account ON City.player_id_fk = Account.id_pk
                 LEFT JOIN AccountClasses ON Account.id_pk = AccountClasses.user_id_fk
                 LEFT JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                 WHERE Class.id_pk =:classID
                 ORDER BY City.level DESC, City.xp DESC, City.coins DESC";
    $stmt2 = $dbConn->prepare($SQLquery);
    $stmt2->execute(array('classID' => $classSelected));

    while ($rowObj = $stmt2->fetchObject())
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT COUNT(Citizen.id_pk) AS citizenCounter
                     FROM Citizen
                     WHERE Citizen.city_id_fk =:cityID";
        $stmt3 = $dbConn->prepare($SQLquery);
        $stmt3->execute(array('cityID' => $rowObj->id_pk));
        $citizenCounter = $stmt3->fetchObject();

        echo "<tr>
                    <td>{$rowObj->username}</td>
                    <td>{$rowObj->first_name} {$rowObj->last_name}</td>
                    <td>{$rowObj->level}</td>
                    <td>{$rowObj->xp} XP</td>
                    <td>{$rowObj->coins} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                    <td>{$citizenCounter->citizenCounter} <img src='images/population_logo.png' width='15px' height='15px'></td>
                </tr>";
    }


    echo"     </table> 
            </div> 
        </div>
    </div>";




}
else
{
    notLoggedIn();
}

?>


<script type='text/javascript'>
    window.addEventListener('load', function() {
        'use strict';

    });


    function reloadFunc()
    {
        var classSelected = document.getElementById('classSelect').value;

        const text1 = '?classSelected='
        const page = 'leaderboard.php';

        var holder = page.concat(text1, classSelected);
        document.location = holder;
    }

</script>

</body>

</html>