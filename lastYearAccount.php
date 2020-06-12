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
checkLogin('lastYearAccount');
if ($_SESSION['loggedIn'] == 'true')
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET accounts_seen = 1
                  WHERE LastYear.city_id_fk =:cityID
                              ";
    $stmt4 = $dbConn->prepare($SQLupdate);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID']));

    buildBanner();
    turnButtBlack('gameButt');

    lastYearHolder();
    turnButtBlack('accounts');


    $dbConn = getConnection();
    $SQLquery = "SELECT year
                 FROM City
                 WHERE City.id_pk =:cityID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $year = $stmt->fetchObject();
    $reportYear = $year->year - 1;
    if ($reportYear < 0)
    {
        $reportYear = 0;
    }

    if ($year->year == 0)
    {
        $other = 10000;
    }
    else
    {
        $other = 0;
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT tax_rate
                 FROM City
                 WHERE City.id_pk =:cityID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $taxRate = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT rent_collected, tax_collected, bus_revenue, rec_revenue, salaries_paid, retirement_funds_paid, building_costs,
                        new_citizen_xp, born, arrived, starting_coins
                 FROM LastYear
                 WHERE LastYear.city_id_fk =:cityID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $accounts = $stmt->fetchObject();

    $revenue = $accounts->bus_revenue + $accounts->rec_revenue;

    $incTot = $accounts->rent_collected + $accounts->tax_collected + $other + $revenue;
    $outTot = $accounts->salaries_paid + $accounts->retirement_funds_paid + $accounts->building_costs;
    $difference = $incTot - $outTot;

    if ($difference > 0 )
    {
        $difference = "+".$difference;
    }

    $newCitizens = $accounts->born + $accounts->arrived;

    echo    "<h1 style='font-family: \"Arial Black\", Arial, sans-serif; margin-left:  10px; margin-top: 0px; margin-bottom: 20px'>Economic Accounts Report for Year {$reportYear}</h1>
               <div class='lastYearCitizensTableHolder'>
                <table style='width: 100%'>
                    <tr>
                        <th style='text-align: left;text-decoration: underline'>Coins Incomings</th>
                        <td></td>
                        <th style='text-align: left;text-decoration: underline'>Coin Outgoings</th>
                        <td></td>
                        <th style='text-align: left;text-decoration: underline'>XP Incomings</th>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Rent Collected: </td>
                        <td>{$accounts->rent_collected} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                        <td>Salaries Paid: </td>
                        <td>{$accounts->salaries_paid} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                        <td>New Citizens ({$newCitizens}): </td>
                        <td>{$accounts->new_citizen_xp}XP</td>
                    </tr>
                    <tr>
                        <td>Tax Collected ({$taxRate->tax_rate}%): </td>
                        <td>{$accounts->tax_collected} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                        <td>Retirement Funds Paid: </td>
                        <td>{$accounts->retirement_funds_paid} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                    </tr>
                    <tr>
                        <td>Business Revenue: </td>
                        <td>{$revenue} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                        <td>Building Running Costs: </td>
                        <td>{$accounts->building_costs} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Other: </td>
                        <td>{$other} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                        <td>Other: </td>
                        <td>0 <img src='images/coins_icon.png' width='15px' height='15px'></td>
                        <th style='text-align: left;text-decoration: underline'>City Coins</th>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Starting Coin Balance: </td>
                        <td>{$accounts->starting_coins} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                    </tr>
                    <tr>
                        <th style='text-align: left;'>Total:</th>
                        <th style='text-align: left;'>{$incTot} <img src='images/coins_icon.png' width='15px' height='15px'></th>
                        <th style='text-align: left;'>Total:</th>
                        <th style='text-align: left;'>{$outTot} <img src='images/coins_icon.png' width='15px' height='15px'></th>
                        <td>Difference</td>
                        <td>{$difference} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                    </tr>
                    <tr>
                        <td style='color: white'>|</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>New Coin Balance: </td>
                        <td style='border-bottom: solid 1px black; text-align: right'>{$_SESSION['coins']} <img src='images/coins_icon.png' width='15px' height='15px'></td>
                    </tr>
                   
                
                </table>
            </div>

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