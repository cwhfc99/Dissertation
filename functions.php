<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 17:00
 */


function set_session($key, $value) //Sets the session.
{
    $_SESSION[$key] = $value;
    return true;
}

function log_error($e) //Logs errors to a file.
{
    $fileHandle = fopen("error_log_file.log", "ab");
    $errorDate = date('D M j G:i:s T Y');
    $errorMessage = $e->getMessage();
    fwrite($fileHandle, "$errorDate, $errorMessage /n");
    fclose($fileHandle);
}

function loadScreen()
{
    echo "<div class='loadScreen' id='loadScreen'><div style='margin-top: 200px; text-align: center; width:100%; opacity: 1;' ><img src='images/loading_screen_icon.png' width='200px' height='200px'><h1 style='font-family: \"Arial Black\", Arial, sans-serif'>Loading</h1></div></div>";

    echo "
    <script type='text/javascript'>
        function displayLoading(direction)
        {
            const loadDiv = document.getElementById('loadScreen');
            loadDiv.style.display = 'flex';
            
            document.location = direction;
        }
    </script>
        
    
    ";
}

function checkLogin($redirect) //Checks in the user is logged in or not.
{
    if (!isset($_SESSION['loggedIn']))
    {
        $_SESSION['loggedIn'] = false;
    }

    if (!isset($_SESSION['level']))
    {
        $_SESSION['level'] = 0;
    }

    if (!isset($_SESSION['cityID']))
    {
        if ($_SESSION['loggedIn'] == true)
        {
            $dbConn = getConnection();
            $SQLquery = "SELECT City.id_pk
                       FROM City
                       WHERE City.player_id_fk = :userID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':userID' => $_SESSION['userID']));
            $cityID = $stmt->fetchObject();

            $_SESSION['cityID'] = $cityID->id_pk;
        }
    }

    loadScreen();

    $_SESSION['redirect'] = $redirect;
    if ($_SESSION['loggedIn'] == false)
    {
        buildLoggedOut();
    }
    else
    {
        buildLoggedIN();
    }

}

function buildLoggedOut() //Builds the form to log in, when the user is logged out.
{
    echo
    "
        <div class='lOtop'>
            <div class='formHolder'>
                <form id='loginForm' action='loginProcess.php' method='get' > 
                            <span>Username: <input class='formInput' type='text' name='username'></span>
                            <span>Password: <input class='formInput' type='password' name='password' ></span>
                            <input type='submit' name='submit' value='Go'>
                </form>
            </div>
        </div>
    ";

}

function buildLoggedIN()
{
    echo
    "
        <div class='lOtop'>
            <div class='formHolder'>
                <form id='logOutForm' action='logOutProcess.php' method='post' > 
                            <span>You are currently logged in as </span>";
                                echo $_SESSION['username']; echo ".";
                            echo "<input class='logOutButt' type='submit' name='submit' value='Logout'>
                </form>
            </div>
        </div>
    ";
}

function checkRevIncreases()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 2)
        {
            $increase++;
        }
        else if ($rowObj->itemType == 8)
        {
                $increase++;
        }
        else if ($rowObj->itemType == 15)
        {
            $increase++;
        }
        else if ($rowObj->itemType == 19)
        {
            $increase = $increase + 3;
        }
        else if ($rowObj->itemType == 26)
        {
            $increase = $increase + 2;
        }
    }
    return $increase;
}


function dailyStreak($streakLevel)
{
    $streakLevel = $streakLevel - 1;
    if ($streakLevel == 0)
    {
        $coins = 500;
        $xp = 250;
    }
    else
    {
        $coins = round(500*(1.5**$streakLevel),0);
        $xp = round(250*(1.5**$streakLevel),0);
    }

    //Create Reward Record
    $dbConn = getConnection();
    $insertSQL = "INSERT INTO Rewards(teacher_id_fk, player_id_fk, message, date, xp, coins)
                                      VALUES (:userID, :userID2, :message, :dateT, :xp, :coins)
                                      ";
    $stmt = $dbConn->prepare($insertSQL);
    $stmt->execute(array(':userID' => $_SESSION['userID'], ':userID2' => $_SESSION['userID'], ':message' => 'You have achieved a daily streak of'.(($streakLevel+1)*5)." days. Here's your reward.", ':dateT' => date('Y/m/d'), ':xp' => $xp, ':coins' => $coins));

    //Reward Player
    rewardXP($xp);
    rewardCoins($coins);

    //Get the reward ID created
    $dbConn = getConnection();
    $SQLquery = "SELECT id_pk
                     FROM Rewards
                     ORDER BY id_pk DESC";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute();
    $rewardRecord = $stmt->fetchObject();
    $newRewardID = $rewardRecord->id_pk;

    //Send Notification
    $dbConn = getConnection();
    $insertSQL = "INSERT INTO Notification(from_id_fk, to_id_fk, message, date, type, reward_id_fk)
                                      VALUES (:cityName, :userID, :message, :dateT, :type, :rewardID)";
    $stmt = $dbConn->prepare($insertSQL);
    $stmt->execute(array(':cityName' => $_SESSION['cityName'], ':userID' => $_SESSION['userID'], ':message' => 'You have achieved a daily streak of '.(($streakLevel+1)*5)." days. Here's your reward.", ':dateT' => date('Y/m/d'), ':type' => 'Daily Streak', ':rewardID' => $newRewardID));

}

