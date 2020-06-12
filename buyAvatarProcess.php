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
checkLogin('buyAvatarProcess');
if ($_SESSION['loggedIn'] == 'true')
{
    $itemSelected = filter_has_var(INPUT_GET, 'avatarItem')
        ? $_GET['avatarItem'] : null;

    if (filter_var($itemSelected, FILTER_VALIDATE_INT))
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT AvatarType.id_pk
                     FROM AvatarType
                     WHERE AvatarType.id_pk =:itemID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':itemID' => $itemSelected));
        $itemCheck = $stmt->fetchObject();

        if($itemCheck)
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT AvatarItems.id_pk
                     FROM AvatarItems
                     WHERE AvatarItems.item_id_fk =:itemID AND AvatarItems.player_id_fk =:userID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':itemID' => $itemSelected, ':userID' => $_SESSION['userID']));
            $itemOwnCheck = $stmt->fetchObject();

            if (!($itemOwnCheck))
            {
                $dbConn = getConnection();
                $SQLquery = "SELECT AvatarType.price, AvatarType.type
                             FROM AvatarType
                             WHERE AvatarType.id_pk =:itemID";
                $stmt = $dbConn->prepare($SQLquery);
                $stmt->execute(array(':itemID' => $itemSelected));
                $itemInfo = $stmt->fetchObject();

                //Charge User
                $newCityQCoins = ($_SESSION['qCoin']-$itemInfo->price);
                $_SESSION['qCoin'] = $newCityQCoins;

                $dbConn = getConnection();
                $SQLupdate = "UPDATE City
                  SET q_coins = :coins
                  WHERE City.id_pk =:cityID
                              ";
                $stmt = $dbConn->prepare($SQLupdate);
                $stmt->execute(array(':coins' => $newCityQCoins, ':cityID' => $_SESSION['cityID']));

                //Remove previously equipped item
                $dbConn = getConnection();
                $SQLupdate = "UPDATE AvatarItems
                              SET equipped = 0
                              WHERE AvatarItems.player_id_fk =:userID AND AvatarItems.type =:type
                              ";
                $stmt = $dbConn->prepare($SQLupdate);
                $stmt->execute(array(':userID' => $_SESSION['userID'], ':type' => $itemInfo->type));

                //Give new item and equip it
                $dbConn = getConnection();
                $SQLinsert = "INSERT INTO AvatarItems(player_id_fk, item_id_fk, type, equipped)
                      VALUES (:userID, :itemID, :type, 1)
                      ";
                $stmt = $dbConn->prepare($SQLinsert);
                $stmt->execute(array(':userID' => $_SESSION['userID'], ':itemID' => $itemSelected, ':type' => $itemInfo->type));

                header('Location: buyAvatar.php');

            }
            else
            {
                echo "You already own this item."."<br>";
                echo "<a href='menu.php'><button>Back</button></a>";
            }
        }
        else
        {
            echo "A problem occurred and the avatar item was not bought."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }
    }
    else
    {
        echo "A problem occurred and the avatar item was not bought."."<br>";
        echo "<a href='menu.php'><button>Back</button></a>";
    }

}
else
{
    notLoggedIn();
}

?>

</body>

</html>