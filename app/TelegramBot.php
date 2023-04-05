<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Auth.php';
use Telegram\Bot\Api;

class TelegramBot
{
    private $botToken;
    public $apiUrl;
    public $bot;

    public function __construct()
    {
        $this->botToken = '';
        $this->apiUrl = 'https://api.telegram.org/bot';
        $this->bot = new Api($this->botToken);
    }

    /**
     * Работа с входящими сообщениями
     * @return void
     */
    public function poll()
    {
        $result = $this->bot->getWebhookUpdates();
        $text = $result["message"]["text"];
        $chatId = $result["message"]["chat"]["id"];
        $userName = $result["message"]["from"]["username"];
        $lastName = $result['message']['from']['last_name'];
        $firstName = $result['message']['from']['first_name'];

        if ($text == "/start")
        {
            $auth = new Auth();
            $authMsg = $auth->userAuth($chatId, $userName, $firstName, $lastName);

            if (!empty($authMsg))
                $this->sendTelegramMessage($chatId, $authMsg);
        }
    }

    /**
     * Отправка сообщения пользователю в telegram
     * @param $chatId string
     * @param $message string
     * @return void
     */
    private function sendTelegramMessage(string $chatId, string $message)
    {
       $this->createRequest('sendMessage', $chatId, $message);
    }

    /**
     * Вспомогательный метод отправки запросов
     * @param $mode string
     * @param $chatId string
     * @param $message string
     * @return bool|string
     */
    private function createRequest(string $mode, string $chatId, string $message): bool|string
    {
        $url = $this->apiUrl . $this->botToken . '/' . $mode . '?chat_id=' . $chatId . '&text=' . urlencode($message);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