function buildBanner()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT City.id_pk, City.name, City.year, City.coins, City.q_coins, City.xp, City.level
                       FROM City
                       WHERE City.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $cityDetails = $stmt->fetchObject();
    $_SESSION['cityID'] = $cityDetails->id_pk;
    $_SESSION['cityName'] = $cityDetails->name;
    $_SESSION['coins'] = $cityDetails->coins;
    $_SESSION['qCoin'] = $cityDetails->q_coins;
    $_SESSION['xp'] = $cityDetails->xp;
    calcLevel();
    $dbConn = getConnection();
    $SQLquery = "SELECT City.level
                 FROM City
                 WHERE City.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $levelDetails = $stmt->fetchObject();
    $_SESSION['level'] = $levelDetails->level;

    $daysPast = getDifferenceInDates();
    for ($i = 1; $i <= $daysPast; $i++)
    {
        newYear();
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT City.id_pk, City.name, City.year, City.coins, City.q_coins, City.xp, City.level
                       FROM City
                       WHERE City.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $cityDetails = $stmt->fetchObject();
    $_SESSION['cityID'] = $cityDetails->id_pk;
    $_SESSION['cityName'] = $cityDetails->name;
    $_SESSION['coins'] = $cityDetails->coins;
    $_SESSION['qCoin'] = $cityDetails->q_coins;
    $_SESSION['xp'] = $cityDetails->xp;
    calcLevel();
    $dbConn = getConnection();
    $SQLquery = "SELECT City.level
                 FROM City
                 WHERE City.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $levelDetails = $stmt->fetchObject();
    $_SESSION['level'] = $levelDetails->level;


    $dbConn = getConnection();
    $SQLquery = "SELECT daily_log_in
                 FROM City
                 WHERE City.player_id_fk =:userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $dailyLogIn = $stmt->fetchObject();

    if ($dailyLogIn->daily_log_in == 0)
    {
        // SET Daily Log In to true
        $dbConn = getConnection();
        $SQLupdate = "UPDATE City
                      SET City.daily_log_in = 1
                      WHERE City.player_id_fk =:userID
                  ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':userID' => $_SESSION['userID']));

        //Create Reward Record
        $dbConn = getConnection();
        $insertSQL = "INSERT INTO Rewards(teacher_id_fk, player_id_fk, message, date, xp)
                                      VALUES (:userID, :userID2, :message, :dateT, 100)
                                      ";
        $stmt = $dbConn->prepare($insertSQL);
        $stmt->execute(array(':userID' => $_SESSION['userID'], ':userID2' => $_SESSION['userID'], ':message' => 'Here is your daily gift.', ':dateT' => date('Y/m/d')));

        //Reward Player
        rewardXP(100);


        //Get the reward ID created
        $dbConn = getConnection();
        $SQLquery = "SELECT id_pk
                     FROM Rewards
                     ORDER BY id_pk DESC";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute();
        $rewardRecord = $stmt->fetchObject();
        $newRewardID = $rewardRecord->id_pk;

        //Send Notification
        $dbConn = getConnection();
        $insertSQL = "INSERT INTO Notification(from_id_fk, to_id_fk, message, date, type, reward_id_fk)
                                      VALUES (:cityName, :userID, :message, :dateT, :type, :rewardID)";
        $stmt = $dbConn->prepare($insertSQL);
        $stmt->execute(array(':cityName' => $_SESSION['cityName'], ':userID' => $_SESSION['userID'], ':message' => 'Here is your daily gift.', ':dateT' => date('Y/m/d'), ':type' => 'Daily Gift', ':rewardID' => $newRewardID));

        if ($daysPast > 1)
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE City
                          SET City.daily_streak = 1
                          WHERE City.player_id_fk =:userID
                         ";
            $stmt = $dbConn->prepare($SQLupdate);
            $stmt->execute(array(':userID' => $_SESSION['userID']));
        }
        else
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE City
                          SET City.daily_streak = daily_streak + 1
                          WHERE City.player_id_fk =:userID
                         ";
            $stmt = $dbConn->prepare($SQLupdate);
            $stmt->execute(array(':userID' => $_SESSION['userID']));

            $dbConn = getConnection();
            $SQLquery = "SELECT daily_streak
                         FROM City
                         WHERE City.id_pk =:cityID";
            $stmt = $dbConn->prepare($SQLquery);
            $stmt->execute(array(':cityID' => $_SESSION['cityID']));
            $dailyStreakCount = $stmt->fetchObject();

            $testStreakIs5 = $dailyStreakCount->daily_streak/5;
            if (is_int($testStreakIs5))
            {
                dailyStreak($testStreakIs5);
            }
        }

    }

    $dbConn = getConnection();
    $SQLquery = "SELECT City.id_pk, City.name, City.year, City.coins, City.q_coins, City.xp, City.level
                       FROM City
                       WHERE City.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $cityDetails = $stmt->fetchObject();
    $_SESSION['cityID'] = $cityDetails->id_pk;
    $_SESSION['cityName'] = $cityDetails->name;
    $_SESSION['coins'] = $cityDetails->coins;
    $_SESSION['qCoin'] = $cityDetails->q_coins;
    $_SESSION['xp'] = $cityDetails->xp;
    calcLevel();
    $dbConn = getConnection();
    $SQLquery = "SELECT City.level
                 FROM City
                 WHERE City.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $levelDetails = $stmt->fetchObject();
    $_SESSION['level'] = $cityDetails->level;

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Account
                  SET Account.last_log_in =:dateT
                  WHERE Account.id_pk =:userID
                  ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':userID' => $_SESSION['userID'], ':dateT' => date('Y/m/d')));
    try
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT first_name, last_name
                 FROM Account
                 WHERE Account.id_pk =:userID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':userID' => $_SESSION['userID']));

        $user = $stmt->fetchObject();

        if ($_SESSION['teacher'] == true)
        {
            try
            {
                $dbConn = getConnection();
                $SQLquery = "SELECT title
                 FROM Teacher
                 JOIN Account ON Account.id_pk = Teacher.account_id_fk
                 WHERE Account.id_pk =:userID";
                $stmt = $dbConn->prepare($SQLquery);
                $stmt->execute(array(':userID' => $_SESSION['userID']));
                $title  = $stmt->fetchObject();
            }
            catch (Exception $e)
            {
                echo "An error occurred.";
            }
        }
    }
    catch (Exception $e)
    {
        echo "An error occurred.";
    }

    if ($_SESSION['teacher'] == true)
    {
        echo
        "
            <ul class='gameTeacherContainer'>
                <li class='gameTeacherButt' id='gameButt'><a href=\"menu.php\">Game</a></li>
                <li class='gameTeacherButt' id='teacherButt'><a href=\"classes.php\">Teacher</a></li>
                <li class='testNextYearButt' id='teacherButt' onclick='displayLoading(\"nextYearProcess.php\")'>Test: New Year</li>
            </ul>
        ";
    }
    else
    {
        echo
        "
            <ul class='gameTeacherContainer'>
                <li class='testNextYearButt' id='teacherButt' onclick='displayLoading(\"nextYearProcess.php\")'>Test: New Year</li>
            </ul>
        ";
    }
    echo
    "
        <div class='banner'>
            <a href='myAvatar.php' style='text-decoration: none; color: black'><div class='avatarContainer'>
                    <div class='avatarContainerInner'>";

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarType.image_file_name
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID AND AvatarItems.type = 4";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $background = $stmt->fetchObject();

    if ($background)
    {
        echo "<img src='images/{$background->image_file_name}.png' width='150px' height='150px' >";
    }
    else
    {
        echo "<img src='images/white_background_icon.png' width='150px' height='150px' >";
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarType.image_file_name
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID AND AvatarItems.type = 0";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $hat = $stmt->fetchObject();

    if ($hat)
    {
        echo "<img src='images/{$hat->image_file_name}.png' width='60px' height='60px' style='position: fixed; margin-left: 2.5px; margin-top: -150px'>";
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarType.image_file_name
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID AND AvatarItems.type = 1";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $tshirt = $stmt->fetchObject();

    if ($tshirt)
    {
        echo "<img src='images/{$tshirt->image_file_name}.png' width='60px' height='60px' style='position: fixed; margin-left: 65px; margin-top: -150px'>";
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarType.image_file_name
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID AND AvatarItems.type = 2";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $trousers = $stmt->fetchObject();

    if ($trousers)
    {
        echo "<img src='images/{$trousers->image_file_name}.png' width='60px' height='60px' style='position: fixed; margin-left: 65px; margin-top: -90px'>";
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarType.image_file_name
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID AND AvatarItems.type = 3";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $accessory = $stmt->fetchObject();

    if ($accessory)
    {
        echo "<img src='images/{$accessory->image_file_name}.png' width='60px' height='60px' style='position: fixed; margin-left: 2.5px; margin-top: -90px'>";
    }

    if ((!($background))&&(!($hat))&&(!($tshirt))&&(!($trousers))&&(!($accessory)))
    {
        echo "<div style='text-align: center; margin-top: -120px; font-family: \"Arial Black\", Arial, sans-serif; z-index: 1'>Click here to edit your avatar.</div>";
    }
                   
    echo "          </div>

            </div></a>

            <div class='usersName'>
            
                <p class='firstName'>";
                if ($_SESSION['teacher'] == true)
                {
                    echo $title->title." ";
                }
                echo $user->first_name." ".$user->last_name." - ".$_SESSION['username'];

                //echo $lastName;

          echo "</p>";
          echo "<p class='leaderOf'>leader of</p>";



    $popNum = countPop();
    $_SESSION['population'] = $popNum;

          echo "<ul class='gameButtsContainer'>
                    <li class='cityNameButt'><a href='menu.php'><p>{$_SESSION['cityName']}</p></a></li>
                    <li class='cityYearButt'><a href='lastYearPeople.php'><p style='color: black'>Year {$cityDetails->year}</p></a></li>
                    <li class='cityPopButt' id='citizensButt'><a href='lastYearPeople.php'><p style='color: black'><img src='images/population_logo.png' width='20px' height='20px'> {$popNum}</p></a></li>
                    <li class='cityPopButt' id='accountsButt'><a href='lastYearAccount.php'><p style='color: black'><img src='images/coins_icon.png' width='20px' height='20px'> {$cityDetails->coins}</p></a></li>
                    <li class='cityPopButt'><a href='buyAvatar.php '><p style='color: black'><img src='images/qCoins_icon.png' width='20px' height='20px'> {$cityDetails->q_coins}</p></a></li>
                    <li class='cityPopButt'><a href='#'><p style='color: black'>XP {$cityDetails->xp}</p></a></li> 
                    <li class='cityPopButt'><a href='#'><p style='color: black'>Level {$cityDetails->level}</p></a></li>                    
                </ul>";

    $dbConn = getConnection();
    $SQLquery = "SELECT citizens_seen, accounts_seen
                       FROM LastYear
                       WHERE LastYear.city_id_fk = :cityID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensSeen = $stmt->fetchObject();

    if ($citizensSeen->citizens_seen == 0)
    {
        echo "
        
        <script type='text/javascript'>
            const citizensButt = document.getElementById('citizensButt');
            citizensButt.style.border = '2px Red Solid';
        </script>    
            
        ";
    }

    if ($citizensSeen->accounts_seen == 0)
    {
        echo "
        
        <script type='text/javascript'>
            const accountsButt = document.getElementById('accountsButt');
            accountsButt.style.border = '2px Red Solid';
        </script>    
            
        ";
    }

            
    echo "    </div>
            <div class='upperButtsContainer'>
            
            </div> 
            <div class='lowerButtsContainer'>
                <ul class='noteButtContainer'>
                    <li class='notificationButt' id='notificationButt'><a href='notifications.php' id='noteRed'>Notifications</a></li>
                    <li class='notificationButt' id='teacherButt'><a href='leaderboard.php'>Leaderboard</a></li>
                </ul>
            </div>
        </div>";

    $dbConn = getConnection();
    $SQLquery = "SELECT Notification.id_pk
                 FROM Notification
                 WHERE Notification.to_id_fk =:userID 
                 AND Notification.read =:read
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID'], ':read' => 0));
    $notes = $stmt->fetch();


    if($notes)
    {
        echo
        "
            <script type='text/javascript'>
                const redDot = document.getElementById('noteRed');
                redDot.style.color = 'Red';
            </script>
        ";
    }


}


function turnButtBlack($button)
{
    echo
    "
        <script type='text/javascript'>
            var gameButt = document.getElementById('$button');
            gameButt.style.backgroundColor = 'black';
        </script>    
    ";
}


function notLoggedIn()
{
    echo "<p>You're going to need to log in to access this part of the site.</p>";
}

function buildTeacherInterface($classSelected, $presetSelected)
{

    $dbConn = getConnection();
    $SQLquery = "SELECT Class.id_pk, Class.name
                 FROM Class
                 JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk
                 WHERE ClassTeachers.teacher_id_fk =:teacherID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array('teacherID' => $_SESSION['userID']));

    $classID = array();
    $className = array();
    while ($rowObj = $stmt->fetchObject())
    {
        $classID[] = $rowObj->id_pk;
        $className[] = $rowObj->name;
    }

    if (!(count($classID)) == 0)
    {
        $inClass = false;
        for ($i = 0; $i <= (count($classID) - 1); $i++)
        {
            if ($classSelected == $classID[$i])
            {
                $inClass = true;
            }
        }
    }
    else
    {
        $inClass = false;
    }

    if (!(count($classID)) == 0)
    {
        if (($classSelected == null) || ($inClass == false))
        {
            $classSelected = $classID[0];
        }
    }
    else
    {
        $classSelected = null;
    }

    echo "
    <div class='classesMain'>
    <div class='classButtContainer'>
        <div class='viewClasses'><a href=\"classes.php?classSelected={$classSelected}&presetSelected={$presetSelected}\">View Classes</a></div>
        <div class='selectContainer'>
            <form id='classSelector' action={$_SESSION['redirect']}.php method='get'>
                <select id='classSelect' name='classSelected' onchange='reloadFunc()'>";

    if(count($classID) == 0)
    {
        echo "<option value=null>Please create an Class</option>";
    }
    else
    {
        for ($i = 0; $i <= (count($classID) - 1); $i++)
        {
            if ($classSelected == $classID[$i])
            {
                $temp = "<option selected value='" . $classID[$i] . "'>";
            }
            else
            {
                $temp = "<option value='" . $classID[$i] . "'>";
            }
            echo $temp;
            echo $className[$i];
            echo "</option>";
        }
    }

    echo
    "
                </select>
             </form> 
             </div>  
             <div id='createClass' class='createClass2'><a href='createClass.php?classSelected={$classSelected}'>Create Class</a></div>
             <div id='deleteClass' class='deleteClass'><a href='deleteClass.php?classSelected={$classSelected}'>Delete Class</a></div>
             <div id='newStudentButt' class='newStudentButt'><a href='newStudentForm.php?classSelected={$classSelected}'>New Student</a></div>
             <div id='inviteTeacherButt' class='inviteTeacherButt'><a href='inviteTeacherForm.php?classSelected={$classSelected}'>Invite Teacher</a></div>
             <div id='downloadButt' class='downloadButt'><a href='downloadPDF.php?classSelected={$classSelected}' target='_blank'>Download PDF</a></div>
    ";

    $_SESSION['classSelected'] = $classSelected;
    return $classSelected;
}


function buildAccountTable($classSelected)
{
    echo "<div class='tableContainer'>
            <table class='accountsTable'>
                <tr>
                    <th  class='tableUsername'>Username</th> 
                    <th class='tableName'>Name</th> 
                    <th class='tableCheck'><input type='checkbox' id='checkAll' name='checkAll' onClick='selectAll(this)'></th>  
                </tr>";

    try
    {
        $dbConn = getConnection();
        $SQLquery = "SELECT DISTINCT Account.id_pk, Account.username, Account.first_name, Account.last_name, Class.id_pk
                 FROM Account
                 LEFT JOIN AccountClasses ON AccountClasses.user_id_fk = Account.id_pk
                 LEFT JOIN Class ON AccountClasses.class_id_fk = Class.id_pk
                 LEFT JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk
                 WHERE Class.id_pk =:classID
                 ORDER BY Account.username";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array('classID' => $classSelected));

        $i = 0;
        $accountArray = array();
        while ($rowObj = $stmt->fetchObject())
        {
            $accountArray[$i] = $rowObj;

            $dbConn = getConnection();
            $SQLquery = "SELECT DISTINCT Account.id_pk
                 FROM Account
                 WHERE Account.username =:username
                 ";
            $stmt3 = $dbConn->prepare($SQLquery);
            $stmt3->execute(array(':username' => $accountArray[$i]->username));

            $account = $stmt3->fetchObject();
            $accountID = $account->id_pk;

            $dbConn = getConnection();
            $querySQL = "SELECT id_pk
                 FROM Teacher
                 WHERE account_id_fk = :userID";
            $stmt2 = $dbConn->prepare($querySQL);
            $stmt2->execute(array(':userID' => $accountID));
            $temp = $stmt2->fetchColumn();

            if ($temp)
            {
                echo "<tr><td>» {$accountArray[$i]->username}</td><td>{$accountArray[$i]->first_name} {$accountArray[$i]->last_name}</td><td><input type='checkbox' id='{$accountArray[$i]->username}' value='{$accountID}' name='accountCheck'></td></tr>";
            }
            else
            {
                echo "<tr><td>{$accountArray[$i]->username}</td><td>{$accountArray[$i]->first_name} {$accountArray[$i]->last_name}</td><td><input type='checkbox' id='{$accountArray[$i]->username}' value='{$accountID}' name='accountCheck'></td></tr>";
            }

            $i++;
        }
        echo "
            </table>
        </div>";
    }
    catch(Exception $e)
    {
        echo "An error occurred.";
    }
}

function buildRewardForm($classSelected, $presetSelected)
{
    $dbConn = getConnection();
    $SQLquery = "SELECT Preset.id_pk, Preset.name
                 FROM Preset
                 WHERE Preset.teacher_id_fk =:teacherID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array('teacherID' => $_SESSION['userID']));

    $presetsID = array();
    $presetsName = array();
    while ($rowObj = $stmt->fetchObject())
    {
        $presetsID[] = $rowObj->id_pk;
        $presetsName[] = $rowObj->name;
    }

    if (!(count($presetsID)) == 0)
    {
        $inClass = false;
        for ($i = 0; $i <= (count($presetsID) - 1); $i++)
        {
            if ($presetSelected == $presetsID[$i])
            {
                $inClass = true;
            }
        }
    }
    else
    {
        $inClass = false;
    }

    if (!(count($presetsID)) == 0)
    {
        if ($inClass == false)
        {
            $presetSelected = null;
        }
    }
    else
    {
        $presetSelected = null;
    }

    echo
    "
        <div class='rewardFormContainer'>
            <form id='rewardForm' action=\"javascript: linkToRewardProc();\" method='get' >
                <select id='rewardPresetSelect' name='classSelected' onchange='reloadFunc()'>";

    if ($presetSelected == null)
    {
        echo "<option value=null selected>Presets</option>";
    }
    else
    {
        echo "<option value=null>Presets</option>";
    }

    for ($i = 0; $i <= (count($presetsID) - 1); $i++)
    {
        if ($presetSelected == $presetsID[$i])
        {
            $temp = "<option selected value='" . $presetsID[$i] . "'>";
        }
        else
        {
            $temp = "<option value='" . $presetsID[$i] . "'>";
        }
        echo $temp;
        echo $presetsName[$i];
        echo "</option>";
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT Preset.name, Preset.message, Preset.coins, q_coins
                 FROM Preset
                 WHERE Preset.id_pk =:presetID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array('presetID' => $presetSelected));
    $preset = $stmt->fetchObject();

    echo
    "           </select><br><br><br>
                <span class='presetName'>Name: <input type='text' id='presetNameInput' class='presetNameInput' value='{$preset->name}' maxlength='25'></span><br><br>
                <span class='presetMessage'>Message: <textarea class='presetMessageInput' id='presetMessageInput' maxlength=200>{$preset->message}</textarea></span><br>
                <span class='presetNums'>Coins: <input type='number' class='presetCoinsInput' id='presetCoinsInput' value={$preset->coins}>      Q-Coins: <input type='number' class='presetCoinsInput' id='presetQCoinsInput' value={$preset->q_coins}></span><br";

     if ($presetSelected == null)
     {
         echo "<span class='rewardFormSubmitSpan'> Create Preset <input type = 'checkbox' id = 'presetUpdateCheck'><input type = 'submit' class='rewardFormSubmit' value = 'Reward' ></span >";
     }
     else
     {
         echo "<span class='rewardFormSubmitSpan'> Update Preset <input type = 'checkbox' id = 'presetUpdateCheck'><input type = 'submit' class='rewardFormSubmit' value = 'Reward' ></span >";
     }
    echo     "</form>
        </div>
       
    ";

    echo
    "
        <script type='text/javascript'>
                window.addEventListener('load', function() {
                    'use strict';
                   
                });
               
                
                function reloadFunc()
                {
                    var classSelected = document.getElementById('classSelect').value;
                    var presetSelected = document.getElementById('rewardPresetSelect').value;
                
                    const text1 = '&classSelected='
                    
                    const checkboxes = document.getElementsByName('accountCheck');
                    const checkboxesNum = checkboxes.length;
                    const page = 'classes.php';
                    const checkboxNumVar = \"num=\";
                    const comp = checkboxesCheck();
                    
                    var text = '&presetSelected=';
                    var holder = page.concat(comp, checkboxNumVar, checkboxesNum.toString(), text1, classSelected, text, presetSelected);
                    document.location = holder;
                }


                function linkToRewardProc()
                {
                    const checkboxes = document.getElementsByName('accountCheck');
                    const checkboxesNum = checkboxes.length;
                    const page = 'rewardProcess.php';
                    const checkboxNumVar = \"num=\";
                    const comp = checkboxesCheck();
                        
                    const nameText = '&name=';
                    const nameV = document.getElementById('presetNameInput').value;
                    const messageText = '&message=';
                    const messageV = document.getElementById('presetMessageInput').value;
                    const coinsText = '&coins=';
                    const coinsV = document.getElementById('presetCoinsInput').value;
                    const qCoinsText = '&qCoins=';
                    const qCoinsV = document.getElementById('presetQCoinsInput').value;
                    const updateCheckText = '&updateCheck=';
                    const updateCheck = document.getElementById('presetUpdateCheck');
                    const updateCheckV = updateCheck.checked;
                    const presetSelectedText = '&presetSelected=';
                    const presetSelectedV = document.getElementById('rewardPresetSelect').value;
                    
                    const holder = page.concat(comp, checkboxNumVar, checkboxesNum.toString(), nameText, nameV, messageText, messageV, coinsText, coinsV, qCoinsText, qCoinsV, updateCheckText, updateCheckV, presetSelectedText, presetSelectedV);
                    document.location = holder;

                }
                
                function checkboxesCheck()
                {

                    var checkboxes = document.getElementsByName('accountCheck');

                    var comp = \"?\"
                    for(var i=0, n=checkboxes.length;i<n;i++)
                    {
                        var add = \"\"
                        if (checkboxes[i].checked == true)
                        {
                            const box = i;
                            const vari = \"var\"
                            const boxText = vari.concat(box);
                            const equals = \"=\"
                            const value = checkboxes[i].value;
                            const amper = \"&\"
                            add = add.concat(boxText, equals, value, amper)
                        }

                        comp = comp.concat(add);
                    }
                    return comp;
                }
                   
        </script>
    ";

    return $presetSelected;
}

function testAlreadyAccepted($teacher, $class)
{
    $dbConn = getConnection();
    $querySQL = "SELECT id_pk
                 FROM ClassTeachers
                 WHERE teacher_id_fk = :userID AND class_id_fk =:classID";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':userID' => $teacher, ':classID' => $class));
    $temp = $stmt->fetchColumn();

    if ($temp)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function deleteAccount($i)
{
    //Delete all records from AccountClasses table where user_id_fk = $idTicked[$i]

    $dbConn = getConnection();
    $SQLdelete = "DELETE FROM AccountClasses
                                  WHERE user_id_fk =:studentID
                                 ";
    $stmt = $dbConn->prepare($SQLdelete);
    $stmt->execute(array(':studentID' => $i));

    //Delete record from Account table where id_pk = $idTicked[$i]

    $dbConn = getConnection();
    $SQLdelete = "DELETE FROM Account
                                  WHERE id_pk =:studentID
                                 ";
    $stmt = $dbConn->prepare($SQLdelete);
    $stmt->execute(array(':studentID' => $i));

    //If $idTicked[$i] is a member of the teacher table

    $dbConn = getConnection();
    $SQLquery = "SELECT Teacher.id_pk
                                 FROM Teacher
                                 WHERE Teacher.account_id_fk =:teacherID
                                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':teacherID' => $i));
    $teacher = $stmt ->fetchObject();

    if ($teacher)
    {
        //Delete all records from ClassTeachers where user_id_pk = $idTicked[$i]

        $dbConn = getConnection();
        $SQLdelete = "DELETE FROM ClassTeachers
                                      WHERE teacher_id_fk =:teacherID
                                     ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':teacherID' => $i));

        //Delete record from Teacher where account_id_pk = $idTicked[$i]

        $dbConn = getConnection();
        $SQLdelete = "DELETE FROM Teacher
                                      WHERE account_id_fk =:teacherID
                                     ";
        $stmt = $dbConn->prepare($SQLdelete);
        $stmt->execute(array(':teacherID' => $i));
    }
    //Delete all records from Notification where to_id_fk = $idTicked[$i]
    $dbConn = getConnection();
    $SQLdelete = "DELETE FROM Notification
                                  WHERE to_id_fk =:studentID
                                     ";
    $stmt = $dbConn->prepare($SQLdelete);
    $stmt->execute(array(':studentID' => $i));

    //REMEMBER TO DO
    //Delete all game variables associated with account
}

function popArray()
{
    $dbConn = getConnection();
    $querySQL = "SELECT id_pk
                 FROM Citizen
                 WHERE city_id_fk =:cityID";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $popArray = array();
    while ($rowObj = $stmt->fetchObject())
    {
        $popArray[] = $rowObj->id_pk;
    }
    return $popArray;
}

function countPop()
{
    $popArray = popArray();
    return count($popArray);
}

function houseAssingment()
{

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID";
    $stmt1 = $dbConn->prepare($querySQL);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));

    while ($rowObj = $stmt1->fetchObject())
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET house_id_fk = 0
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt4 = $dbConn->prepare($SQLupdate);
        $stmt4->execute(array(':citizenID' => $rowObj->id_pk));
    }

    $dbConn = getConnection();
    $querySQL = "SELECT Room.id_pk, Accommodation.rent, Accommodation.quality
                 FROM Room
                 LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                 WHERE Room.city_id_fk =:cityID
                 ORDER BY Accommodation.quality DESC, Accommodation.rent ASC, Accommodation.id_pk ASC";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while ($roomRow =  $stmt->fetchObject())
    {
        if ($roomRow->rent <= 250)
        {
            $wealthRequired = 0;
        }
        if (($roomRow->rent > 250) && ($roomRow->rent <= 500))
        {
            $wealthRequired = 1;
        }
        if (($roomRow->rent > 500) && ($roomRow->rent <= 1000))
        {
            $wealthRequired = 2;
        }
        if (($roomRow->rent > 1000) && ($roomRow->rent <= 2000))
        {
            $wealthRequired = 3;
        }
        if (($roomRow->rent > 2000))
        {
            $wealthRequired = 4;
        }


        $dbConn = getConnection();
        $querySQL = "SELECT Citizen.id_pk, Citizen.wealth_status, Citizen.house_id_fk
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                 ORDER BY Citizen.wealth_status DESC";
        $stmt3 = $dbConn->prepare($querySQL);
        $stmt3->execute(array(':cityID' => $_SESSION['cityID']));

        $occupentFound = false;
        while ($citizenRow = $stmt3->fetchObject())
        {
            if ($occupentFound == false)
            {
                if ($citizenRow->house_id_fk == 0)
                {
                    if ($citizenRow->wealth_status >= $wealthRequired)
                    {
                        $occupentFound = true;

                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE Citizen
                                      SET house_id_fk =:houseID
                                      WHERE Citizen.id_pk =:citizenID
                                     ";
                        $stmt4 = $dbConn->prepare($SQLupdate);
                        $stmt4->execute(array(':citizenID' => $citizenRow->id_pk, ':houseID' => $roomRow->id_pk));

                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE Room
                                      SET inhabited = 1
                                      WHERE Room.id_pk =:houseID
                                     ";
                        $stmt4 = $dbConn->prepare($SQLupdate);
                        $stmt4->execute(array(':houseID' => $roomRow->id_pk));
                    }
                }
            }
        }
        if ($occupentFound == false)
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE Room
                          SET inhabited = 0
                          WHERE Room.id_pk =:houseID
                                     ";
            $stmt4 = $dbConn->prepare($SQLupdate);
            $stmt4->execute(array(':houseID' => $roomRow->id_pk));
        }

    }

    housingHappiness();
}

function getHousingIncrease()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 9)
        {
            $increase = $increase + 1;
        }
    }
    return $increase;
}

