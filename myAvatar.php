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
checkLogin('myAvatar');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    echo "<div class='mainGameContainer'>
            <div class='myAvatarTop'>
                <h1 style='font-family: \"Arial Black\", Arial, sans-serif; margin-top: 0px; font-size: 15px;'>Equipped Items</h1>";

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.image_file_name, AvatarType.name, AvatarType.description, AvatarItems.type
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID
                 ORDER BY AvatarType.type ASC";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    echo "<table><tr>";

    while($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->type == 4)
        {
            echo "<td>
                <div class='avatarItemHolder'>
                    <div class='avatarItemImageHolder'><img src='images/{$rowObj->image_file_name}.png' style='margin: 0px' width='85px' height='85px'></div>
                    <h1 class='avatarItemName'>{$rowObj->name}</h1>
                    <p style='font-size: 12px; margin-left: 90px; margin-top: -65px;'> </p>
                    <p style='font-size: 12px; margin-left: 90px;'>{$rowObj->description}.</p>
                    <div class='removeAvatarItemButt' onclick='removeItem({$rowObj->id_pk})'>Remove</div>
                </div>
              </td>";
        }
        else
        {
            echo "<td>
                <div class='avatarItemHolder'>
                    <div class='avatarItemImageHolder'><img src='images/{$rowObj->image_file_name}.png' style='margin: 5px' width='65px' height='65px'></div>
                    <h1 class='avatarItemName'>{$rowObj->name}</h1>
                    <p style='font-size: 12px; margin-left: 90px; margin-top: -65px;'> </p>
                    <p style='font-size: 12px; margin-left: 90px;'>{$rowObj->description}.</p>
                    <div class='removeAvatarItemButt' onclick='removeItem({$rowObj->id_pk})'>Remove</div>
                </div>
              </td>";
        }
    }

    echo"    </tr></table>
            </div>
            <div class='myAvatarBottom'>
                <h1 style='font-family: \"Arial Black\", Arial, sans-serif; margin-top: 0px; font-size: 15px;'>Other Items</h1>";

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.image_file_name, AvatarType.name, AvatarType.description, AvatarItems.type
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 0 AND AvatarItems.player_id_fk = :userID
                 ORDER BY AvatarType.type ASC, AvatarType.price DESC";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    echo "<table><tr>";

    $items = false;
    while($rowObj = $stmt->fetchObject())
    {
        $items = true;
        if ($rowObj->type == 4)
        {
            echo "<td>
                <div class='avatarItemHolder'>
                    <div class='avatarItemImageHolder'><img src='images/{$rowObj->image_file_name}.png' style='margin: 0px' width='85px' height='85px'></div>
                    <h1 class='avatarItemName'>{$rowObj->name}</h1>
                    <p style='font-size: 12px; margin-left: 90px; margin-top: -65px;'> </p>
                    <p style='font-size: 12px; margin-left: 90px;'>{$rowObj->description}.</p>
                    <div class='equipAvatarItemButt' onclick='equipItem({$rowObj->id_pk})'>Equip</div>
                </div>
              </td>";
        }
        else
        {
            echo "<td>
                <div class='avatarItemHolder'>
                    <div class='avatarItemImageHolder'><img src='images/{$rowObj->image_file_name}.png' style='margin: 5px' width='65px' height='65px'></div>
                    <h1 class='avatarItemName'>{$rowObj->name}</h1>
                    <p style='font-size: 12px; margin-left: 90px; margin-top: -65px;'> </p>
                    <p style='font-size: 12px; margin-left: 90px;'>{$rowObj->description}.</p>
                    <div class='equipAvatarItemButt' onclick='equipItem({$rowObj->id_pk})'>Equip</div>
                </div>
              </td>";
        }

    }

    if ($items == false)
    {
        echo "<td style='height: 130px; width: 1200px;text-align: center'>Click the 'Buy Items' button below to purchase items for your avatar.</td>";
    }

    echo "  </tr></table></div>
            <a href='buyAvatar.php' style='color: black; text-decoration: none'><div class='buyAvatarButt'>Buy Items</div></a>

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

    function removeItem(itemID)
    {
            const text = 'removeAvatarItem.php?avatarItem=';
            const holder = text.concat(itemID);
            displayLoading(holder);

    }

    function equipItem(itemID)
    {
        const text = 'equipAvatarItem.php?avatarItem=';
        const holder = text.concat(itemID);
        displayLoading(holder);

    }

</script>

</body>

</html>