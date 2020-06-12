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
checkLogin('newCity');
if ($_SESSION['loggedIn'] == 'true')
{
    echo "<p>Welcome to the game <strong>{$_SESSION['username']}</strong>!</p>
          <p>The aim of the game is to grow the population in your city by as much as possible, by keeping your citizens as happy as possible.</p>
          <p>Lets start by giving your city a name:</p>
          <form id='newCityForm' onsubmit='displayLoading(\"#\")' action='newCityRecord.php' method='get' >
                <span><input type='text' name='newCityInput' id='newCityInput'></span><br>
                <p>We've given you a few items to make the first few days in your city that little bit easier:</p>
                <br>
                <p>100 Citizens - These people live in your city. They can work for your businesses, be educated and retire. You must keep them happy, however otherwise they will leave.</p>
                <p>10000 Coins - This is currency in your city. Use it to buy buildings, businesses and upgrades to make your city as good as possible.</p>
                <p>2 Q-Coins - This is your personal currencies. Use it to buy items at the Custom Store.</p>
                <p>2x Block of Flats - Your citizens need a place to live. These flats are big enough to house your first 100 citizens.</p>
                <p>1x Small School - Educate citizens will want to be as educated as possible. This school will educate 20 of your younger citizens.</p>
                <p>1x Electrical Substation - Your buildings need power. This substation will keep the lights on for 100 citizens.</p>
                <p>1x Small Water Treatment Centre - Water is an essential resource. This water treatment centre will provide water for 100 citizens.</p>
                <p>1x Small Butchers - Your citizens will need food to survive. This butchers will provide meat for 100 citizens.</p>
                <p>1x Small Park - Keep your citizens entertained. This small park is big enough for 10 citizens.</p>
                <p>1x Steel Manufacturer - Start businesses to make your city money. This steel manufacturer is your first enterprise.</p>
                <span><input type='submit' value='Lets Go!' class='newCitySubmit'></span>
            </form>
          ";



    echo
    "
    <script type=\"text/javascript\">
    window.addEventListener('load', function() {
        'use strict';


        validateInput();
        validateForm();
    });

    var bool = false;
    var input = document.getElementById('newCityInput');
    var butt = document.getElementById('newCityButt');

    input.addEventListener('input', validateInput);


    function validateInput()
    {
        input.style.borderWidth = '2px';
        if ((input.value) == '' || (input.value.length) > 25) //If the first name is empty or over 20 character.
        {
            bool = false;
            input.style.borderColor = 'red';
        }
        else
        {
            bool = true;
            input.style.borderColor = 'green';
        }
        validateForm();
    }

    function validateForm()
    {
        if (bool == false)
        {
            butt.disabled = true;
            butt.style.display = 'none';
        }
        else
        {
            butt.disabled = false;
            butt.style.display = 'block';

        }
    }

</script>
    ";
}
else
{
    notLoggedIn();
}

?>

</body>

</html>