function housingHappiness()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.house_id_fk = 0
                 ";
    $stmt1 = $dbConn->prepare($SQLselect);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt1->fetchObject();
    $hCitizensCount = $citizensInfo->counter;

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.house_id_fk, Citizen.wealth_status, Accommodation.rent, Accommodation.quality, AccommodationType.base_quality, AccommodationType.lower_rent_limit, AccommodationType.upper_rent_limit
                 FROM Citizen
                 LEFT JOIN Room ON Citizen.house_id_fk = Room.id_pk
                 LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                 LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $homelessRate = ($hCitizensCount/countPop())*100;

    $homelessHappiness = ((1-($homelessRate/0.1))*100);
    if ($homelessHappiness < 0)
    {
        $homelessHappiness = 0;
    }

    $increase = getHousingIncrease();

    while ($rowobj = $stmt->fetchObject())
    {

        if (!($rowobj->house_id_fk == 0))
        {
            $houseQuality = $rowobj->quality;
            if ($houseQuality > 100)
            {
                $houseQuality = 100;
            }

            $equationA = (($rowobj->rent)-($rowobj->lower_rent_limit))/(($rowobj->upper_rent_limit)-($rowobj->lower_rent_limit));
            $equationB = (($rowobj->quality)/$rowobj->base_quality);
            $rentHappiness = ((1-($equationA))*($equationB))*100;

            if ($rentHappiness > 100)
            {
                $rentHappiness = 100;
            }

            $expectedQuality = (14+(12*(($rowobj->wealth_status)+1)));

            $wealthToQualHappiness = (((($rowobj->quality)/$expectedQuality)/2)*100);

            if ($wealthToQualHappiness > 100)
            {
                $wealthToQualHappiness = 100;
            }


            $housingHappiness = round((($houseQuality*0.4)+($rentHappiness*0.3)+($wealthToQualHappiness*0.2)+($homelessHappiness*0.1)+$increase),0);

            if ($housingHappiness > 100)
            {
                $housingHappiness = 100;
            }

        }
        else
        {
            $housingHappiness = 0;
        }
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET housing_happiness =:housingHappy
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt4 = $dbConn->prepare($SQLupdate);
        $stmt4->execute(array(':citizenID' => $rowobj->id_pk, ':housingHappy' => $housingHappiness));
    }
    overallHappiness();
}

function getHouseImage($houseTypeID)
{
    if ($houseTypeID == 1)
    {
        $imagePath = 'images/block_of_flats_icon.png';
    }
    if ($houseTypeID == 2)
    {
        $imagePath = 'images/b_terraced_house_icon.png';
    }
    if ($houseTypeID == 3)
    {
        $imagePath = 'images/bungalow_icon.png';
    }
    if ($houseTypeID == 4)
    {
        $imagePath = 'images/u_terraced_house_icon.png';
    }
    if ($houseTypeID == 5)
    {
        $imagePath = 'images/mansion_icon.png';
    }
    if ($houseTypeID == 6)
    {
        $imagePath = 'images/high_rise_flats_icon.png';
    }
    if ($houseTypeID == 7)
    {
        $imagePath = 'images/apartment_block_icon.png';
    }
    if ($houseTypeID == 8)
    {
        $imagePath = 'images/country_estate_icon.png';
    }


    return $imagePath;
}

function getHouseType($houseID)
{
    if ($houseID == 1)
    {
        $type = 'Block of Flats';
    }
    if ($houseID == 2)
    {
        $type = 'Basic Terraced House';
    }
    if ($houseID == 3)
    {
        $type = 'Bungalow';
    }
    if ($houseID == 4)
    {
        $type = 'Upmarket Terraced House';
    }
    if ($houseID == 5)
    {
        $type = 'Mansion';
    }
    if ($houseID == 6)
    {
        $type = 'High-Rise Flats';
    }
    if ($houseID == 7)
    {
        $type = 'Upmarket Apartment Block';
    }
    if ($houseID == 8)
    {
        $type = 'Country Estate';
    }


    return $type;
}

function getBusImage($busID)
{
    if ($busID == 1)
    {
        $imagePath = 'images/steel_manu_icon.png';
    }
    if ($busID == 2)
    {
        $imagePath = 'images/fashion_brand_icon.png';
    }
    if ($busID == 3)
    {
        $imagePath = 'images/advertising_icon.png';
    }
    if ($busID == 4)
    {
        $imagePath = 'images/construction_icon.png';
    }
    if ($busID == 5)
    {
        $imagePath = 'images/toy_factory_icon.png';
    }
    if ($busID == 6)
    {
        $imagePath = 'images/tech_start_up_icon.png';
    }
    if ($busID == 7)
    {
        $imagePath = 'images/it_consultant_icon.png';
    }
    if ($busID == 8)
    {
        $imagePath = 'images/music_producer_icon.png';
    }
    if ($busID == 9)
    {
        $imagePath = 'images/ship_building_icon.png';
    }
    if ($busID == 10)
    {
        $imagePath = 'images/football_icon.png';
    }
    if ($busID == 11)
    {
        $imagePath = 'images/airline_icon.png';
    }
    if ($busID == 12)
    {
        $imagePath = 'images/film_icon.png';
    }
    if ($busID == 13)
    {
        $imagePath = 'images/car_manu_icon.png';
    }
    if ($busID == 14)
    {
        $imagePath = 'images/petrol_company_icon.png';
    }
    if ($busID == 15)
    {
        $imagePath = 'images/space_icon.png';
    }


    return $imagePath;
}


function getBusType($busID)
{
    if ($busID == 1)
    {
        $type = 'Steel Manufacturer';
    }
    if ($busID == 2)
    {
        $type = 'Fashion Brand';
    }
    if ($busID == 3)
    {
        $type = 'Advertising Agency';
    }
    if ($busID == 4)
    {
        $type = 'Construction Company';
    }
    if ($busID == 5)
    {
        $type = 'Toy Factory';
    }
    if ($busID == 6)
    {
        $type = 'Tech Startup';
    }
    if ($busID == 7)
    {
        $type = 'IT Consultant';
    }
    if ($busID == 8)
    {
        $type = 'Music Producer';
    }
    if ($busID == 9)
    {
        $type = 'Ship Building Yard';
    }
    if ($busID == 10)
    {
        $type = 'Pro Football';
    }
    if ($busID == 11)
    {
        $type = 'Airline';
    }
    if ($busID == 12)
    {
        $type = 'Film Production';
    }
    if ($busID == 13)
    {
        $type = 'Car Manufacturer';
    }
    if ($busID == 14)
    {
        $type = 'Petroleum Company';
    }
    if ($busID == 15)
    {
        $type = 'Space Exploration';
    }

    return $type;
}

function rewardXP($xp)
{
    $newXP = $_SESSION['xp'] + $xp;
    $_SESSION['xp'] = $newXP;

    $dbConn = getConnection();
    $SQLupdate = "UPDATE City
                  SET xp = :xp
                  WHERE City.id_pk =:cityID
                   ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':xp' => $newXP, ':cityID' => $_SESSION['cityID']));

    calcLevel();
}

function rewardQcoin($qCoin)
{
    $newQCoin = $_SESSION['qCoin'] + $qCoin;
    $_SESSION['qCoin'] = $newQCoin;

    $dbConn = getConnection();
    $SQLupdate = "UPDATE City
                  SET q_coins = :qCoin
                  WHERE City.id_pk =:cityID
                   ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':qCoin' => $newQCoin, ':cityID' => $_SESSION['cityID']));
}

function calcLevel()
{
    $xp = $_SESSION['xp'];
    $level = 0;
    $i = 0;
    $higherThan = true;
    while ($higherThan == true)
    {
      $xpRequired = 333.33*exp(0.4055*($i+1));
      $xpRequired = round($xpRequired, 0);

      if ($xp >= $xpRequired)
      {
          $i++;
      }
      else
      {
          $level = $i;
          $higherThan = false;
      }
    }

    $dbConn = getConnection();
    $SQLupdate = "UPDATE City
                  SET level = :levelT
                  WHERE City.id_pk =:cityID
                   ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':levelT' => $level, ':cityID' => $_SESSION['cityID']));
    $_SESSION['level'] = $level;
}

function getDifferenceInDates()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT Account.last_log_in
                  FROM Account
                  WHERE Account.id_pk =:userID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':userID' => $_SESSION['userID']));
    $temp = $stmt->fetchObject();

    $lastLogIn = $temp->last_log_in;
    $lastLogIn = strtotime($lastLogIn);
    $date = strtotime(date('Y/m/d'));
    if ($lastLogIn < $date)
    {
        $t = $date - $lastLogIn;
        $daysSince = $t/86400;
    }
    else if ($lastLogIn > $date)
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Account
                  SET last_log_in =:dateT
                  WHERE Account.id_pk =:userID
                                     ";
        $stmt = $dbConn->prepare($SQLupdate);
        $stmt->execute(array(':userID' => $_SESSION['userID'], ':dateT' => date('Y/m/d')));
        getDifferenceInDates();
        $daysSince = 0;
    }
    else
    {
        $daysSince = 0;
    }

    return $daysSince;

}

function updateLastYearHapp()
{
    $dbConn = getConnection();
    $querySQL = "SELECT AVG(Citizen.overall_happiness) as overall, AVG(Citizen.housing_happiness) as housing, AVG(Citizen.education_happiness) as education, 
                        AVG(Citizen.job_happiness) as job, AVG(Citizen.recreation_happiness) as recreation, 
                        AVG(Citizen.health_happiness) as health, AVG(Citizen.safety_happiness) as safety, AVG(Citizen.qol_happiness) as qol
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID 
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $happ = $stmt->fetchObject();


    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.overall_happiness =:overall, LastYear.housing_happiness =:housing, LastYear.education_happiness =:education, 
                      LastYear.job_happiness =:job, LastYear.recreation_happiness =:recreation, 
                      LastYear.health_happiness =:health, LastYear.safety_happiness =:safety, LastYear.qol_happiness =:qol, LastYear.starting_pop =:pop,
                      LastYear.starting_coins =:coins
                  WHERE LastYear.city_id_fk =:cityID
                  ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':overall' => $happ->overall, ':housing' => $happ->housing,
                         ':education' => $happ->education, ':health' => $happ->health, ':job' => $happ->job,
                         ':recreation' => $happ->recreation, ':safety' => $happ->safety, ':qol' => $happ->qol, ':pop' => $_SESSION['population'],
                         ':coins' => $_SESSION['coins']));
}


