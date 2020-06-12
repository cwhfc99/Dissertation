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
checkLogin('buyEducation');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    educHolder();
    turnButtBlack('buyHouses');

    echo "          <div class='myHousesMain'>";

    $dbConn = getConnection();
    $SQLquery = "SELECT EducationType.id_pk, EducationType.name, EducationType.price, EducationType.running_cost,
                        EducationType.workers, EducationType.capacity, EducationType.level_unlocked, EducationType.xp_reward,
                        EducationType.q_coin_reward
                 FROM EducationType
                 ORDER BY EducationType.id_pk ASC 
                 ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while($rowObj = $stmt->fetchObject())
    {
        $imagePath = getEducImage($rowObj->id_pk);
        echo "
                                <a href='#' id='link{$rowObj->id_pk}' style='color: black; text-decoration: none;'><div class='individualHouseBuyer' id='{$rowObj->id_pk}'onmouseover='validateBus($rowObj->id_pk, $rowObj->level_unlocked, $rowObj->price) 'onmouseout='mouseOff($rowObj->id_pk)' '>
                                    <div class='houseImageHolder'><img style='margin-left: 9px; margin-top:11px;' width='70px' height='70px' src='$imagePath'></div>
                                    <table class='houseDetails'>
                                        <tr>
                                            <td class='houseDetailsName'>{$rowObj->name}</td>
                                            <td class='houseDetailsType'>Cost: {$rowObj->price} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td class='houseDetailsUpCost'>Upkeep Cost: {$rowObj->running_cost} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                                            <td class='houseDetailsUpgrades'>Teachers Required: {$rowObj->workers} </td>
                                        </tr>
                                        <tr>
                                            <td class='houseDetailsOccupancy'>Capacity: {$rowObj->capacity}</td>
                                            <td class='houseDetailsType'>Level Unlocked: {$rowObj->level_unlocked}</td>
                                            <td class='houseDetailsUpCost'>XP: {$rowObj->xp_reward}</td>";
        if (!($rowObj->q_coin_reward == 0))
        {
            echo "<td class='houseDetailsUpgrades'>Q-Coins: {$rowObj->q_coin_reward}</td>";
        }
        else
        {
            echo "<td class='houseDetailsUpgrades'></td>";
        }

        echo "
                                        </tr>
                                    </table>
                                </div></a>
                             ";
    }


    echo "          </div>                
               </div>";

    echo  "  </div>";

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

    function validateBus(accomType, accomLevel, accomPrice)
    {
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var level = document.getElementById('levelMeta').getAttribute('level');
        var container = document.getElementById(accomType);
        const temp1 = 'link';
        const temp = temp1.concat(accomType);
        var link = document.getElementById(temp);

        coins = parseInt(coins);
        level = parseInt(level);

        var canBuy = false;

        if (coins >= accomPrice)
        {
            if (level >= accomLevel)
            {
                canBuy = true;
            }
        }

        if (canBuy == false)
        {
            link.href='#';
            container.style.backgroundColor = '#db4b12';
        }
        else
        {
            var text = 'buyEducationProcess.php?educToBuy=';
            var textVar = text.concat(accomType);
            link.href = textVar;
            container.style.backgroundColor = '#55e67b';
        }
    }

    function mouseOff(accomType)
    {
        var container = document.getElementById(accomType);
        container.style.backgroundColor = '#ffffff';

    }


</script>

</body>

</html>