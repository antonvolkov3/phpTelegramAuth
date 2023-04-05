<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Db.php';

//Процесс авторизации по ссылке
if(isset($_GET['action']) && $_GET['action'] == 'auth' && $_SERVER['HTTP_USER_AGENT'] != 'TelegramBot (like TwitterBot)')
{
    $authType = $_GET['type'] ?? 'login';
    if(isset($_GET['chat_id']) && !empty($_GET['chat_id'])
        && isset($_GET['key']) && !empty($_GET['key']))
    {
        $auth = new Auth();
        $userId = $auth->userIdByTgChatId($_GET['chat_id']);

        $activityId = $auth->login($authType, $userId, $_GET['key']);
        if($activityId > 0)
        {
            $db = new Db();
            $updateQuery = "
                UPDATE user_activity
                SET date_action = '" . date('Y-m-d H:i:s') . "'
                WHERE id = " . $activityId . "
            ";
            $db->query($updateQuery);

            $_SESSION['user_id'] = $userId;
            setcookie('user_id', $userId, time() + 7200, '/');
            header('Location: /index.php');
        }
        else
            header('Location: /index.php?error=expired');
    }
    else
        header('Location: /index.php');
}