function newYear()
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE City
                      SET City.daily_log_in = 0
                      WHERE City.player_id_fk =:userID
                  ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    //Change City Year
    $dbConn = getConnection();
    $SQLupdate = "UPDATE City
                  SET City.year = (City.year + 1)
                  WHERE City.id_pk =:cityID
                  ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $dbConn = getConnection();
    $querySQL = "SELECT City.year
                 FROM City
                 WHERE City.id_pk =:cityID 
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $year = $stmt->fetchObject();

    updateLastYearHapp();

    //Deterioate Houses
    $testYearIs5 = $year->year/5;
    if (is_int($testYearIs5))
    {
        deteriorateHouses();
        deteriorateFire();
        deteriorateHealth();
        deterioratePolice();
    }

    //Coin Incomings
    collectRent();
    collectTax();
    calcBusinessRev();
    calcRecRev();

    //Coin Outgoings
    payCitizens();
    payRetirementFunds();
    buildingUpkeep();

    ageCitizens();
    citizensDifference();
    incrementEducYear();
    educCheck();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(EducationSpace.id_pk) AS educSpaces
                 FROM EducationSpace
                 WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.taken = 0
                 LIMIT 1";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $educSpaces = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Job
                  SET Job.citizen_employed = 0
                  WHERE Job.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID']));

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Room
                  SET Room.inhabited = 0
                  WHERE Room.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID']));

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Citizen
                  SET Citizen.house_id_fk = null
                  WHERE Citizen.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID']));

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.age, Citizen.retired, Citizen.educational_status, Citizen.job_id_fk, 
                        Citizen.in_education, City.retirement_age, Citizen.wealth_status
                 FROM Citizen
                 LEFT JOIN City ON Citizen.city_id_fk = City.id_pk
                 WHERE Citizen.city_id_fk =:cityID
                 ORDER BY Citizen.educational_status DESC, Citizen.wealth_status DESC, Citizen.age ASC
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Citizen
                  SET Citizen.job_id_fk = null
                  WHERE Citizen.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID']));

    $educSpacesTaken = 0;
    $newRetired = 0;
    $newRetiredFund = 0;

    $startedSchool = 0;
    $startedCollege = 0;
    $startedUni = 0;


    while ($citizenInfo = $stmt->fetchObject())
    {
        if ($citizenInfo->age > 5)
        {
            //Education Assignment

            if ($citizenInfo->in_education == 0)
            {
                if ($educSpacesTaken < $educSpaces->educSpaces)
                {
                    if ($citizenInfo->educational_status == 0)
                    {
                        $educType = 0;
                        $assign = true;
                    }
                    else if ($citizenInfo->educational_status == 1)
                    {
                        $educType = 1;
                        $assign = true;
                    }
                    else if ($citizenInfo->educational_status == 2)
                    {
                        $educType = 2;
                        $assign = true;
                    }
                    else
                    {
                        $assign = false;
                    }

                    if ($assign == true)
                    {
                        $educAssigned = educAssignmentCitizen($citizenInfo->id_pk, $educType);
                        if ($educAssigned == true)
                        {
                            if ($educType == 0)
                            {
                                $startedSchool++;
                            }
                            else if ($educType == 1)
                            {
                                $startedCollege++;
                            }
                            else if ($educType == 2)
                            {
                                $startedUni++;
                            }

                            $educSpacesTaken++;
                        }
                    }
                }
            }

            //Checking Retirement

            if ((($citizenInfo->age) >= ($citizenInfo->retirement_age)))
            {
                if ($citizenInfo->retired == 0)
                {
                    $dbConn = getConnection();
                    $querySQL = "SELECT Job.salary
                                 FROM Job
                                 WHERE Job.id_pk =:jobID
                                ";
                    $stmt3 = $dbConn->prepare($querySQL);
                    $stmt3->execute(array(':jobID' => $citizenInfo->job_id_fk));
                    $salary = $stmt3->fetchObject();

                    $newRetired++;

                    if ($salary)
                    {
                        $retiredFund = ($salary->salary)/2;
                        $newRetiredFund++;
                    }
                    else
                    {
                        $retiredFund = 0;
                    }

                    $dbConn = getConnection();
                    $SQLupdate = "UPDATE Citizen
                              SET Citizen.retirement_fund =:retiredFund
                              WHERE Citizen.id_pk =:citizenID
                         ";
                    $stmt2 = $dbConn->prepare($SQLupdate);
                    $stmt2->execute(array(':citizenID' => $citizenInfo->id_pk, ':retiredFund' => $retiredFund));
                }

                $dbConn = getConnection();
                $SQLupdate = "UPDATE Citizen
                          SET Citizen.retired =:retiredBool
                          WHERE Citizen.id_pk =:citizenID
                         ";
                $stmt2 = $dbConn->prepare($SQLupdate);
                $stmt2->execute(array(':citizenID' => $citizenInfo->id_pk, ':retiredBool' => 1));
            }

        }

        //Check if citizen can work
        if (($citizenInfo->age > 15) && ($citizenInfo->age < $citizenInfo->retirement_age) && ($citizenInfo->in_education == 0) && ($citizenInfo->retired == 0))
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE Citizen
                          SET Citizen.can_work = 1
                          WHERE Citizen.id_pk =:citizenID
                         ";
            $stmt2 = $dbConn->prepare($SQLupdate);
            $stmt2->execute(array(':citizenID' => $citizenInfo->id_pk));

            //Job Assingment

            $dbConn = getConnection();
            $querySQL = "SELECT COUNT(Job.id_pk) AS jobsLeft
                         FROM Job
                         WHERE Job.city_id_fk =:cityID AND Job.citizen_employed = 0 ";
            $stmtE = $dbConn->prepare($querySQL);
            $stmtE->execute(array(':cityID' => $_SESSION['cityID']));
            $jobsLeft = $stmtE->fetchObject();

            if ($jobsLeft->jobsLeft > 0)
            {
                $dbConn = getConnection();
                $querySQL = "SELECT Job.id_pk
                             FROM Job
                             WHERE Job.city_id_fk =:cityID AND Job.citizen_employed = 0 AND Job.educ_required < :educReq
                             ORDER BY Job.educ_required DESC, Job.job_industry ASC, Job.quality DESC, Job.Salary DESC
                             LIMIT 1";
                $stmtJ = $dbConn->prepare($querySQL);
                $stmtJ->execute(array(':cityID' => $_SESSION['cityID'], ':educReq' => ($citizenInfo->educational_status + 1)));
                $job = $stmtJ->fetchObject();
                if ($job)
                {
                    $dbConn = getConnection();
                    $SQLupdate = "UPDATE Citizen
                                  SET job_id_fk =:jobID
                                  WHERE Citizen.id_pk =:citizenID
                                     ";
                    $stmt4 = $dbConn->prepare($SQLupdate);
                    $stmt4->execute(array(':citizenID' => $citizenInfo->id_pk, ':jobID' => $job->id_pk));

                    $dbConn = getConnection();
                    $SQLupdate = "UPDATE Job
                                  SET citizen_employed = 1
                                  WHERE Job.id_pk =:jobID
                                     ";
                    $stmt4 = $dbConn->prepare($SQLupdate);
                    $stmt4->execute(array(':jobID' => $job->id_pk));
                }
            }
        }
        else
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE Citizen
                          SET Citizen.can_work = 0
                          WHERE Citizen.id_pk =:citizenID
                         ";
            $stmt3 = $dbConn->prepare($SQLupdate);
            $stmt3->execute(array(':citizenID' => $citizenInfo->id_pk));
        }

        //House Assignemt
        $dbConn = getConnection();
        $querySQL = "SELECT COUNT(Room.id_pk) AS roomsLeft
                         FROM Room
                         WHERE Room.city_id_fk =:cityID AND Room.inhabited = 0 ";
        $stmtE = $dbConn->prepare($querySQL);
        $stmtE->execute(array(':cityID' => $_SESSION['cityID']));
        $roomsLeft = $stmtE->fetchObject();

        if ($roomsLeft->roomsLeft > 0)
        {
            $dbConn = getConnection();
            $querySQL = "SELECT Room.id_pk
                         FROM Room
                         LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                         WHERE Room.city_id_fk =:cityID AND Room.inhabited = 0 AND Accommodation.rent <= :rentPos
                         ORDER BY Accommodation.quality DESC, Accommodation.rent ASC
                         LIMIT 1";
            $stmtJ = $dbConn->prepare($querySQL);
            $stmtJ->execute(array(':cityID' => $_SESSION['cityID'], ':rentPos' => getWealthForRent($citizenInfo->wealth_status)));
            $room = $stmtJ->fetchObject();
            if ($room)
            {
                $dbConn = getConnection();
                $SQLupdate = "UPDATE Citizen
                              SET house_id_fk =:houseID
                              WHERE Citizen.id_pk =:citizenID
                              ";
                $stmt4 = $dbConn->prepare($SQLupdate);
                $stmt4->execute(array(':citizenID' => $citizenInfo->id_pk, ':houseID' => $room->id_pk));

                $dbConn = getConnection();
                $SQLupdate = "UPDATE Room
                              SET inhabited = 1
                              WHERE Room.id_pk =:roomID
                                     ";
                $stmt4 = $dbConn->prepare($SQLupdate);
                $stmt4->execute(array(':roomID' => $room->id_pk));
            }
        }


    }

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET retired =:retired, retired_with_fund =:retiredWF, started_school = :startSchool, started_College = :startCollege,
                  started_uni = :startUni, citizens_seen = 0, accounts_seen = 0
                  WHERE LastYear.city_id_fk =:cityID
                              ";
    $stmt4 = $dbConn->prepare($SQLupdate);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID'], ':retired' => $newRetired, ':retiredWF' => $newRetiredFund,
                          ':startSchool' => $startedSchool, ':startCollege' => $startedCollege, ':startUni' => $startedUni));

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Accommodation.quality, Accommodation.rent, AccommodationType.base_quality,
                        AccommodationType.lower_rent_limit, AccommodationType.upper_rent_limit, Citizen.wealth_status, 
                        Citizen.house_id_fk, Citizen.educational_status, Citizen.job_id_fk, Citizen.can_work,
                        Citizen.retired, Citizen.retirement_fund, Citizen.age
                 FROM Citizen
                 LEFT JOIN Room ON Citizen.house_id_fk = Room.id_pk
                 LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                 LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                 WHERE Citizen.city_id_fk =:cityID
                 ORDER BY Citizen.wealth_status ASC
                ";
    $stmtY = $dbConn->prepare($querySQL);
    $stmtY->execute(array(':cityID' => $_SESSION['cityID']));

    $homelessHappiness = getHomelessHapp();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 0 
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $noneEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $schoolEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 2 
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $collEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 3
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $uniEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS counter
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 0
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $schoolSpaces = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS counter
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $collegeSpaces = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS counter
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 2
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $universitySpaces = $stmt->fetchObject();

    $popCanBeEduc = (countPop())-($uniEducated->counter);
    $totalSpaces = ($schoolSpaces->counter)+($collegeSpaces->counter)+($universitySpaces->counter);
    if ($popCanBeEduc == 0)
    {
        $popEducAvailble = 100;
    }
    else
    {
        $popEducAvailble = (($totalSpaces)/($popCanBeEduc)*100);
    }
    if ($popEducAvailble > 100)
    {
        $popEducAvailble = 100;
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk = 0 AND Citizen.can_work = 1
                 ";
    $stmt1 = $dbConn->prepare($SQLselect);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo3 = $stmt1->fetchObject();
    $unECitizensCount = $citizensInfo3->counter;


    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.can_work = 1
                 ";
    $stmt6 = $dbConn->prepare($SQLselect);
    $stmt6->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo2 = $stmt6->fetchObject();

    if ($citizensInfo2->counter > 0)
    {
        $unEmpRate = ($unECitizensCount/$citizensInfo2->counter)*100;
    }
    else
    {
        $unEmpRate = 0;
    }
    $emplomentHappiness = 100-$unEmpRate;

    recHappiness();

    $healthHappT = getHealthHapp();

    if ($_SESSION['level'] > 15)
    {
        safetyHappiness();
    }

    $qolHap = getQolHappiness();

    $housingIncrease = getHousingIncrease();
    $healthIncrease = getHealthIncrease();

    while ($citizenInfo = $stmtY->fetchObject())
    {
        //Housing Happiness
        if (!($citizenInfo->house_id_fk == 0))
        {
            $houseQuality = $citizenInfo->quality;
            if ($houseQuality > 100)
            {
                $houseQuality = 100;
            }

            $equationA = (($citizenInfo->rent)-($citizenInfo->lower_rent_limit))/(($citizenInfo->upper_rent_limit)-($citizenInfo->lower_rent_limit));
            $equationB = (($citizenInfo->quality)/($citizenInfo->base_quality));
            $rentHappiness = ((1-($equationA))*($equationB))*100;
            if ($rentHappiness > 100)
            {
                $rentHappiness = 100;
            }
            $expectedQuality = (14+(12*(($citizenInfo->wealth_status)+1)));
            $wealthToQualHappiness = (((($citizenInfo->quality)/$expectedQuality)/2)*100);
            if ($wealthToQualHappiness > 100)
            {
                $wealthToQualHappiness = 100;
            }

            $housingHappiness = ($houseQuality*0.4)+($rentHappiness*0.3)+($wealthToQualHappiness*0.2)+($homelessHappiness*0.1)+$housingIncrease;
            if ($housingHappiness > 100)
            {
                $housingHappiness = 100;
            }
        }
        else
        {
            $housingHappiness = 0;
        }

        //Educational Happiness
        $educStatusHapp = ($citizenInfo->educational_status/3)*100;

        if ($citizenInfo->educational_status == 0)
        {
            $educAvailble = (($schoolSpaces->counter)/($noneEducated->counter)*100);
        }
        else if ($citizenInfo->educational_status == 1)
        {
            $educAvailble = (($collegeSpaces->counter)/($schoolEducated->counter)*100);
        }
        else if ($citizenInfo->educational_status == 2)
        {
            $educAvailble = (($universitySpaces->counter)/($collEducated->counter)*100);
        }
        else if ($citizenInfo->educational_status == 3)
        {
            $educAvailble = 100;
        }
        if ($educAvailble > 100)
        {
            $educAvailble = 100;
        }

        $educHappiness = ($educStatusHapp * 0.3)+($educAvailble * 0.5)+($popEducAvailble * 0.2);

        //Job Happiness

        $dbConn = getConnection();
        $SQLselect = "SELECT Job.quality, Job.salary
                      FROM Citizen
                      LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                      WHERE Citizen.id_pk =:citizenID
                      ";
        $stmt1 = $dbConn->prepare($SQLselect);
        $stmt1->execute(array(':citizenID' => $citizenInfo->id_pk));
        $temp = $stmt1->fetchObject();

        if (!($citizenInfo->job_id_fk == 0))
        {

            $jobQuality = $temp->quality;

            $expectedSalary = getSalary($citizenInfo->educational_status);
            $salHappiness = ((($temp->salary)/($expectedSalary))/2)*100;
            if ($salHappiness > 100)
            {
                $salHappiness = 100;
            }
            $jobHappiness = round((($jobQuality*0.4)+($salHappiness*0.4)+($emplomentHappiness*0.2)),0);
        }
        else
        {
            if ($citizenInfo->can_work == 0)
            {
                $jobQuality = 50;
                if ($citizenInfo->retired == 1)
                {
                    $expectedSalary = getSalary($citizenInfo->educational_status);
                    $salHappiness = ((($citizenInfo->retirement_fund)/($expectedSalary))/2)*100;
                    if ($salHappiness > 100)
                    {
                        $salHappiness = 100;
                    }
                }
                else
                {
                    $salHappiness = 50;
                }
                $jobHappiness = round((($jobQuality*0.4)+($salHappiness*0.4)+($emplomentHappiness*0.2)),0);
            }
            else
            {
                $jobHappiness = round($emplomentHappiness*0.2, 0);
            }
        }

        //Health Happiness
        $healthHappiness = $healthHappT + ($citizenInfo->age*0.1) + $healthIncrease;
        if ($healthHappiness > 100)
        {
            $healthHappiness = 100;
        }

        //QOL Happiness
        if ($citizenInfo->age < 16)
        {
            $retirementFundHap = 100;
        }
        else
        {
            if ($citizenInfo->retired == 1)
            {
                $money = $citizenInfo->retirement_fund;
            }
            else
            {
                $money = ($temp->salary)/2;
            }
            $retirementFundHap = ($money/3750)*100;
        }

        if ($retirementFundHap < 0)
        {
            $retirementFundHap = 0;
        }
        if ($retirementFundHap > 100)
        {
            $retirementFundHap = 100;
        }

        $qolHappiness = $qolHap+($retirementFundHap*0.2);
        if ($qolHappiness > 100)
        {
            $qolHappiness = 100;
        }

        //Overall Happiness
        $dbConn = getConnection();
        $SQLselect = "SELECT Citizen.recreation_happiness, Citizen.safety_happiness
                      FROM Citizen
                      WHERE Citizen.id_pk =:citizenID
                      ";
        $stmt1 = $dbConn->prepare($SQLselect);
        $stmt1->execute(array(':citizenID' => $citizenInfo->id_pk));
        $info = $stmt1->fetchObject();

        if ($_SESSION['level'] > 15)
        {
            $overallHappiness = (($housingHappiness * 0.12)+($educHappiness * 0.15)+($jobHappiness * 0.15)+($info->recreation_happiness * 0.15)+($healthHappiness * 0.15)+($info->safety_happiness * 0.18)+($qolHappiness * 0.10));
        }
        else
        {
            $overallHappiness = (($housingHappiness * 0.12)+($educHappiness * 0.15)+($jobHappiness * 0.15)+($info->recreation_happiness * 0.15)+($healthHappiness * 0.15)+($info->safety_happiness * 0.18)+($qolHappiness * 0.10));
            $overallHappiness= (($overallHappiness)/82)*100;
        }

        //Updating happiness in database
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET Citizen.housing_happiness =:houseHapp, Citizen.education_happiness =:educHapp, Citizen.job_happiness =:jobHapp,
                          Citizen.health_happiness =:healthHapp, Citizen.qol_happiness =:qolHapp, Citizen.overall_happiness =:overHapp
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt5 = $dbConn->prepare($SQLupdate);
        $stmt5->execute(array(':houseHapp' => round($housingHappiness,0), ':citizenID' => $citizenInfo->id_pk,
            ':educHapp' => round($educHappiness,0), 'jobHapp' => $jobHappiness,
            ':healthHapp' => round($healthHappiness,0), ':qolHapp' => $qolHappiness,
            ':overHapp' => round($overallHappiness,0)));
    }
}

function buildingUpkeep()
{
    $dbConn = getConnection();
    $querySQL = "SELECT SUM(AccommodationType.running_cost) AS runningCost
                 FROM Accommodation
                 LEFT JOIN AccommodationType ON Accommodation.accom_type_id_fk = AccommodationType.id_pk
                 WHERE Accommodation.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $accomodation = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(EducationType.running_cost) AS runningCost
                 FROM EducationBuilding
                 LEFT JOIN EducationType ON EducationBuilding.educ_type_id_fk = EducationType.id_pk
                 WHERE EducationBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $education = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(BusinessType.running_cost) AS runningCost
                 FROM Business
                 LEFT JOIN BusinessType ON Business.bus_type_id_fk = BusinessType.id_pk
                 WHERE Business.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $business = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(RecreationType.running_cost) AS runningCost
                 FROM RecreationBuilding
                 LEFT JOIN RecreationType ON RecreationBuilding.rec_type_id_fk = RecreationType.id_pk
                 WHERE RecreationBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $recreation = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(ElectricityType.running_cost) AS runningCost
                 FROM ElectricityBuilding
                 LEFT JOIN ElectricityType ON ElectricityBuilding.elect_type_id_fk = ElectricityType.id_pk
                 WHERE ElectricityBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $electricity = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(WaterType.running_cost) AS runningCost
                 FROM WaterBuilding
                 LEFT JOIN WaterType ON WaterBuilding.water_type_id_fk = WaterType.id_pk
                 WHERE WaterBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $water = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(FoodType.running_cost) AS runningCost
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $food = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(FireType.running_cost) AS runningCost
                 FROM FireBuilding
                 LEFT JOIN FireType ON FireBuilding.fire_type_id_fk = FireType.id_pk
                 WHERE FireBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $fire = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(PoliceType.running_cost) AS runningCost
                 FROM PoliceBuilding
                 LEFT JOIN PoliceType ON PoliceBuilding.police_type_id_fk = PoliceType.id_pk
                 WHERE PoliceBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $police = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(HealthType.running_cost) AS runningCost
                 FROM HealthBuilding
                 LEFT JOIN HealthType ON HealthBuilding.health_type_id_fk = HealthType.id_pk
                 WHERE HealthBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $health = $stmt->fetchObject();

    $total = ($accomodation->runningCost)+($education->runningCost)+($business->runningCost)+($recreation->runningCost)+($electricity->runningCost)+($water->runningCost)+($food->runningCost)+($fire->runningCost)+($police->runningCost)+($health->runningCost);

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.building_costs =:sal
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':sal' => $total));

    chargeUser($total);
}

function payRetirementFunds()
{
    $dbConn = getConnection();
    $querySQL = "SELECT SUM(Citizen.retirement_fund) AS retirementFund
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $retirementFund = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.retirement_funds_paid =:sal
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':sal' => $retirementFund->retirementFund));

    chargeUser($retirementFund->retirementFund);
}

function payCitizens()
{
    $dbConn = getConnection();
    $querySQL = "SELECT SUM(Job.salary) AS salaries
                 FROM Job
                 WHERE Job.city_id_fk =:cityID AND Job.citizen_employed = 1
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $salaries = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.salaries_paid =:sal
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':sal' => $salaries->salaries));

    chargeUser($salaries->salaries);
}

function collectRent()
{
    $dbConn = getConnection();
    $querySQL = "SELECT SUM(Accommodation.rent) AS rent
                 FROM Room
                 LEFT JOIN Accommodation ON Room.accom_id_fk = Accommodation.id_pk
                 WHERE Room.city_id_fk =:cityID AND Room.inhabited = 1
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $temp = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.rent_collected =:rent
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':rent' => $temp->rent));

    rewardCoins($temp->rent);
}

function collectTax()
{
    $dbConn = getConnection();
    $querySQL = "SELECT SUM(Job.salary) AS salary
                 FROM Job
                 WHERE Job.city_id_fk =:cityID AND Job.citizen_employed = 1
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $salary = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT City.tax_rate
                 FROM City
                 WHERE City.id_pk =:cityID
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $taxRate = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.tax_collected =:tax
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':tax' => ($salary->salary * ($taxRate->tax_rate / 100))));

    rewardCoins($salary->salary * ($taxRate->tax_rate / 100));
}

function checkRetired()
{
    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.age, Citizen.retired, Citizen.job_id_fk, 
                        City.retirement_age
                 FROM Citizen
                 LEFT JOIN City ON Citizen.city_id_fk = City.id_pk
                 WHERE Citizen.city_id_fk =:cityID
                 ORDER BY Citizen.educational_status DESC, Citizen.wealth_status DESC, Citizen.age ASC
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while ($citizenInfo = $stmt->fetchObject())
    {
        if ((($citizenInfo->age) >= ($citizenInfo->retirement_age)))
        {
            if ($citizenInfo->retired == 0)
            {
                $dbConn = getConnection();
                $querySQL = "SELECT Job.salary
                                 FROM Job
                                 WHERE Job.id_pk =:jobID
                                ";
                $stmt3 = $dbConn->prepare($querySQL);
                $stmt3->execute(array(':jobID' => $citizenInfo->job_id_fk));
                $salary = $stmt3->fetchObject();

                if ($salary)
                {
                    $retiredFund = ($salary->salary)/2;
                }
                else
                {
                    $retiredFund = 0;
                }

                $dbConn = getConnection();
                $SQLupdate = "UPDATE Citizen
                              SET Citizen.retirement_fund =:retiredFund
                              WHERE Citizen.id_pk =:citizenID
                         ";
                $stmt2 = $dbConn->prepare($SQLupdate);
                $stmt2->execute(array(':citizenID' => $citizenInfo->id_pk, ':retiredFund' => $retiredFund));
            }

            $dbConn = getConnection();
            $SQLupdate = "UPDATE Citizen
                          SET Citizen.retired =:retiredBool
                          WHERE Citizen.id_pk =:citizenID
                         ";
            $stmt2 = $dbConn->prepare($SQLupdate);
            $stmt2->execute(array(':citizenID' => $citizenInfo->id_pk, ':retiredBool' => 1));
        }
    }
}

