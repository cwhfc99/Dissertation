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
checkLogin('utilitiesInfo');
if ($_SESSION['loggedIn'] == 'true')
{
    buildBanner();
    turnButtBlack('gameButt');

    utilHolder();
    turnButtBlack('housesInfo');

    echo "          <div class='myHousesMain'>";

    echo "<p>Utilities are what keeps your city running. There are broken down into 5 categories: Electricity, Water, Food, Fire, Police and Healthcare. Each play a significant role in ensuring your citizens are happy living in your city.</p>
          <p>A citizens health happiness is based on the quality, variety and capacity of the health care you provide, as well as the age of the citizen.</p>
          <p>Their quality of life happiness is a combination of the tax rate in the city, the retirement age in the city, their individual retirement fund, the electricity and water provision in the city and the food provision and variety provided in the city.</p>
          <p>Once you reach level 15, you must ensure that your citizens are safe. The quality and size of the fire brigade and police force you supply, will determine the citizens safety happiness.</p>
          <p style='font-weight: bold'>Electricity</p>
          <p>Citizens require 10kW of electricity each. Ensure that you have enough power stations to keep the lights on.</p>
          <p style='font-weight: bold'>Water</p>
          <p>Citizens expect at least 15L of water each. Supply enough for the city by building water utilities</p>
          <p style='font-weight: bold'>Food</p>
          <p>Each citizen in your city will expect at least 25 units of food each. They will also expect a good variety of food to be supplied. Ensure they are happy buy building food buildings that supply a range of different products.</p>
          <p style='font-weight: bold'>Fire</p>
          <p>If you are level 15 or more, you will need to provide a fire brigade. Your fire brigade will be judge on it's quality and size compared to the population of your city. The quality of your fire brigade can be improved by purchasing upgrade, however the quality will drop by one point every five years, so ensure that you are maintaining your fire buildings. Your citizens will expect at least 0.05% of your city to be employed by the fire brigade.</p>
          <p style='font-weight: bold'>Police</p>
          <p>If you are level 15 or more, you will need to provide a police force. Your police force will be judge on it's quality and size compared to the population of your city. The quality of your police force can be improved by purchasing upgrade, however the quality will drop by one point every five years, so ensure that you are maintaining your police buildings. Your citizens will expect at least 0.18% of your city to be employed by the police force.</p>
          <p style='font-weight: bold'>Healthcare</p>
          <p>It is important that your healthcare system is up to scratch in order to stop your citizen feeling ill. Ensure that your healthcare system is big enough to handle at least 10% and it sof a good quality to increase the health happiness in your city. Again, the quality of your healthcare system will deteriorate 1 point every 5 years.</p>

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