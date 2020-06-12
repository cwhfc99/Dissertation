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
checkLogin('recInfo');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    recHolder();
    turnButtBlack('housesInfo');

    echo "          <div class='myHousesMain'>";

    echo "<p>Recreational buildings provide your citizens with things to do in their free time, whilst also providing jobs to your citizens.</p>
          <p>Many recreational buildings will not generate any income for you city, although some will based on the population of your city. All recreational buildings have running costs and you will have to pay employees a salary.</p>
          <p>The recreation you provide in your city will give your citizens a recreational happiness. To attain a good recreational happiness, citizen will want a relatively big recreational capacity, for the size of the city, and plenty of diversity in the recreation supplied.</p>

";

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