function canWork()
{
    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.age, Citizen.retired, 
                        City.retirement_age, Citizen.in_education
                 FROM Citizen
                 LEFT JOIN City ON Citizen.city_id_fk = City.id_pk
                 WHERE Citizen.city_id_fk =:cityID
                 ORDER BY Citizen.educational_status DESC, Citizen.wealth_status DESC, Citizen.age ASC
                ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while ($citizenInfo = $stmt->fetchObject())
    {
        if (($citizenInfo->age > 15) && ($citizenInfo->age < $citizenInfo->retirement_age) && ($citizenInfo->in_education == 0) && ($citizenInfo->retired == 0))
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE Citizen
                          SET Citizen.can_work = 1
                          WHERE Citizen.id_pk =:citizenID
                         ";
            $stmt2 = $dbConn->prepare($SQLupdate);
            $stmt2->execute(array(':citizenID' => $citizenInfo->id_pk));
        }
        else
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE Citizen
                          SET Citizen.can_work = 0
                          WHERE Citizen.id_pk =:citizenID
                         ";
            $stmt3 = $dbConn->prepare($SQLupdate);
            $stmt3->execute(array(':citizenID' => $citizenInfo->id_pk));
        }
    }
}

function getHomelessHapp()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.house_id_fk = 0
                 ";
    $stmt1 = $dbConn->prepare($SQLselect);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt1->fetchObject();
    $hCitizensCount = $citizensInfo->counter;

    $homelessRate = ($hCitizensCount/countPop())*100;

    $homelessHappiness = ((1-($homelessRate/0.1))*100);
    if ($homelessHappiness < 0)
    {
        $homelessHappiness = 0;
    }
    return $homelessHappiness;
}

function getWealthForRent($wealth)
{
    if ($wealth <= 0)
    {
        $rent = 250;
    }
    else if ($wealth <= 1)
    {
        $rent = 500;
    }
    else if ($wealth <= 2)
    {
        $rent = 1000;
    }
    else if ($wealth <= 3)
    {
        $rent = 2000;
    }
    else if ($wealth <= 4)
    {
        $rent = 5000;
    }
    return $rent;
}

function getPopulationIncrease()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 5)
        {
            $increase = $increase + 2;
        }
        else if ($rowObj->itemType == 10)
        {
            $increase = $increase + 3;
        }
        else if ($rowObj->itemType == 14)
        {
            $increase = $increase + 3;
        }
        else if ($rowObj->itemType == 20)
        {
            $increase = $increase + 5;
        }
        else if ($rowObj->itemType == 27)
        {
            $increase = $increase + 5;
        }
    }
    $increase = 1 + ($increase/100);
    return $increase;
}

function getBirthIncrease()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 12)
        {
            $increase = $increase + 1;
        }
    }
    return 1+ ($increase/100);
}

function citizensDifference()
{

    //Kill over 110s
    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk
                 FROM Citizen
                 WHERE Citizen.age > 110
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute();
    $i = 0;
    while ($rowObj = $stmt->fetchObject())
    {
        killCitizen($rowObj->id_pk);
        $i++;
    }
    $over110Die = $i;

    $birthIncrease = getBirthIncrease();

    $births = citizensBorn();
    $births = round(($births*$birthIncrease),0);
    for ($i = 0; $i < $births; $i++)
    {
        newCitizen('birth');
    }

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) AS counter
                 FROM Citizen
                 WHERE Citizen.house_id_fk = 0 AND Citizen.city_id_fk = :cityID
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $homeless = $stmt->fetchObject();

    $homelessLeaving = ($homeless->counter) - (floor((countPop() * 0.01)));
    if ($homelessLeaving < 0)
    {
        $homelessLeaving = 0;
    }

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk
                 FROM Citizen
                 WHERE Citizen.house_id_fk = 0 AND Citizen.city_id_fk = :cityID
                 LIMIT $homelessLeaving
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    while ($rowObj = $stmt->fetchObject())
    {
        killCitizen($rowObj->id_pk);
    }


    $arrivals = citizensArrive();
    $increase = getPopulationIncrease();
    $arrivals = round($arrivals * $increase,0);
    if ((countPop() + $arrivals < 100))
    {
        $arrivals = 100 - countPop();
    }
    for ($i = 0; $i < $arrivals; $i++)
    {
        newCitizen('arrive');
    }

    $leaving = citizensLeave();
    if (countPop() - $leaving < 100)
    {
        $leaving = countPop() - 100;
    }
    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk
                 FROM Citizen
                 WHERE Citizen.city_id_fk = :cityID
                 ORDER BY rand()
                 LIMIT $leaving
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    while ($rowObj = $stmt->fetchObject())
    {
        killCitizen($rowObj->id_pk);
    }

    $deaths = citizensDie($births);
    if (countPop() - $deaths < 100)
    {
        $deaths = countPop() - 100;
    }
    for ($i = 0; $i < $deaths; $i++)
    {
        $id = getCitizenToKill();
        killCitizen($id);
    }
    $totDie = $over110Die + $deaths;
    updateLastYearDiff($births, $arrivals, $leaving, $homelessLeaving, $totDie);

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.new_citizen_xp =:xp
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':xp' => (10*($births+$arrivals))));

    rewardXP(10*($births+$arrivals));
}

function updateLastYearDiff($births, $arrivals, $leaving, $homelessLeaving, $totDie)
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.born =:born, LastYear.arrived =:arrived, LastYear.leaving =:leaving,
                      LastYear.homeless =:homeless, LastYear.die =:die
                  WHERE LastYear.city_id_fk =:cityID
                  ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':born' => $births, ':arrived' => $arrivals, ':leaving' => $leaving,
                         ':homeless' => $homelessLeaving, ':die' => $totDie));

}

function getCitizenToKill()
{
    $rand = rand(1,10);
    if ($rand < 9)
    {
        $lowerAge = 79;
        $upperAge = 110;
    }
    else if ($rand = 9)
    {
        $lowerAge = 59;
        $upperAge = 79;
    }
    else if ($rand = 10)
    {
        $lowerAge = 0;
        $upperAge = 59;
    }

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk
                 FROM Citizen
                 WHERE Citizen.city_id_fk = :cityID AND Citizen.age > $lowerAge AND Citizen.age < $upperAge
                 ORDER BY rand()
                 LIMIT 1
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizen = $stmt->fetchObject();

    if (!$citizen)
    {
        $dbConn = getConnection();
        $querySQL = "SELECT Citizen.id_pk
                 FROM Citizen
                 WHERE Citizen.city_id_fk = :cityID
                 ORDER BY rand()
                 LIMIT 1
                    ";
        $stmt = $dbConn->prepare($querySQL);
        $stmt->execute(array(':cityID' => $_SESSION['cityID']));
        $citizen = $stmt->fetchObject();
    }
    return $citizen->id_pk;
}

function citizensLeave()
{
    $dbConn = getConnection();
    $querySQL = "SELECT AVG(Citizen.overall_happiness) AS avgHap
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $avgHap = $stmt->fetchObject();

    $rand = rand(95, 105);
    $rand = $rand/100;
    $citizensLeave = (($_SESSION['population'])*(((100-($avgHap->avgHap))/400)))*$rand;
    $citizensLeave = round($citizensLeave, 0);

    return $citizensLeave;
}

function killCitizen($citizenID)
{
    //Make citizen unemployed
    $dbConn = getConnection();
    $querySQL = "SELECT Job.id_pk
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.id_pk =:citizenID
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':citizenID' => $citizenID));
    $job = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Job
                  SET Job.citizen_employed = 0
                  WHERE Job.id_pk =:jobID
                                     ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':jobID' => $job->id_pk));

    //Make citizen homeless

    $dbConn = getConnection();
    $querySQL = "SELECT Room.id_pk
                 FROM Citizen
                 LEFT JOIN Room ON Citizen.house_id_fk = Room.id_pk
                 WHERE Citizen.id_pk =:citizenID
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':citizenID' => $citizenID));
    $room = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Room
                  SET Room.inhabited = 0
                  WHERE Room.id_pk =:roomID
                                     ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':roomID' => $room->id_pk));

    //Make citizen have no education
    $dbConn = getConnection();
    $querySQL = "SELECT EducationSpace.id_pk
                 FROM Citizen
                 LEFT JOIN EducationSpace ON Citizen.education_id_fk = EducationSpace.id_pk
                 WHERE Citizen.id_pk =:citizenID
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':citizenID' => $citizenID));
    $educ = $stmt->fetchObject();


    $dbConn = getConnection();
    $SQLupdate = "UPDATE EducationSpace
                  SET EducationSpace.taken = 0, EducationSpace.year = 0
                  WHERE EducationSpace.id_pk =:educID
                                     ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':educID' => $educ->id_pk));

    //Remove Citizen
    $dbConn = getConnection();
    $SQLdelete = "DELETE FROM Citizen
                  WHERE Citizen.id_pk =:citizenID
                                     ";
    $stmt1 = $dbConn->prepare($SQLdelete);
    $stmt1->execute(array(':citizenID' => $citizenID));
}

function citizensDie($births)
{
    $dbConn = getConnection();
    $querySQL = "SELECT AVG(Citizen.qol_happiness) AS avgQol
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                    ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $avgQol = $stmt->fetchObject();

    $dead = ($births * 0.84) * (1-(($avgQol->avgQol - 50)/50));
    $dead = round($dead, 0);
    return $dead;
}

function newCitizen($type)
{
    if ($type == 'birth')
    {
        $age = 0;
        $educationalStatus = 0;

        $dbConn = getConnection();
        $querySQL = "SELECT Citizen.id_pk, Citizen.wealth_status
                     FROM Citizen
                     WHERE Citizen.city_id_fk =:cityID AND Citizen.age BETWEEN 23 AND 37
                     ORDER BY rand()
                     LIMIT 1
                    ";
        $stmt = $dbConn->prepare($querySQL);
        $stmt->execute(array(':cityID' => $_SESSION['cityID']));
        $wealthStatusI = $stmt->fetchObject();
        $wealthStatus = $wealthStatusI->wealth_status;
    }
    else if ($type == 'arrive')
    {
        $age = rand(0,100);
        $educRand = rand(1,20);
        if ($age > 18)
        {
            if ($age > 20)
            {
                if ($age > 23)
                {
                    //For over 23s
                    if ($educRand < 3)
                    {
                        $educStatus = 3;
                    }
                    else if (($educRand > 2)&& ($educRand < 6))
                    {
                        $educStatus = 2;
                    }
                    else if (($educRand > 5)&& ($educRand < 16))
                    {
                        $educStatus = 1;
                    }
                    else
                    {
                        $educStatus = 0;
                    }
                }
                else
                {
                    //For over 20 - 23s
                    if ($educRand < 4)
                    {
                        $educStatus = 2;
                    }
                    else if (($educRand > 3)&& ($educRand < 13))
                    {
                        $educStatus = 1;
                    }
                    else
                    {
                        $educStatus = 0;
                    }
                }
            }
            else
            {
                //For over 18 - 20s
                if ($educRand < 10)
                {
                    $educStatus = 1;
                }
                else
                {
                    $educStatus = 0;
                }
            }
        }
        else
        {
            //for <=18s
            $educStatus = 0;
        }

        $educationalStatus = $educStatus;

        $wealthRand = rand(1,20);
        if ($wealthRand == 1)
        {
            $wealthStatus = 0;
        }
        else if (($wealthRand > 1) && ($wealthRand < 6))
        {
            $wealthStatus = 1;
        }
        else if (($wealthRand > 5) && ($wealthRand < 16))
        {
            $wealthStatus = 2;
        }
        else if (($wealthRand > 15) && ($wealthRand < 20))
        {
            $wealthStatus = 3;
        }
        else if ($wealthRand == 20)
        {
            $wealthStatus = 4;
        }
    }

    $dbConn = getConnection();
    $querySQL = "SELECT City.retirement_age
                 FROM City
                 WHERE City.id_pk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $cityInfo = $stmt->fetchObject();

    if ((($age) >= ($cityInfo->retirement_age)))
    {
        $retiredBool = 1;
    }
    else
    {
        $retiredBool = 0;
    }

    $dbConn = getConnection();
    $SQLinsert = "INSERT INTO Citizen(city_id_fk, age, wealth_status, educational_status, retired)
                          VALUES (:cityID, :age, :wealth, :educStatus, :retired)
                         ";
    $stmt = $dbConn->prepare($SQLinsert);
    $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':age' => $age, ':wealth' => $wealthStatus, ':educStatus' => $educationalStatus, ':retired' => $retiredBool));

}

function citizensArrive()
{
    $dbConn = getConnection();
    $querySQL = "SELECT AVG(Citizen.overall_happiness) AS avgHap
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $avgHap = $stmt->fetchObject();

    $rand = rand(95, 105);
    $rand = $rand/100;
    $citizenArrive = ((($_SESSION['population'])/4)*((($avgHap->avgHap)/100)/0.5))*($rand);
    $citizenArrive = round($citizenArrive, 0);

    return $citizenArrive;
}

function citizensBorn()
{
    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) AS counter
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.age BETWEEN 23 AND 37
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citInfo = $stmt->fetchObject();

    $rand = rand(7, 17);
    $rand = $rand/100;

    $citizensBorn = (($citInfo->counter)/2)*($rand);
    $citizensBorn = round($citizensBorn, 0);
    return $citizensBorn;
}

function overallHappiness()
{
    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.housing_happiness, Citizen.education_happiness, Citizen.job_happiness, 
                        Citizen.recreation_happiness, Citizen.health_happiness, Citizen.safety_happiness, Citizen.qol_happiness
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while ($rowobj = $stmt->fetchObject())
    {
        if ($_SESSION['level'] > 15)
        {
            $overallHappiness = (($rowobj->housing_happiness * 0.12)+($rowobj->education_happiness * 0.15)+($rowobj->job_happiness * 0.15)+($rowobj->recreation_happiness * 0.15)+($rowobj->health_happiness * 0.15)+($rowobj->safety_happiness * 0.18)+($rowobj->qol_happiness * 0.10));
        }
        else
        {
            $overallHappiness = (($rowobj->housing_happiness * 0.12)+($rowobj->education_happiness * 0.15)+($rowobj->job_happiness * 0.15)+($rowobj->recreation_happiness * 0.15)+($rowobj->health_happiness * 0.15)+($rowobj->qol_happiness * 0.10));
            $overallHappiness= (($overallHappiness)/82)*100;
        }


        $overallHappiness = round($overallHappiness, 0);

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET Citizen.overall_happiness =:overallHapp
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt5 = $dbConn->prepare($SQLupdate);
        $stmt5->execute(array(':overallHapp' => $overallHappiness, ':citizenID' => $rowobj->id_pk));
    }

}

function incrementEducYear()
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE EducationSpace
                  SET EducationSpace.year = (EducationSpace.year + 1)
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.taken = 1
                                     ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));

}

function educCheck()
{
    $dbConn = getConnection();
    $querySQL = "SELECT EducationSpace.id_pk, EducationSpace.educ_type, EducationSpace.year
                 FROM EducationSpace
                 WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.taken = 1";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $schoolGrad = 0;
    $collegeGrad = 0;
    $uniGrad = 0;

    while ($educRow =  $stmt->fetchObject())
    {
        $reset = false;

        $dbConn = getConnection();
        $querySQL = "SELECT Citizen.id_pk
                     FROM Citizen
                     WHERE Citizen.city_id_fk =:cityID AND Citizen.education_id_fk =:educID";
        $stmt1 = $dbConn->prepare($querySQL);
        $stmt1->execute(array(':cityID' => $_SESSION['cityID'], ':educID' => $educRow->id_pk));
        $studentCheck = $stmt1->fetchObject();
        if ($studentCheck)
        {
            if ($educRow->educ_type == 0)
            {
                if ($educRow->year > 12)
                {
                    $schoolGrad++;
                    $reset = true;
                }
            }
            else if ($educRow->educ_type == 1)
            {
                if ($educRow->year > 2)
                {
                    $collegeGrad++;
                    $reset = true;
                }
            }
            else if ($educRow->educ_type == 2)
            {
                if ($educRow->year > 4)
                {
                    $uniGrad++;
                    $reset = true;
                }
            }
        }
        else
        {
            $reset = true;
        }

        if ($reset == true)
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE EducationSpace
                          SET EducationSpace.taken = 0, EducationSpace.year = 0
                          WHERE EducationSpace.id_pk =:educID
                                     ";
            $stmt5 = $dbConn->prepare($SQLupdate);
            $stmt5->execute(array(':educID' => $educRow->id_pk));

            if ($studentCheck)
            {
                $educStatus = ($educRow->educ_type) + 1;

                $dbConn = getConnection();
                $SQLupdate = "UPDATE Citizen
                          SET Citizen.in_education = 0, Citizen.education_id_fk = 0, Citizen.educational_status =:educStatus
                          WHERE Citizen.id_pk =:citizenID
                                     ";
                $stmt5 = $dbConn->prepare($SQLupdate);
                $stmt5->execute(array(':citizenID' => $studentCheck->id_pk, ':educStatus' => $educStatus));
            }
        }

    }

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET school_grad=:school, college_grad=:college, uni_grad=:uni
                  WHERE LastYear.city_id_fk =:cityID
                                     ";
    $stmt5 = $dbConn->prepare($SQLupdate);
    $stmt5->execute(array(':cityID' => $_SESSION['cityID'], ':school' => $schoolGrad, ':college' => $collegeGrad, ':uni' => $uniGrad));
}

function ageCitizens()
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE Citizen
                      SET Citizen.age = (Citizen.age + 1)
                    WHERE Citizen.city_id_fk =:cityID
                ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
}



function deteriorateHouses()
{

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Accommodation
                  SET Accommodation.quality = (Accommodation.quality - 1)
                  WHERE Accommodation.city_id_fk =:cityID AND Accommodation.quality > 0
        ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
}

function deteriorateFire()
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE FireBuilding
                  SET FireBuilding.quality = (FireBuilding.quality - 1)
                  WHERE FireBuilding.city_id_fk =:cityID AND FireBuilding.quality > 0
        ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
}

