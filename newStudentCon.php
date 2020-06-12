<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 14/02/2020
 * Time: 17:02
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
require_once("functions.php");
checkLogin('classes');

if ($_SESSION['loggedIn'] == 'true')
{
    if ($_SESSION['teacher'] == true)
    {
        $classSelected = filter_has_var(INPUT_GET, 'classSelected')
            ? $_GET['classSelected'] : null;

        $username = filter_has_var(INPUT_GET, 'username')
            ? $_GET['username'] : null;

        $password = filter_has_var(INPUT_GET, 'password')
            ? $_GET['password'] : null;

        buildBanner();
        turnButtBlack('teacherButt');
        $updatedClassSelected = buildTeacherInterface($classSelected, null);


        $dbConn = getConnection();
        $SQLquery = "SELECT Class.name
                     FROM Class
                     WHERE Class.id_pk =:classID";
        $stmt = $dbConn->prepare($SQLquery);
        $stmt->execute(array(':classID' => $updatedClassSelected));
        $class = $stmt->fetchObject();
        $className = $class->name;

        echo "A new student has been created with the following details and added to the class: {$className}.";
        echo
        "
                <table>
                    <tr>
                        <th style='text-align: left;'>Username</th>
                        <th style='text-align: left'>Password</th>
                    </tr>
                    <tr>
                    <td style=' margin-right: 15px;'>{$username}</td>
                    <td>{$password}</td>
                    </tr>
                </table>
            ";

        echo "Please note, students will have the ability to change there password upon first log in.";


    }
    else
    {
        echo "<p>You do not have permission to view this page.</p>";
    }
}
else
{
    notLoggedIn();
}

?>


</body>
</html>
