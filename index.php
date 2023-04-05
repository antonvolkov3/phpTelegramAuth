<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Db.php';

//Создаем таблицы в БД
$queryUsers = "CREATE TABLE IF NOT EXISTS users (id SERIAL, PRIMARY KEY(id), tg_chat_id varchar(30) NOT NULL, username varchar(50) NULL, first_name varchar(30) NULL, last_name varchar(30) NULL, create_at TIMESTAMP NOT NULL)";
$queryUserActivity = "CREATE TABLE IF NOT EXISTS user_activity (id SERIAL, PRIMARY KEY(id), user_id integer NOT NULL, action_type varchar(30) NOT NULL, date_action TIMESTAMP NULL, auth_key varchar (100) NOT NULL)";
try
{
    $db = new Db();
    $db->query($queryUsers, 0);
    $db->query($queryUserActivity);
}
catch (Exception $e)
{
    die($e->getMessage());
}

$userData = [];
$userActivity = [];
//Получение данных для авторизованного пользователя
if(isset($_SESSION['user_id']))
{
    $db = new Db();
    $userData = $db->query("SELECT * FROM users WHERE id = " . $_SESSION['user_id'], 0);
    $userData = $userData[0] ?? [];

    $dateLimit = date('Y-m-d H:i:s', strtotime('-1 month'));
    $userActivity = $db->query("SELECT * FROM user_activity WHERE user_id = " . $_SESSION['user_id'] . " AND date_action >= '" . $dateLimit . "' ORDER BY id");
}

include_once ('template/layout.php');