function deterioratePolice()
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE PoliceBuilding
                  SET PoliceBuilding.quality = (PoliceBuilding.quality - 1)
                  WHERE PoliceBuilding.city_id_fk =:cityID AND PoliceBuilding.quality > 0
        ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
}

function deteriorateHealth()
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE HealthBuilding
                  SET HealthBuilding.quality = (HealthBuilding.quality - 1)
                  WHERE HealthBuilding.city_id_fk =:cityID AND HealthBuilding.quality > 0
        ";
    $stmt1 = $dbConn->prepare($SQLupdate);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
}

function jobAssingment()
{
    canWork();

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID";
    $stmt1 = $dbConn->prepare($querySQL);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));

    while ($rowObj = $stmt1->fetchObject())
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET job_id_fk = 0
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt4 = $dbConn->prepare($SQLupdate);
        $stmt4->execute(array(':citizenID' => $rowObj->id_pk));
    }

    $dbConn = getConnection();
    $querySQL = "SELECT Job.id_pk, Job.educ_required
                 FROM Job
                 WHERE Job.city_id_fk =:cityID
                 ORDER BY Job.educ_required DESC, Job.job_industry ASC, Job.quality DESC, Job.Salary DESC";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while ($jobRow =  $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $querySQL = "SELECT Citizen.id_pk, Citizen.educational_status
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.can_work = 1 AND Citizen.job_id_fk = 0
                 ORDER BY Citizen.educational_status DESC";
        $stmt3 = $dbConn->prepare($querySQL);
        $stmt3->execute(array(':cityID' => $_SESSION['cityID']));

        $occupentFound = false;
        while ($citizenRow = $stmt3->fetchObject())
        {
            if ($occupentFound == false)
            {

                    if ($citizenRow->educational_status >= $jobRow->educ_required)
                    {
                        $occupentFound = true;

                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE Citizen
                                      SET job_id_fk =:jobID
                                      WHERE Citizen.id_pk =:citizenID
                                     ";
                        $stmt4 = $dbConn->prepare($SQLupdate);
                        $stmt4->execute(array(':citizenID' => $citizenRow->id_pk, ':jobID' => $jobRow->id_pk));

                        $dbConn = getConnection();
                        $SQLupdate = "UPDATE Job
                                      SET citizen_employed = 1
                                      WHERE Job.id_pk =:jobID
                                     ";
                        $stmt4 = $dbConn->prepare($SQLupdate);
                        $stmt4->execute(array(':jobID' => $jobRow->id_pk));
                    }

            }
        }
        if ($occupentFound == false)
        {
            $dbConn = getConnection();
            $SQLupdate = "UPDATE Job
                          SET citizen_employed = 0
                          WHERE Job.id_pk =:jobID
                                     ";
            $stmt4 = $dbConn->prepare($SQLupdate);
            $stmt4->execute(array(':jobID' => $jobRow->id_pk));
        }

    }

    jobHappiness();
}

function getWealth($jobRole, $salA, $salB, $salC)
{
    if ($jobRole == 'a')
    {
        $salary = $salA;
    }
    if ($jobRole == 'b')
    {
        $salary = $salB;
    }
    if ($jobRole == 'c')
    {
        $salary = $salC;
    }

    if ($salary < 751)
    {
        $wealth = 0;
    }
    if ($salary > 750 && $salary < 1501)
    {
        $wealth = 1;
    }
    if ($salary > 1500 && $salary < 3001)
    {
        $wealth = 2;
    }
    if ($salary > 3000 && $salary < 5001)
    {
        $wealth = 3;
    }
    if ($salary > 5000)
    {
        $wealth = 4;
    }

    return $wealth;
}

function jobHappiness()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk = 0 AND Citizen.can_work = 1
                 ";
    $stmt1 = $dbConn->prepare($SQLselect);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt1->fetchObject();
    $unECitizensCount = $citizensInfo->counter;

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.can_work = 1
                 ";
    $stmt6 = $dbConn->prepare($SQLselect);
    $stmt6->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo2 = $stmt6->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.educational_status, Citizen.job_id_fk, Citizen.can_work, Citizen.retired, 
                        Citizen.retirement_fund
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));


    $unEmpRate = ($unECitizensCount/$citizensInfo2->counter)*100;

    $emplomentHappiness = 100-$unEmpRate;

    while ($rowobj = $stmt->fetchObject())
    {
        if (!($rowobj->job_id_fk == 0))
        {
            $dbConn = getConnection();
            $querySQL = "SELECT Job.quality, Job.salary
                         FROM Job
                         WHERE Job.id_pk =:jobID
                         ";
            $stmt9 = $dbConn->prepare($querySQL);
            $stmt9->execute(array(':jobID' => $rowobj->job_id_fk));
            $jobInfo = $stmt9->fetchObject();

            $jobQuality = $jobInfo->quality;
            $expectedSalary = getSalary($rowobj->educational_status);
            $salHappiness = ((($jobInfo->salary)/($expectedSalary))/2)*100;
            if ($salHappiness > 100)
            {
                $salHappiness = 100;
            }
            $jobHappiness = round((($jobQuality*0.4)+($salHappiness*0.4)+($emplomentHappiness*0.2)),0);

        }
        else
        {
            if ($rowobj->can_work == 0)
            {
                $jobQuality = 50;
                if ($rowobj->retired == 1)
                {
                    $expectedSalary = getSalary($rowobj->educational_status);
                    $salHappiness = ((($rowobj->retirement_fund)/($expectedSalary))/2)*100;
                    if ($salHappiness > 100)
                    {
                        $salHappiness = 100;
                    }
                }
                else
                {
                    $salHappiness = 50;
                }
                $jobHappiness = round((($jobQuality*0.4)+($salHappiness*0.4)+($emplomentHappiness*0.2)),0);
            }
            else
            {
                $jobHappiness = round($emplomentHappiness*0.2, 0);
            }
        }
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET job_happiness =:jobHappy
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt4 = $dbConn->prepare($SQLupdate);
        $stmt4->execute(array(':citizenID' => $rowobj->id_pk, ':jobHappy' => $jobHappiness));

    }
    overallHappiness();
}

function educAssignmentCitizen($citizenID, $educType)
{
    $dbConn = getConnection();
    $querySQL = "SELECT EducationSpace.id_pk
                 FROM EducationSpace
                 WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.taken = 0 AND EducationSpace.educ_type = :educType
                 LIMIT 1";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID'], ':educType' => $educType));
    $educSpace = $stmt->fetchObject();


    if ($educSpace)
    {
        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET in_education = 1, education_id_fk =:educID
                      WHERE Citizen.id_pk =:citizenID
                               ";
        $stmt5 = $dbConn->prepare($SQLupdate);
        $stmt5->execute(array(':citizenID' => $citizenID, ':educID' => $educSpace->id_pk));

        $dbConn = getConnection();
        $SQLupdate = "UPDATE EducationSpace
                      SET taken = 1, year = 1
                      WHERE EducationSpace.id_pk =:educID
                                     ";
        $stmt5 = $dbConn->prepare($SQLupdate);
        $stmt5->execute(array(':educID' => $educSpace->id_pk));

        return true;
    }
    else
    {
        return false;
    }
}

function educAssignment()
{
    $dbConn = getConnection();
    $querySQL = "SELECT EducationSpace.id_pk, EducationSpace.educ_type
                 FROM EducationSpace
                 WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.taken = 0";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while ($educRow =  $stmt->fetchObject())
    {
        $dbConn = getConnection();
        $querySQL = "SELECT Citizen.id_pk, Citizen.age, Citizen.educational_status
                     FROM Citizen
                     WHERE Citizen.city_id_fk =:cityID AND Citizen.in_education = 0
                     ORDER BY Citizen.age ASC";
        $stmt3 = $dbConn->prepare($querySQL);
        $stmt3->execute(array(':cityID' => $_SESSION['cityID']));

        $studentFound = false;
        while ($citizenRow = $stmt3->fetchObject())
        {
            if ($studentFound == false)
            {
                if (($citizenRow->educational_status) < ($educRow->educ_type + 1))
                {
                    if ($educRow->educ_type == 0)
                    {
                        if (($citizenRow->age) > 5)
                        {
                            $studentFound = true;

                        }
                    }
                    else if ($educRow->educ_type == 1)
                    {
                        if (($citizenRow->educational_status) == 1)
                        {
                            $studentFound = true;


                            $dbConn = getConnection();
                            $SQLupdate = "UPDATE Citizen
                                      SET in_education = 1, education_id_fk =:educID
                                      WHERE Citizen.id_pk =:citizenID
                                     ";
                            $stmt5 = $dbConn->prepare($SQLupdate);
                            $stmt5->execute(array(':citizenID' => $citizenRow->id_pk, ':educID' => $educRow->id_pk));

                            $dbConn = getConnection();
                            $SQLupdate = "UPDATE EducationSpace
                                      SET taken = 1, year = 1
                                      WHERE EducationSpace.id_pk =:educID
                                     ";
                            $stmt5 = $dbConn->prepare($SQLupdate);
                            $stmt5->execute(array(':educID' => $educRow->id_pk));

                        }
                    }
                    else if ($educRow->educ_type == 2)
                    {
                        if (($citizenRow->educational_status) == 2)
                        {
                            $studentFound = true;


                            $dbConn = getConnection();
                            $SQLupdate = "UPDATE Citizen
                                      SET in_education = 1, education_id_fk =:educID
                                      WHERE Citizen.id_pk =:citizenID
                                     ";
                            $stmt5 = $dbConn->prepare($SQLupdate);
                            $stmt5->execute(array(':citizenID' => $citizenRow->id_pk, ':educID' => $educRow->id_pk));

                            $dbConn = getConnection();
                            $SQLupdate = "UPDATE EducationSpace
                                      SET taken = 1, year = 1
                                      WHERE EducationSpace.id_pk =:educID
                                     ";
                            $stmt5 = $dbConn->prepare($SQLupdate);
                            $stmt5->execute(array(':educID' => $educRow->id_pk));

                        }
                    }
                }
            }
        }
    }

    educHappiness();
    canWork();
    jobAssingment();
}

function educHappiness()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 0 
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $noneEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $schoolEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 2 
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $collEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 3
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $uniEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS counter
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 0
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $schoolSpaces = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS counter
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $collegeSpaces = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS counter
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 2
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $universitySpaces = $stmt->fetchObject();


    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $population = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.educational_status
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmtT = $dbConn->prepare($querySQL);
    $stmtT->execute(array(':cityID' => $_SESSION['cityID']));

    $popCanBeEduc = ($population->counter)-($uniEducated->counter);

    $totalSpaces = ($schoolSpaces->counter)+($collegeSpaces->counter)+($universitySpaces->counter);

    $popEducAvailble = (($totalSpaces)/($popCanBeEduc)*100);

    while ($rowobj = $stmtT->fetchObject())
    {


        if ($rowobj->educational_status == 0)
        {
            $educStatusHappiness = 0;
            $educAvailble = (($schoolSpaces->counter)/($noneEducated->counter)*100);
        }
        else if ($rowobj->educational_status == 1)
        {
            $educStatusHappiness = 33;
            $educAvailble = (($collegeSpaces->counter)/($schoolEducated->counter)*100);
        }
        else if ($rowobj->educational_status == 2)
        {
            $educStatusHappiness = 66;
            $educAvailble = (($universitySpaces->counter)/($collEducated->counter)*100);
        }
        else if ($rowobj->educational_status == 3)
        {
            $educStatusHappiness = 100;
            $educAvailble = 100;
        }

        if ($educAvailble > 100)
        {
            $educAvailble = 100;
        }

        if ($popEducAvailble > 100)
        {
            $popEducAvailble = 100;
        }

        $educHappiness = ($educStatusHappiness * 0.3)+($educAvailble * 0.5)+($popEducAvailble * 0.2);
        $educHappiness = round($educHappiness, 0);

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET education_happiness =:educHappy
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt4 = $dbConn->prepare($SQLupdate);
        $stmt4->execute(array(':citizenID' => $rowobj->id_pk, ':educHappy' => $educHappiness));
    }
    overallHappiness();
}

function calcRecRev()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT RecreationBuilding.id_pk, RecreationType.base_revenue
                  FROM RecreationBuilding
                  LEFT JOIN RecreationType ON RecreationBuilding.rec_type_id_fk = RecreationType.id_pk
                  WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    while ($rowObj = $stmt->fetchObject())
    {
        $revenue = ($rowObj->base_revenue)*(countPop());

        $dbConn = getConnection();
        $SQLupdate = "UPDATE RecreationBuilding
                      SET yearly_revenue =:rev
                      WHERE RecreationBuilding.id_pk =:bussID
                                     ";
        $stmt3 = $dbConn->prepare($SQLupdate);
        $stmt3->execute(array(':bussID' => $rowObj->id_pk, ':rev' => $revenue));
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT SUM(RecreationBuilding.yearly_revenue) AS revenue
                  FROM RecreationBuilding
                  WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt4 = $dbConn->prepare($SQLselect);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID']));
    $revenue= $stmt4->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.rec_revenue =:rev
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':rev' => $revenue->revenue));

    rewardCoins($revenue->revenue);
}

function calcBusinessRev()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT Business.id_pk, BusinessType.base_revenue, BusinessType.job_a_quantity, BusinessType.job_b_quantity, BusinessType.job_c_quantity
                  FROM Business
                  LEFT JOIN BusinessType ON Business.bus_type_id_fk = BusinessType.id_pk
                  WHERE Business.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));

    $multiplier = checkRevIncreases();
    $multiplier = 1 + ($multiplier/100);

    while ($rowObj = $stmt->fetchObject())
    {

        $dbConn = getConnection();
        $SQLselect = "SELECT COUNT(Job.id_pk) AS workers
                      FROM Job
                      WHERE Job.city_id_fk =:cityID AND Job.citizen_employed = 1 AND Job.job_industry = 3 AND Job.employer_id_fk = :empID
                     ";
        $stmt1 = $dbConn->prepare($SQLselect);
        $stmt1->execute(array(':cityID' => $_SESSION['cityID'], ':empID' => $rowObj->id_pk));
        $workers = $stmt1->fetchObject();


        $jobs = ($rowObj->job_a_quantity)+($rowObj->job_b_quantity)+($rowObj->job_c_quantity);
        $employementRate = (($workers->workers)/$jobs);
        $randMulit = rand(95, 105);

        $revenue = (($rowObj->base_revenue)*($employementRate)*($randMulit/100)*$multiplier);

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Business
                      SET yearly_revenue =:rev
                      WHERE Business.id_pk =:bussID
                                     ";
        $stmt3 = $dbConn->prepare($SQLupdate);
        $stmt3->execute(array(':bussID' => $rowObj->id_pk, ':rev' => $revenue));
    }

    $dbConn = getConnection();
    $SQLselect = "SELECT SUM(Business.yearly_revenue) AS revenue
                  FROM Business
                  WHERE Business.city_id_fk =:cityID
                 ";
    $stmt4 = $dbConn->prepare($SQLselect);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID']));
    $revenue= $stmt4->fetchObject();

    $dbConn = getConnection();
    $SQLupdate = "UPDATE LastYear
                  SET LastYear.bus_revenue =:rev
                  WHERE LastYear.city_id_fk =:cityID
                         ";
    $stmt2 = $dbConn->prepare($SQLupdate);
    $stmt2->execute(array(':cityID' => $_SESSION['cityID'], ':rev' => $revenue->revenue));

    rewardCoins($revenue->revenue);
}

function educHolder()
{
    echo "<div class='mainGameContainer'>
                <div class='housesTopContainer'>
                    <ul class='housesButtContainer'>
                        <li class='housesButt' id='housesInfo'><a href='educationInfo.php'>Education Info</a></li>
                        <li class='housesButt' id='buyHouses'><a href='buyEducation.php' id='noteRed'>Buy Education</a></li>
                        <li class='housesButt' id='myHouses'><a href='myEducation.php'>My Education</a></li>
                    </ul>
                </div>";


    $dbConn = getConnection();
    $SQLselect = "SELECT AVG(Citizen.education_happiness) AS avgHappiness
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt->fetchObject();

    $educHappiness = round($citizensInfo->avgHappiness, 0);

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationBuilding.id_pk) AS educCount
                  FROM EducationBuilding
                  WHERE EducationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $educDetail = $stmt->fetchObject();
    $educCount = $educDetail->educCount;

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS schoolCount
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 0 AND EducationSpace.taken = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $inSchoolCount = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS schoolCount
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 1 AND EducationSpace.taken = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $inColCount = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(EducationSpace.id_pk) AS schoolCount
                  FROM EducationSpace
                  WHERE EducationSpace.city_id_fk =:cityID AND EducationSpace.educ_type = 2 AND EducationSpace.taken = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $inUniCount = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 0 
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $noneEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 1
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $schoolEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 2 
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $collEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 3
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $uniEducated = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 0 AND Citizen.age > 6 AND Citizen.in_education = 0
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $schoolWaiting = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 1 AND Citizen.in_education = 0
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $collegeAwaiting = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(Citizen.id_pk) AS counter
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID AND Citizen.educational_status = 2 AND Citizen.in_education = 0
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $uniAwaiting = $stmt->fetchObject();

    echo "      <div class='housesHolder'>
                    <div class='myHousesLeftBar'>
                        <p style='margin-top: 10px; margin-left: 10px;'>View and manage all the current educational buildings in your city from this page.</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Educational Happiness: {$educHappiness}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Total Educational Buildings: {$educCount}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Citizens In School (Awaiting): {$inSchoolCount->schoolCount} ({$schoolWaiting->counter})</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Citizens In College (Awaiting): {$inColCount->schoolCount} ({$collegeAwaiting->counter})</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Citizens In University (Awaiting): {$inUniCount->schoolCount} ({$uniAwaiting->counter})</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>None Educated Citizens: {$noneEducated->counter}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>School Educated Citizens: {$schoolEducated->counter}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>College Educated Citizens: {$collEducated->counter}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>University Educated Citizens: {$uniEducated->counter}</p>


                    </div>";
}

