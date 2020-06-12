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
checkLogin('removeAvatarItem');
if ($_SESSION['loggedIn'] == 'true')
{
    //Get Item ID
    $itemSelected = filter_has_var(INPUT_GET, 'avatarItem')
        ? $_GET['avatarItem'] : null;

    //Check ID is int
    if (filter_var($itemSelected, FILTER_VALIDATE_INT))
    {
        //Check User owns ID
        $dbConn = getConnection();
        $SQLquery = "SELECT AvatarItems.id_pk
                     FROM AvatarItems
                     WHERE AvatarItems.id_pk =:itemID AND AvatarItems.player_id_fk =:userID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':itemID' => $itemSelected, ':userID' => $_SESSION['userID']));
        $itemCheck = $stmt->fetchObject();

        if($itemCheck)
        {
            //Set equipped to 0
            $dbConn = getConnection();
            $SQLupdate = "UPDATE AvatarItems
                          SET equipped = 0
                          WHERE AvatarItems.player_id_fk =:userID AND AvatarItems.id_pk =:itemID
                              ";
            $stmt = $dbConn->prepare($SQLupdate);
            $stmt->execute(array(':userID' => $_SESSION['userID'], ':itemID' => $itemSelected));

            header('Location: myAvatar.php');
        }
        else
        {
            echo "A problem occurred and the avatar item was not removed."."<br>";
            echo "<a href='menu.php'><button>Back</button></a>";
        }
    }
    else
    {
        echo "A problem occurred and the avatar item was not removed."."<br>";
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