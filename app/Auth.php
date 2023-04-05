<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Db.php';

class Auth{
    /**
     * @param $tgChatId string
     * @param $userName string
     * @param $firstName string
     * @param $lastName string
     * @return string
     */
    public function userAuth(string $tgChatId, string $userName, string $firstName, string $lastName): string
    {
        //Проверка пользователя на регистрацию
        $userRegisterId = $this->userIdByTgChatId($tgChatId);
        $dateTime = date('Y-m-d H:i:s');
        $actionType = $userRegisterId > 0 ? 'login' : 'register';
        $authStringHash = '';

        //Регистрируем пользователя
        if($userRegisterId == 0)
        {
            $userRegisterData = $this->userRegister($tgChatId, $dateTime, $userName, $firstName, $lastName);
            $userRegisterId = $userRegisterData[0] ?? 0;
        }
        //Проверка на уже существующий ключ авторизации
        else
            $authStringHash = $this->checkUserForKey($userRegisterId);

        if(empty($authStringHash) && $userRegisterId > 0)
        {
            $authString = $tgChatId.$userRegisterId.$actionType.$dateTime;
            $authStringHash = sha1($authString);

            //Создаем запись активности с внесением ключа авторизации
            $this->createActivity($userRegisterId, $actionType, $authStringHash);
        }

        if(!empty($authStringHash))
        {
            $url = 'https://' . $_SERVER['HTTP_HOST'] . '/login.php?action=auth&type=' . $actionType . '&chat_id=' . $tgChatId . '&key=' . $authStringHash;
            $actionTypeMsg = $actionType == 'register' ? 'регистрации' : 'авторизации';
            return 'Для ' . $actionTypeMsg . ' пройдите по ссылке: ' . $url;
        }

        return '';
    }

    /**
     * Авторизация пользователя по ссылке
     * @param $authType string
     * @param $userId integer
     * @param $authStringHash string
     * @return integer
     */
    public function login(string $authType, int $userId, string $authStringHash): int
    {
        $authType = Db::stringClear($authType);
        $authStringHash = Db::stringClear($authStringHash);

        return $this->validate($authType, $userId, $authStringHash);
    }

    /**
     * Получение userId по telegram_chat_id
     * @param $tgChatId string
     * @return int
     */
    public function userIdByTgChatId(string $tgChatId): int
    {
        $db = new Db();
        $query = "SELECT id FROM users WHERE tg_chat_id = '" . $tgChatId . "'";
        $userData = $db->query($query);
        return $userData[0][0] ?? 0;
    }

    /**
     * Проверка соответсвия данных для авторизации пользователя
     * @param $authType string
     * @param $userId integer
     * @param $authStringHash string
     * @return int
     */
    private function validate(string $authType, int $userId, string $authStringHash):int
    {
        if($userId > 0)
        {
            $activityQuery = "
                SELECT id FROM user_activity 
                WHERE user_id = " . $userId . " 
                    AND action_type = '" . $authType . "'
                    AND date_action IS NULL
                    AND auth_key = '" . $authStringHash . "'
            ";

            $db = new Db();
            $activityData = $db->query($activityQuery);
            return $activityData[0][0] ?? 0;
        }

        return 0;
    }

    /**
     * Регистрация пользователя
     * @param $tgChatId string
     * @param $dateTime string
     * @param $userName string
     * @param $firstName string
     * @param $lastName string
     * @return array
     */
    private function userRegister(string $tgChatId, string $dateTime, string $userName, string $firstName, string $lastName): array
    {
        $db = new Db();
        $data = ['tg_chat_id' => $tgChatId, 'username' => $userName, 'first_name' => $firstName, 'last_name' => $lastName, 'create_at' => $dateTime];

        return $db->insert('users', $data);
    }

    /**
     * Создание записи активности пользователя
     * @param $userId integer
     * @param $actionType string
     * @param $authKey string
     * @return void
     */
    private function createActivity(int $userId, string $actionType, string $authKey)
    {
        $db = new Db();
        $query = "INSERT INTO user_activity (user_id, action_type, auth_key) 
        VALUES (" . $userId . ", '" . $actionType . "', '" . $authKey . "')";
        $db->query($query);
    }

    /**
     * Получение ключа пользователя по userId
     * @param $userId integer
     * @return string
     */
    private function checkUserForKey(int $userId):string
    {
        $db = new Db();
        $query = "SELECT auth_key FROM user_activity WHERE user_id = " . $userId . " AND action_type = 'login' AND date_action IS NULL";
        $authKeyData = $db->query($query);
        return $authKeyData[0][0] ?? '';
    }
}