function getEducImage($educTypeID)
{
    if ($educTypeID == 1)
    {
        $imagePath = 'images/small_school_icon.png';
    }
    if ($educTypeID == 2)
    {
        $imagePath = 'images/small_school_icon.png';
    }
    if ($educTypeID == 3)
    {
        $imagePath = 'images/small_school_icon.png';
    }
    if ($educTypeID == 4)
    {
        $imagePath = 'images/small_school_icon.png';
    }
    if ($educTypeID == 5)
    {
        $imagePath = 'images/college_icon.png';
    }
    if ($educTypeID == 6)
    {
        $imagePath = 'images/college_icon.png';
    }
    if ($educTypeID == 7)
    {
        $imagePath = 'images/college_icon.png';
    }
    if ($educTypeID == 8)
    {
        $imagePath = 'images/college_icon.png';
    }
    if ($educTypeID == 9)
    {
        $imagePath = 'images/university_icon.png';
    }
    if ($educTypeID == 10)
    {
        $imagePath = 'images/university_icon.png';
    }
    if ($educTypeID == 11)
    {
        $imagePath = 'images/university_icon.png';
    }
    if ($educTypeID == 12)
    {
        $imagePath = 'images/university_icon.png';
    }

    return $imagePath;
}

function recHolder()
{
    echo "<div class='mainGameContainer'>
                <div class='housesTopContainer'>
                    <ul class='housesButtContainer'>
                        <li class='housesButt' id='housesInfo'><a href='recInfo.php'>Recreation Info</a></li>
                        <li class='housesButt' id='buyHouses'><a href='buyRecreation.php' id='noteRed'>Buy Recreation</a></li>
                        <li class='housesButt' id='myHouses'><a href='myRecreation.php'>My Recreation</a></li>
                    </ul>
                </div>";


    $dbConn = getConnection();
    $SQLselect = "SELECT AVG(Citizen.recreation_happiness) AS avgHappiness
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt->fetchObject();

    $recHappiness = round($citizensInfo->avgHappiness, 0);

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(RecreationBuilding.id_pk) AS recCount
                  FROM RecreationBuilding
                  WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $recDetail = $stmt->fetchObject();
    $recCount = $recDetail->recCount;

    $dbConn = getConnection();
    $SQLselect = "SELECT SUM(RecreationType.capacity) AS recCapacity, COUNT(DISTINCT RecreationBuilding.rec_type_id_fk) AS recTypesInCity
                  FROM RecreationBuilding
                  LEFT JOIN RecreationType ON RecreationBuilding.rec_type_id_fk = RecreationType.id_pk
                  WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $recCapacity = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as job
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk > 0 AND Job.job_industry = 2
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $recWork = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(DISTINCT RecreationType.id_pk) AS recTypesInGame
                  FROM RecreationType
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $totalRecTypes = $stmt->fetchObject();

    $recVariety = round((($recCapacity->recTypesInCity)/($totalRecTypes->recTypesInGame)*100),0);

    echo "      <div class='housesHolder'>
                    <div class='myHousesLeftBar'>
                        <p style='margin-top: 10px; margin-left: 10px;'>View and manage all the current recreational spaces in your city from this page.</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Recreational Happiness: {$recHappiness}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Recreational Variety: {$recVariety}%</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Total Recreational Buildings: {$recCount}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Recreation Capacity: {$recCapacity->recCapacity}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Total Recreation Workers: {$recWork->job}</p>


                    </div>";
}

function recHappiness()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT SUM(RecreationType.capacity) AS recCapacity, COUNT(DISTINCT RecreationBuilding.rec_type_id_fk) AS recTypesInCity
                  FROM RecreationBuilding
                  LEFT JOIN RecreationType ON RecreationBuilding.rec_type_id_fk = RecreationType.id_pk
                  WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $recCapacity = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(DISTINCT RecreationType.id_pk) AS recTypesInGame
                  FROM RecreationType
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $totalRecTypes = $stmt->fetchObject();

    $varietyHappiness = (($recCapacity->recTypesInCity)/($totalRecTypes->recTypesInGame)*100);

    $avalHappiness = (($recCapacity->recCapacity)/(($_SESSION['population'])/10))*100;

    if ($avalHappiness > 100)
    {
        $avalHappiness = 100;
    }

    $increase = getRecIncrease();

    $recHappiness = round(((($avalHappiness*0.6)+($varietyHappiness*0.4))+$increase),0);
    if ($recHappiness > 100)
    {
        $recHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Citizen
                  SET recreation_happiness =:recHappy
                  WHERE Citizen.city_id_fk =:cityID
                                     ";
    $stmt4 = $dbConn->prepare($SQLupdate);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID'], ':recHappy' => $recHappiness));

    overallHappiness();
}

function getRecIncrease()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 3)
        {
            $increase++;
        }
        else if ($rowObj->itemType == 18)
        {
            $increase = $increase + 3;
        }
    }
    return $increase;
}

function citizensHolder()
{
    echo "<div class='mainGameContainer'>";


    echo "      <div class='housesHolder' style='margin-top: 20px'>
                    <div class='myHousesLeftBar'>
                        <ul class='citizenTabHolder'>
                            <li class='citizenTabButt' id='overview'><a href=\"citizensOverview.php\">Overview</a></li>
                            <li class='citizenTabButt' id='demographic'><a href=\"demographic.php\">Demographic</a></li>
                        </ul>
                    </div>";
}

function getRecImage($recTypeID)
{
    if ($recTypeID == 1)
    {
        $imagePath = 'images/recreation_icon.png';
    }
    if ($recTypeID == 2)
    {
        $imagePath = 'images/recreation_icon.png';
    }
    if ($recTypeID == 3)
    {
        $imagePath = 'images/recreation_icon.png';
    }
    if ($recTypeID == 4)
    {
        $imagePath = 'images/recreation_icon.png';
    }
    if ($recTypeID == 5)
    {
        $imagePath = 'images/comm_centre_icon.png';
    }
    if ($recTypeID == 6)
    {
        $imagePath = 'images/cinema_icon.png';
    }
    if ($recTypeID == 7)
    {
        $imagePath = 'images/theatre_icon.png';
    }
    if ($recTypeID == 8)
    {
        $imagePath = 'images/football_icon.png';
    }
    if ($recTypeID == 9)
    {
        $imagePath = 'images/art_gallery_icon.png';
    }
    if ($recTypeID == 10)
    {
        $imagePath = 'images/zoo_icon.png';
    }

    return $imagePath;
}

function citizensHappiness()
{
    $dbConn = getConnection();
    $SQLselect = "SELECT SUM(RecreationType.capacity) AS recCapacity, COUNT(DISTINCT RecreationBuilding.rec_type_id_fk) AS recTypesInCity
                  FROM RecreationBuilding
                  LEFT JOIN RecreationType ON RecreationBuilding.rec_type_id_fk = RecreationType.id_pk
                  WHERE RecreationBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $recCapacity = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLselect = "SELECT COUNT(DISTINCT RecreationType.id_pk) AS recTypesInGame
                  FROM RecreationType
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $totalRecTypes = $stmt->fetchObject();

    $varietyHappiness = (($recCapacity->recTypesInCity)/($totalRecTypes->recTypesInGame)*100);

    $avalHappiness = (($recCapacity->recCapacity)/(($_SESSION['population'])/10))*100;

    if ($avalHappiness > 100)
    {
        $avalHappiness = 100;
    }

    $recHappiness = ($avalHappiness*0.6)+($varietyHappiness*0.4);

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Citizen
                  SET recreation_happiness =:recHappy
                  WHERE Citizen.city_id_fk =:cityID
                                     ";
    $stmt4 = $dbConn->prepare($SQLupdate);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID'], ':recHappy' => $recHappiness));

    overallHappiness();
}

function utilHolder()
{
    echo "<div class='mainGameContainer'>
                <div class='housesTopContainer'>
                    <ul class='housesButtContainer'>
                        <li class='housesButt' id='housesInfo'><a href='utilitiesInfo.php'>Utilities Info</a></li>
                        <li class='housesButt' id='buyHouses'><a href='buyUtilities.php' id='noteRed'>Buy Utilities</a></li>
                        <li class='housesButt' id='myHouses'><a href='myUtilities.php'>My Utilities</a></li>
                    </ul>
                </div>";


    $dbConn = getConnection();
    $SQLselect = "SELECT AVG(Citizen.health_happiness) AS avgHealthHappiness, AVG(Citizen.qol_happiness) AS avgQolHappiness, AVG(Citizen.safety_happiness) AS avgSafetyHappiness
                  FROM Citizen
                  WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($SQLselect);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $citizensInfo = $stmt->fetchObject();

    $healthHappiness = round($citizensInfo->avgHealthHappiness, 0);
    $qolHappiness = round($citizensInfo->avgQolHappiness, 0);
    $safetyHappiness = round($citizensInfo->avgSafetyHappiness, 0);

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Citizen.id_pk) as job
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.city_id_fk =:cityID AND Citizen.job_id_fk > 0 AND Job.job_industry = 1
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $utilityWork = $stmt->fetchObject();


    echo "      <div class='housesHolder'>
                    <div class='myHousesLeftBar'>
                        <p style='margin-top: 10px; margin-left: 10px;'>View and manage all the current utilities in your city from this page.</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Health Happiness: {$healthHappiness}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Quality of Life Happiness: {$qolHappiness}</p>
                        ";
    if ($_SESSION['level']>=15)
    {
        echo "                        <p style='margin-top: 10px; margin-left: 10px;'>Safety Happiness: {$safetyHappiness}</p>";
    }

    $dbConn = getConnection();
    $querySQL = "SELECT SUM(ElectricityType.elect_output) as electOutput
                 FROM ElectricityBuilding
                 LEFT JOIN ElectricityType ON ElectricityBuilding.elect_type_id_fk = ElectricityType.id_pk
                 WHERE ElectricityBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $electOutput = $stmt->fetchObject();

    $expected = $_SESSION['population']*10;


    $dbConn = getConnection();
    $querySQL = "SELECT SUM(WaterType.water_output) as waterOutput
                 FROM WaterBuilding
                 LEFT JOIN WaterType ON WaterBuilding.water_type_id_fk = WaterType.id_pk
                 WHERE WaterBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $waterOutput = $stmt->fetchObject();

    $waterExpected = $_SESSION['population']*15;

    $dbConn = getConnection();
    $SQLquery = "SELECT SUM(FoodType.output_a_quantity) AS foodOutputA, SUM(FoodType.output_b_quantity) AS foodOutputB, SUM(FoodType.output_c_quantity) AS foodOutputC
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(DISTINCT FoodType.output_a_name) AS aTypes, 
                        COUNT(DISTINCT FoodType.output_b_name) AS bTypes, 
                        COUNT(DISTINCT FoodType.output_c_name) AS cTypes
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails2 = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(DISTINCT FoodType.output_a_name) AS aTypes, 
                        COUNT(DISTINCT FoodType.output_b_name) AS bTypes, 
                        COUNT(DISTINCT FoodType.output_c_name) AS cTypes
                 FROM FoodType
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array());
    $foodDetails3 = $stmt->fetchObject();


    $foodOutput = ($foodDetails->foodOutputA)+($foodDetails->foodOutputB)+($foodDetails->foodOutputC);
    $foodTypes = ($foodDetails2->aTypes)+($foodDetails2->bTypes)+($foodDetails2->cTypes);
    $foodTypesInGame = ($foodDetails3->aTypes)+($foodDetails3->bTypes)+($foodDetails3->cTypes);

    $foodExpected = $_SESSION['population']*25;
    $foodVariety = round((($foodTypes/$foodTypesInGame)*100),0);

    $dbConn = getConnection();
    $SQLquery = "SELECT AVG(FireBuilding.quality) AS fireQuality
                 FROM FireBuilding
                 WHERE FireBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $fireQuality = $stmt->fetchObject();
    $fireQuality = round($fireQuality->fireQuality,0);

    $dbConn = getConnection();
    $SQLquery = "SELECT AVG(PoliceBuilding.quality) AS policeQuality
                 FROM PoliceBuilding
                 WHERE PoliceBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $policeQuality = $stmt->fetchObject();
    $policeQuality = round($policeQuality->policeQuality,0);

    $dbConn = getConnection();
    $SQLquery = "SELECT AVG(HealthBuilding.quality) AS healthQuality, SUM(HealthType.capacity) AS healthCapacity
                 FROM HealthBuilding
                 LEFT JOIN HealthType ON HealthBuilding.health_type_id_fk = HealthType.id_pk
                 WHERE HealthBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $health = $stmt->fetchObject();
    $healthQuality = round($health->healthQuality,0);

    echo "              <p style='margin-top: 10px; margin-left: 10px;'>Total Utility Workers: {$utilityWork->job}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Electricity Output / Expected: {$electOutput->electOutput}kW / {$expected}kW</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Water Output / Expected: {$waterOutput->waterOutput}L / {$waterExpected}L</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Food Output / Expected: {$foodOutput} / {$foodExpected}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Food Variety: {$foodVariety}%</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Fire Brigade Quality: {$fireQuality}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Police Force Quality: {$policeQuality}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Healthcare Quality: {$healthQuality}</p>
                        <p style='margin-top: 10px; margin-left: 10px;'>Healthcare Capacity: {$health->healthCapacity}</p>
                    </div>";
}

function utilHappiness()
{
    healthHappiness();
    qolHappiness();
    if ($_SESSION['level'] >= 15)
    {
        safetyHappiness();
    }
    overallHappiness();
}

function getHealthHapp()
{
    $dbConn = getConnection();
    $querySQL = "SELECT AVG(HealthBuilding.quality) AS healthQuality, COUNT(DISTINCT HealthBuilding.health_type_id_fk) AS healthTypesInCity, SUM(HealthType.capacity) AS healthCapacity
                 FROM HealthBuilding
                 LEFT JOIN HealthType ON HealthBuilding.health_type_id_fk = HealthType.id_pk
                 WHERE HealthBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $healthInfo = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(DISTINCT HealthType.id_pk) AS healthTypesInGame
                 FROM HealthType
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $healthTypesInGame = $stmt->fetchObject();

    $healthQuality = $healthInfo->healthQuality;

    if ($healthQuality > 100)
    {
        $healthQuality = 100;
    }

    $healthVariety = (($healthInfo->healthTypesInCity)/($healthTypesInGame->healthTypesInGame)*100);
    $capactiyToPopulation = ((($healthInfo->healthCapacity)/(($_SESSION['population'])*0.1)/2)*100);

    if ($capactiyToPopulation > 100)
    {
        $capactiyToPopulation = 100;
    }

    $healthHappiness = ($healthQuality*0.5)+($healthVariety*0.1)+($capactiyToPopulation*0.3);

    return $healthHappiness;
}

function getBusReducation()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 17)
        {
            $increase = $increase + 1;
        }
    }
    return (100-$increase)/100;
}

function getHealthIncrease()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 13)
        {
            $increase = $increase + 1;
        }
    }
    return $increase;
}

function healthHappiness()
{
    $dbConn = getConnection();
    $querySQL = "SELECT AVG(HealthBuilding.quality) AS healthQuality, COUNT(DISTINCT HealthBuilding.health_type_id_fk) AS healthTypesInCity, SUM(HealthType.capacity) AS healthCapacity
                 FROM HealthBuilding
                 LEFT JOIN HealthType ON HealthBuilding.health_type_id_fk = HealthType.id_pk
                 WHERE HealthBuilding.city_id_fk =:cityID
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $healthInfo = $stmt->fetchObject();

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(DISTINCT HealthType.id_pk) AS healthTypesInGame
                 FROM HealthType
                 ";
    $stmt = $dbConn->prepare($querySQL);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $healthTypesInGame = $stmt->fetchObject();

    $healthQuality = $healthInfo->healthQuality;

    if ($healthQuality > 100)
    {
        $healthQuality = 100;
    }

    $healthVariety = (($healthInfo->healthTypesInCity)/($healthTypesInGame->healthTypesInGame)*100);
    $capactiyToPopulation = ((($healthInfo->healthCapacity)/(($_SESSION['population'])*0.1)/2)*100);

    if ($capactiyToPopulation > 100)
    {
        $capactiyToPopulation = 100;
    }

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.age
                 FROM Citizen
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmtT = $dbConn->prepare($querySQL);
    $stmtT->execute(array(':cityID' => $_SESSION['cityID']));

    $increase = getHealthIncrease();

    while ($rowobj = $stmtT->fetchObject())
    {
        $ageScore = (100 - $rowobj->age);

        if ($ageScore < 0)
        {
            $ageScore = 0;
        }

        $healthHappiness = ($healthQuality*0.5)+($healthVariety*0.1)+($ageScore*0.1)+($capactiyToPopulation*0.3)+$increase;
        $healthHappiness = round($healthHappiness, 0);

        if ($healthHappiness > 100)
        {
            $healthHappiness = 100;
        }

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET health_happiness =:healthHappy
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt4 = $dbConn->prepare($SQLupdate);
        $stmt4->execute(array(':citizenID' => $rowobj->id_pk, ':healthHappy' => $healthHappiness));
    }
    overallHappiness();
}

