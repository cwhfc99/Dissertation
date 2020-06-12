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
checkLogin('educationInfo');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    educHolder();
    turnButtBlack('housesInfo');


    echo "          <div class='myHousesMain'>";
    echo "<p>Your citizens require education in order to gain better jobs. There are 3 types of education that you can provide: School, College and University.</p>
          <p>For citizens to unreal in each educational stage they must have completed the stage before it and be old enough. Each stage takes the following time to complete:</p>
          <ul>
            <li>School - 12 years</li>
            <li>College - 2 years</li>
            <li>School - 4 years</li>
          </ul>
          <p>Citizens are automatically assigned to educational places when they become available, although are not guaranteed to finish the course as they may leave the city or die before they complete it. When you build a new educational building, citizens will not be assigned to educational places, within the building, until the next year.</p>
          <p>The education you provide your citizens effects their educational happiness. This is a combination of the educational status they currently have, the education available to them and the education available to the whole city. When citizens are in education they will not be assigned jobs.</p>

";


    echo "          </div>                
               </div>";

    echo  "  </div>";

}
else
{
    notLoggedIn();
}

?>

</body>

</html>