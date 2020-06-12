<?php
/**
 * Created by PhpStorm.
 * User: calumwillis
 * Date: 13/02/2020
 * Time: 20:07
 */
ini_set("session.save_path", "/home/unn_w17006735/sessionData");
session_start();

require_once('functions.php');

$classSelected = $_GET['classSelected']; //Gets the class to be deleted.


$dbConn = getConnection();
$SQLquery = "SELECT Class.id_pk
                 FROM Class
                 JOIN ClassTeachers ON ClassTeachers.class_id_fk = Class.id_pk
                 WHERE ClassTeachers.teacher_id_fk =:teacherID";
$stmt = $dbConn->prepare($SQLquery);
$stmt->execute(array('teacherID' => $_SESSION['userID']));
$temp = $stmt->fetchObject();


$isUserClass = false;

if($temp)
{
    $isUserClass = true;
}

if ($isUserClass == true) //This checks that the class to be deleted does belong to the user.
{
//Deletes record from Class table.
    $dbConn = getConnection();
    $DeleteSQL = "DELETE
              FROM Class
              WHERE Class.id_pk=:classID";
    $stmt = $dbConn->prepare($DeleteSQL);
    $stmt->execute(array('classID' => $classSelected));

//Deletes record from ClassTeachers table.
    $dbConn = getConnection();
    $DeleteSQL = "DELETE
              FROM ClassTeachers
              WHERE ClassTeachers.class_id_fk=:classID";
    $stmt = $dbConn->prepare($DeleteSQL);
    $stmt->execute(array('classID' => $classSelected));

//Deletes record from AccountClasses table.
    $dbConn = getConnection();
    $DeleteSQL = "DELETE
              FROM AccountClasses
              WHERE AccountClasses.class_id_fk=:classID";
    $stmt = $dbConn->prepare($DeleteSQL);
    $stmt->execute(array('classID' => $classSelected));
}

header('Location: classes.php');
?>