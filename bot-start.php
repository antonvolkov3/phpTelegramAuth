<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/TelegramBot.php';
$telegramBot = new TelegramBot();
$telegramBot->poll();