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
    <meta id='coinsMeta' coins='<?php echo $_SESSION['qCoin']?>'>
    <meta id='levelMeta' level='<?php echo $_SESSION['level']?>'>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>

<?php

require_once('functions.php');
checkLogin('buyAvatar');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    echo "<div class='mainGameContainer'>
            <div class='avatarItemsTableHolder'>
            <table>
                
                    <tr>";

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarType.id_pk, AvatarType.image_file_name, AvatarType.name, AvatarType.description, 
                        AvatarType.level_unlocked, AvatarType.price, AvatarType.type
                 FROM AvatarType
                 WHERE AvatarType.id_pk NOT IN(SELECT AvatarItems.item_id_fk FROM AvatarItems WHERE AvatarItems.player_id_fk =:userID)";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $loopType = -1;
    $i = 0;
    while($rowObj = $stmt->fetchObject())
    {

        if (($rowObj->type > $loopType)&&(!($loopType == 4)))
        {
            $loopType = $rowObj->type;
            echo "</tr><tr>";

            if ($loopType == 0)
            {
                $title = 'Hats';
            }
            else if ($loopType == 1)
            {
                $title = 'T-Shirts';
            }
            else if ($loopType == 2)
            {
                $title = 'Trousers';
            }
            else if ($loopType == 3)
            {
                $title = 'Accessories';
            }
            else if ($loopType == 4)
            {
                $title = 'Backgrounds';
            }
            echo "<tr><th style='text-align: left'>{$title}</th></tr>";
        }

        if ($loopType == 4)
        {
            echo "<td>
                <div class='avatarItemHolder'>
                    <div class='avatarItemImageHolder'><img src='images/{$rowObj->image_file_name}.png' style='margin: 0px' width='85px' height='85px'></div>
                    <h1 class='avatarItemName'>{$rowObj->name}</h1>
                    <p style='font-size: 12px; margin-left: 90px; margin-top: -65px;'>Level Unlocked: {$rowObj->level_unlocked}.</p>
                    <p style='font-size: 12px; margin-left: 90px;'>{$rowObj->description}.</p>
                    <div class='buyAvatarItemButt' onclick='itemValidateLink({$rowObj->id_pk})' onmouseleave='itemMouseOff({$rowObj->id_pk})' onmouseover='itemValidate({$rowObj->id_pk})' id='{$rowObj->id_pk}' cost='{$rowObj->price}' levelRequired='{$rowObj->level_unlocked}'>{$rowObj->price} <img src='images/qCoins_icon.png' style='margin-top: 3px;' width='20px' height='20px' ></div>
                </div>
              </td>";
        }
        else
        {
            echo "<td>
                <div class='avatarItemHolder'>
                    <div class='avatarItemImageHolder'><img src='images/{$rowObj->image_file_name}.png' style='margin: 5px' width='65px' height='65px'></div>
                    <h1 class='avatarItemName'>{$rowObj->name}</h1>
                    <p style='font-size: 12px; margin-left: 90px; margin-top: -65px;'>Level Unlocked: {$rowObj->level_unlocked}.</p>
                    <p style='font-size: 12px; margin-left: 90px;'>{$rowObj->description}.</p>
                    <div class='buyAvatarItemButt' onclick='itemValidateLink({$rowObj->id_pk})' onmouseleave='itemMouseOff({$rowObj->id_pk})' onmouseover='itemValidate({$rowObj->id_pk})' id='{$rowObj->id_pk}' cost='{$rowObj->price}' levelRequired='{$rowObj->level_unlocked}'>{$rowObj->price} <img src='images/qCoins_icon.png' style='margin-top: 3px;' width='20px' height='20px' ></div>
                </div>
              </td>";
        }


        $i++;
    }

    echo "</tr>";
            
    echo  "        </table> 
                </div>
            <a href='myAvatar.php' style='color: black; text-decoration: none'><div class='buyAvatarButt'>My Items</div></a>
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

    function itemValidate(itemID)
    {
        const itemButt = document.getElementById(itemID);
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var level = document.getElementById('levelMeta').getAttribute('level');
        var cost = itemButt.getAttribute('cost');
        var levelUnlocked = itemButt.getAttribute('levelRequired');

        level = parseInt(level);
        coins = parseInt(coins);
        cost = parseInt(cost);
        levelUnlocked = parseInt(levelUnlocked);


        if ((level >= levelUnlocked) && (coins >= cost))
        {
            itemButt.style.backgroundColor = '#55e67b';
        }
        else
        {
            itemButt.style.backgroundColor = '#db4b12';
        }

    }

    function itemMouseOff(itemID)
    {
        const itemButt = document.getElementById(itemID);
        itemButt.style.backgroundColor = '#bebebe';
    }

    function itemValidateLink(itemID)
    {
        const itemButt = document.getElementById(itemID);
        var coins = document.getElementById('coinsMeta').getAttribute('coins');
        var level = document.getElementById('levelMeta').getAttribute('level');
        var cost = itemButt.getAttribute('cost');
        var levelUnlocked = itemButt.getAttribute('levelRequired');

        level = parseInt(level);
        coins = parseInt(coins);
        cost = parseInt(cost);
        levelUnlocked = parseInt(levelUnlocked);


        if ((level >= levelUnlocked) && (coins >= cost))
        {
            const text = 'buyAvatarProcess.php?avatarItem=';
            const holder = text.concat(itemID);
            displayLoading(holder);
        }

    }



</script>

</body>

</html>