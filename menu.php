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
    checkLogin('menu');
    if ($_SESSION['loggedIn'] == 'true')
    {
        buildBanner();
        if ($_SESSION['teacher'] == true)
        {
            turnButtBlack('gameButt');
        }
        echo "<div class='mainGameContainer'>
                <div class='gameMenuTopRow'>
                    <a href='citizensOverview.php'><div class='gameMenuButt'>
                        <div class='imageHolder'><img src='images/population_logo.png' width='150px' height='150px'></div>
                        <div class='title'><h1>Citizens</h1></div>
                        <div class='gameButtText'><p>View valuable information about the populaiton of your city that will help you make better decisions.</p></div>
                    </div></a>
                    <a href='myHouses.php'><div class='gameMenuButt'>
                        <div class='imageHolder'><img src='images/housing_page_icon.png' width='150px' height='150px'></div>
                        <div class='title'><h1>Housing</h1></div>
                        <div class='gameButtText'><p>View your city's current accommodation and buy new houses for you citizens to live in.</p></div>
                    </div></a>
                    <a href='myEducation.php'><div class='gameMenuButtRight'>
                        <div class='imageHolder'><img src='images/education_icon.png' width='150px' height='150px'></div>
                        <div class='title'><h1>Education</h1></div>
                        <div class='gameButtText'><p>Manage your city's educational institutions and open up new Schools, Colleges and Universities to educate your citizens.</p></div>
                    </div></a>
                </div>
                <div class='gameMenuBottomRow'>
                    <a href='myBusinesses.php'><div class='gameMenuButt'>
                        <div class='imageHolder'><img src='images/currency_icon.png' width='150px' height='150px'></div>
                        <div class='title'><h1>Business</h1></div>
                        <div class='gameButtText'><p>View your city's current businesses and buy new businesses to provide your citizens with jobs and generate money.</p></div>
                    </div>
                    <a href='myRecreation.php'><div class='gameMenuButt'>
                        <div class='imageHolder'><img src='images/recreation_icon.png' width='150px' height='150px'></div>
                        <div class='title'><h1>Recreation</h1></div>
                        <div class='gameButtText'><p>Keep your citizens entertained by providing them with recreational option. </p></div>
                    </div></a>
                    <a href='myUtilities.php'> <div class='gameMenuButtRight'>
                        <div class='imageHolder'><img src='images/water_icon.png' width='150px' height='150px'></div>
                        <div class='title'><h1>Utilities</h1></div>
                        <div class='gameButtText'><p>Manage all the utilities in order to keep your city working and your citizens safe.</p></div>
                    </div></a>
                </div>
              </div>";

    }
    else
    {
        notLoggedIn();
    }

?>

</body>

</html>