function getQolHappiness()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT City.tax_rate, City.retirement_age
                 FROM City
                 WHERE id_pk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $cityDetails = $stmt->fetchObject();

    $taxHappiness = ((1-((($cityDetails->tax_rate)/50)/0.8))*100);
    if ($taxHappiness < 0)
    {
        $taxHappiness = 0;
    }

    if ($taxHappiness > 100)
    {
        $taxHappiness = 100;
    }

    $retireHappiness = ((1-((($cityDetails->retirement_age)-51)/30))*100);
    if ($retireHappiness < 0)
    {
        $retireHappiness = 0;
    }
    if ($retireHappiness > 100)
    {
        $retireHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT SUM(ElectricityType.elect_output) AS elecOutput
                 FROM ElectricityBuilding
                 LEFT JOIN ElectricityType ON ElectricityBuilding.elect_type_id_fk = ElectricityType.id_pk
                 WHERE ElectricityBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $elecDetails = $stmt->fetchObject();

    $elecHappiness = (($elecDetails->elecOutput)/(($_SESSION['population'])*10))*100;
    if ($elecHappiness < 0)
    {
        $elecHappiness = 0;
    }
    if ($elecHappiness > 100)
    {
        $elecHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT SUM(WaterType.water_output) AS waterOutput
                 FROM WaterBuilding
                 LEFT JOIN WaterType ON WaterBuilding.water_type_id_fk = WaterType.id_pk
                 WHERE WaterBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $waterDetails = $stmt->fetchObject();

    $waterHappiness = (($waterDetails->waterOutput)/(($_SESSION['population'])*15))*100;
    if ($waterHappiness < 0)
    {
        $waterHappiness = 0;
    }
    if ($waterHappiness > 100)
    {
        $waterHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT SUM(FoodType.output_a_quantity) AS foodOutputA, SUM(FoodType.output_b_quantity) AS foodOutputB, SUM(FoodType.output_c_quantity) AS foodOutputC
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(DISTINCT FoodType.output_a_name) AS aTypes, 
                        COUNT(DISTINCT FoodType.output_b_name) AS bTypes, 
                        COUNT(DISTINCT FoodType.output_c_name) AS cTypes
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails2 = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(DISTINCT FoodType.output_a_name) AS aTypes, 
                        COUNT(DISTINCT FoodType.output_b_name) AS bTypes, 
                        COUNT(DISTINCT FoodType.output_c_name) AS cTypes
                 FROM FoodType
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails3 = $stmt->fetchObject();


    $foodOutput = ($foodDetails->foodOutputA)+($foodDetails->foodOutputB)+($foodDetails->foodOutputC);
    $foodTypes = ($foodDetails2->aTypes)+($foodDetails2->bTypes)+($foodDetails2->cTypes);
    $foodTypesInGame = ($foodDetails3->aTypes)+($foodDetails3->bTypes)+($foodDetails3->cTypes);

    $foodHappiness = ($foodOutput/($_SESSION['population']*25))*100;
    if ($foodHappiness < 0)
    {
        $foodHappiness = 0;
    }
    if ($foodHappiness > 100)
    {
        $foodHappiness = 100;
    }

    $foodDiversity = ($foodTypes/$foodTypesInGame)*100;
    if ($foodDiversity < 0)
    {
        $foodDiversity = 0;
    }
    if ($foodDiversity > 100)
    {
        $foodDiversity = 100;
    }

    $increase = getQolIncrease();

    $qolHap = ($taxHappiness*0.2)+($retireHappiness*0.2)+($elecHappiness*0.1)+($waterHappiness*0.1)+($foodHappiness*0.1)+($foodDiversity*0.1)+$increase;
    return $qolHap;
}

function getQolIncrease()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 7)
        {
            $increase = $increase + 1;
        }
        else if ($rowObj->itemType == 16)
        {
            $increase = $increase + 1;
        }

    }
    return $increase;
}

function qolHappiness()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT City.tax_rate, City.retirement_age
                 FROM City
                 WHERE id_pk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $cityDetails = $stmt->fetchObject();

    $taxHappiness = ((1-((($cityDetails->tax_rate)/50)/0.8))*100);
    if ($taxHappiness < 0)
    {
        $taxHappiness = 0;
    }

    if ($taxHappiness > 100)
    {
        $taxHappiness = 100;
    }

    $retireHappiness = ((1-((($cityDetails->retirement_age)-51)/30))*100);
    if ($retireHappiness < 0)
    {
        $retireHappiness = 0;
    }
    if ($retireHappiness > 100)
    {
        $retireHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT SUM(ElectricityType.elect_output) AS elecOutput
                 FROM ElectricityBuilding
                 LEFT JOIN ElectricityType ON ElectricityBuilding.elect_type_id_fk = ElectricityType.id_pk
                 WHERE ElectricityBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $elecDetails = $stmt->fetchObject();

    $elecHappiness = (($elecDetails->elecOutput)/(($_SESSION['population'])*10))*100;
    if ($elecHappiness < 0)
    {
        $elecHappiness = 0;
    }
    if ($elecHappiness > 100)
    {
        $elecHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT SUM(WaterType.water_output) AS waterOutput
                 FROM WaterBuilding
                 LEFT JOIN WaterType ON WaterBuilding.water_type_id_fk = WaterType.id_pk
                 WHERE WaterBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $waterDetails = $stmt->fetchObject();

    $waterHappiness = (($waterDetails->waterOutput)/(($_SESSION['population'])*15))*100;
    if ($waterHappiness < 0)
    {
        $waterHappiness = 0;
    }
    if ($waterHappiness > 100)
    {
        $waterHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLquery = "SELECT SUM(FoodType.output_a_quantity) AS foodOutputA, SUM(FoodType.output_b_quantity) AS foodOutputB, SUM(FoodType.output_c_quantity) AS foodOutputC
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(DISTINCT FoodType.output_a_name) AS aTypes, 
                        COUNT(DISTINCT FoodType.output_b_name) AS bTypes, 
                        COUNT(DISTINCT FoodType.output_c_name) AS cTypes
                 FROM FoodBuilding
                 LEFT JOIN FoodType ON FoodBuilding.food_type_id_fk = FoodType.id_pk
                 WHERE FoodBuilding.city_id_fk =:cityID
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails2 = $stmt->fetchObject();

    $dbConn = getConnection();
    $SQLquery = "SELECT COUNT(DISTINCT FoodType.output_a_name) AS aTypes, 
                        COUNT(DISTINCT FoodType.output_b_name) AS bTypes, 
                        COUNT(DISTINCT FoodType.output_c_name) AS cTypes
                 FROM FoodType
                ";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':cityID' => $_SESSION['cityID']));
    $foodDetails3 = $stmt->fetchObject();


    $foodOutput = ($foodDetails->foodOutputA)+($foodDetails->foodOutputB)+($foodDetails->foodOutputC);
    $foodTypes = ($foodDetails2->aTypes)+($foodDetails2->bTypes)+($foodDetails2->cTypes);
    $foodTypesInGame = ($foodDetails3->aTypes)+($foodDetails3->bTypes)+($foodDetails3->cTypes);

    $foodHappiness = ($foodOutput/($_SESSION['population']*25))*100;
    if ($foodHappiness < 0)
    {
        $foodHappiness = 0;
    }
    if ($foodHappiness > 100)
    {
        $foodHappiness = 100;
    }

    $foodDiversity = ($foodTypes/$foodTypesInGame)*100;
    if ($foodDiversity < 0)
    {
        $foodDiversity = 0;
    }
    if ($foodDiversity > 100)
    {
        $foodDiversity = 100;
    }

    $dbConn = getConnection();
    $querySQL = "SELECT Citizen.id_pk, Citizen.retirement_fund, Citizen.retired, Job.salary, Citizen.age
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Citizen.city_id_fk =:cityID
                 ";
    $stmtT = $dbConn->prepare($querySQL);
    $stmtT->execute(array(':cityID' => $_SESSION['cityID']));

    $increase = getQolIncrease();

    while ($rowobj = $stmtT->fetchObject())
    {
        if ($rowobj->age < 16)
        {
            $retirementFundHap = 100;
        }
        else
        {
            if ($rowobj->retired == 1)
            {
                $money = $rowobj->retirement_fund;
            }
            else
            {
                $money = ($rowobj->salary)/2;
            }
            $retirementFundHap = ($money/3750)*100;
        }

        if ($retirementFundHap < 0)
        {
            $retirementFundHap = 0;
        }
        if ($retirementFundHap > 100)
        {
            $retirementFundHap = 100;
        }

        $qolHappiness = ($taxHappiness*0.2)+($retireHappiness*0.2)+($retirementFundHap*0.2)+($elecHappiness*0.1)+($waterHappiness*0.1)+($foodHappiness*0.1)+($foodDiversity*0.1)+$increase;
        $qolHappiness = round($qolHappiness, 0);

        if ($qolHappiness > 100)
        {
            $qolHappiness = 100;
        }

        $dbConn = getConnection();
        $SQLupdate = "UPDATE Citizen
                      SET qol_happiness =:qolHappy
                      WHERE Citizen.id_pk =:citizenID
                                     ";
        $stmt4 = $dbConn->prepare($SQLupdate);
        $stmt4->execute(array(':citizenID' => $rowobj->id_pk, ':qolHappy' => $qolHappiness));
    }
    overallHappiness();
}

function getSafetyIncrease()
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AvatarItems.id_pk, AvatarType.id_pk AS itemType
                 FROM AvatarItems
                 LEFT JOIN AvatarType ON AvatarItems.item_id_fk = AvatarType.id_pk
                 WHERE AvatarItems.equipped = 1 AND AvatarItems.player_id_fk = :userID";
    $stmt = $dbConn->prepare($SQLquery);
    $stmt->execute(array(':userID' => $_SESSION['userID']));

    $increase = 0;

    while ($rowObj = $stmt->fetchObject())
    {
        if ($rowObj->itemType == 4)
        {
            $increase++;
        }
        else if ($rowObj->itemType == 25)
        {
            $increase = $increase + 2;
        }
    }
    return $increase;
}

function safetyHappiness()
{
    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Job.id_pk) AS policeCounter
                 FROM Job
                 WHERE Job.city_id_fk =:cityID AND Job.citizen_employed = 1 AND Job.job_industry = 1 AND Job.util_type = 5
                 ";
    $stmtT = $dbConn->prepare($querySQL);
    $stmtT->execute(array(':cityID' => $_SESSION['cityID']));
    $policeInfo = $stmtT->fetchObject();

    $polToPopHapp = ((($policeInfo->policeCounter)/($_SESSION['population'])/0.0018)/2)*100;
    if ($polToPopHapp < 0)
    {
        $polToPopHapp = 0;
    }
    if ($polToPopHapp > 100)
    {
        $polToPopHapp = 100;
    }

    $dbConn = getConnection();
    $querySQL = "SELECT AVG(PoliceBuilding.quality) AS policeQuality
                 FROM PoliceBuilding
                 WHERE PoliceBuilding.city_id_fk =:cityID
                 ";
    $stmtT = $dbConn->prepare($querySQL);
    $stmtT->execute(array(':cityID' => $_SESSION['cityID']));
    $policeInfo2 = $stmtT->fetchObject();

    $policeQualityHapp = $policeInfo2->policeQuality;
    if ($policeQualityHapp < 0)
    {
        $policeQualityHapp = 0;
    }
    if ($policeQualityHapp > 100)
    {
        $policeQualityHapp = 100;
    }

    $dbConn = getConnection();
    $querySQL = "SELECT COUNT(Job.id_pk) AS fireCounter
                 FROM Job
                 WHERE Job.city_id_fk =:cityID AND Job.citizen_employed = 1 AND Job.job_industry = 1 AND Job.util_type = 4
                 ";
    $stmtT = $dbConn->prepare($querySQL);
    $stmtT->execute(array(':cityID' => $_SESSION['cityID']));
    $fireInfo = $stmtT->fetchObject();

    $fireToPopHapp = ((($fireInfo->fireCounter)/($_SESSION['population'])/0.0005)/2)*100;
    if ($fireToPopHapp < 0)
    {
        $fireToPopHapp = 0;
    }
    if ($fireToPopHapp > 100)
    {
        $fireToPopHapp = 100;
    }

    $dbConn = getConnection();
    $querySQL = "SELECT AVG(FireBuilding.quality) AS fireQuality
                 FROM FireBuilding
                 WHERE FireBuilding.city_id_fk =:cityID
                 ";
    $stmtT = $dbConn->prepare($querySQL);
    $stmtT->execute(array(':cityID' => $_SESSION['cityID']));
    $fireInfo2 = $stmtT->fetchObject();

    $fireQualityHapp = $fireInfo2->fireQuality;
    if ($fireQualityHapp < 0)
    {
        $fireQualityHapp = 0;
    }
    if ($fireQualityHapp > 100)
    {
        $fireQualityHapp = 100;
    }

    $increase = getSafetyIncrease();

    $safetyHappiness = ($polToPopHapp*0.25)+($policeQualityHapp*0.25)+($fireToPopHapp*0.25)+($fireQualityHapp*0.25)+$increase;
    $safetyHappiness = round($safetyHappiness, 0);

    if ($safetyHappiness > 100)
    {
        $safetyHappiness = 100;
    }

    $dbConn = getConnection();
    $SQLupdate = "UPDATE Citizen
                  SET safety_happiness =:safeHappy
                  WHERE Citizen.city_id_fk =:cityID
                                     ";
    $stmt4 = $dbConn->prepare($SQLupdate);
    $stmt4->execute(array(':cityID' => $_SESSION['cityID'], ':safeHappy' => $safetyHappiness));
}

function chargeUser($cost)
{
    $newCityCoins = ($_SESSION['coins']-$cost);
    $_SESSION['coins'] = $newCityCoins;

    $dbConn = getConnection();
    $SQLupdate = "UPDATE City
                  SET coins = :coins
                  WHERE City.id_pk =:cityID
                              ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':coins' => $newCityCoins, ':cityID' => $_SESSION['cityID']));
}

function rewardCoins($coins)
{
    $newCityCoins = ($_SESSION['coins']+($coins));
    $_SESSION['coins'] = $newCityCoins;

    $dbConn = getConnection();
    $SQLupdate = "UPDATE City
                  SET coins = :coins
                  WHERE City.id_pk =:cityID
                              ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':coins' => $newCityCoins, ':cityID' => $_SESSION['cityID']));
}

function newJob($empID, $jobRole, $jobIndustry, $educRequired, $quality, $salary, $utilType)
{
    $dbConn = getConnection();
    $SQLinsert = "INSERT INTO Job( city_id_fk, employer_id_fk, employer_role_type, job_industry, educ_required, quality, salary, util_type)
                          VALUES ( :cityID, :empID, :jobRole, :jobIndustry, :educRequired, :quality, :salary, :utilType)
                     ";
    $stmt1 = $dbConn->prepare($SQLinsert);
    $stmt1->execute(array(':cityID' => $_SESSION['cityID'], ':empID' => $empID, ':jobRole' => $jobRole, ':jobIndustry' => $jobIndustry, ':educRequired' => $educRequired, ':quality' => $quality, ':salary' => $salary, ':utilType' => $utilType));
}

function getSalary($education)
{
    if ($education == 0)
    {
        $salary = 500;
    }
    else if ($education == 1)
    {
        $salary = 1000;
    }
    else if ($education == 2)
    {
        $salary = 2000;
    }
    else if ($education == 3)
    {
        $salary = 4000;
    }
    return $salary;
}

function getEducation($education)
{
    if ($education == 0)
    {
        $educName = 'None';
    }
    else if ($education == 1)
    {
        $educName = 'School';
    }
    else if ($education == 2)
    {
        $educName = 'College';

    }
    else if ($education == 3)
    {
        $educName = 'University';
    }
    return $educName;
}

function updateSalary($salary, $industry, $employer, $utilType)
{
    $dbConn = getConnection();
    $SQLupdate = "UPDATE Job
                  SET salary =:sal
                  WHERE Job.job_industry = :jobIndustry AND Job.employer_id_fk =:empID AND Job.util_type =:utilType AND Job.city_id_fk =:city
                      ";
    $stmt = $dbConn->prepare($SQLupdate);
    $stmt->execute(array(':sal' => $salary, ':jobIndustry' => $industry, ':empID' => $employer, ':utilType' => $utilType, ':city' => $_SESSION['cityID']));
}

function getAverageJobHap($industry, $employer, $utilType)
{
    $dbConn = getConnection();
    $SQLquery = "SELECT AVG(Citizen.job_happiness) AS jobHappiness
                 FROM Citizen
                 LEFT JOIN Job ON Citizen.job_id_fk = Job.id_pk
                 WHERE Job.employer_id_fk =:empID AND Job.job_industry =:jobIndustry AND Job.util_type =:utilType AND Job.city_id_fk =:city
                ";
    $stmt1 = $dbConn->prepare($SQLquery);
    $stmt1->execute(array(':empID' => $employer, ':jobIndustry' => $industry, ':utilType' => $utilType, ':city' => $_SESSION['cityID']));
    $occupantsDetails = $stmt1->fetchObject();
    $occupantsHappiness = round($occupantsDetails->jobHappiness, 0);

    return $occupantsHappiness;
}

function lastYearHolder()
{
    echo "<div class='mainGameContainer'>
    <div class='housesTopContainer'>
                    <ul class='housesButtContainer'>
                        <li class='housesButt' id='accounts'><a href='lastYearAccount.php' id='noteRed'>Accounts</a></li>
                        <li class='housesButt' id='citizens'><a href='lastYearPeople.php'>Citizens</a></li>
                    </ul>
                </div>";

    echo "      <div class='housesHolder'>";
}


?>


