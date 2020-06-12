<?php
    ini_set("session.save_path", "/home/unn_w17006735/sessionData");
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log Out - The script to log out the account.</title>
    <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>
<?php

    $_SESSION['loggedIn'] = false;
    header('Location: index.php');

?>

</body>
</html>