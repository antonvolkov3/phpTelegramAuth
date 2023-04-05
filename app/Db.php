<?php

class Db{
    private $dbConnection;

    public function __construct()
    {
        $dbConfig = [
            'DB_HOST' => '127.0.0.1',
            'DB_NAME' => 'telegram_auth',
            'DB_USER' => 'postgres',
            'DB_PASSWORD' => '',
        ];

        $connectionString = "host=" . $dbConfig['DB_HOST'] . " dbname=" . $dbConfig['DB_NAME'] . " user=" . $dbConfig['DB_USER'] . " password=" . $dbConfig['DB_PASSWORD'];
        try {
            $this->dbConnection = pg_connect($connectionString, PGSQL_CONNECT_FORCE_NEW);
        }
        catch (Exception $e)
        {
            die($e->getMessage());
        }
    }

    /**
     * Выполнение запроса к БД
     * @param $query string
     * @param $close integer
     * @return array
     */
    public function query(string $query, int $close = 1):array
    {
        $queryData = [];
        $query = pg_query($this->dbConnection, $query);
        while ($row = pg_fetch_row($query))
            $queryData[] = $row;

        if($close == 1)
            pg_close($this->dbConnection);
        return $queryData;
    }

    /**
     * Запрос на создание записей в БД
     * @param $table string
     * @param $data array
     * @return array
     */
    public function insert(string $table, array $data):array
    {
        $query = "INSERT INTO " . $table . " (";
        $dataKeys = array_keys($data);
        $query .= implode(",", $dataKeys);
        $query .= ") VALUES (";
        $dataValues = array_values($data);
        $query .= "'" . implode("','", $dataValues) . "'";
        $query .= ")";

        $this->query($query, 0);
        $insertDataQuery = $this->createQuery($table, ['id'], 'id DESC', 1);
        $insertData = $this->query($insertDataQuery);
        return $insertData[0] ?? [];
    }

    /**
     * @param $table string
     * @param $selectFields array
     * @param $order string
     * @param $limit integer
     * @param $where array
     * @return string
     */
    private function createQuery(string $table, array $selectFields, string $order, int $limit, array $where = []): string
    {
        $selectFields = implode(",", $selectFields);
        $query = "SELECT " . $selectFields . " FROM " . $table . " ";
        if(count($where) > 0)
            $query .= implode(",", $where) . " ";

        $query .= "ORDER BY " . $order . " LIMIT " . $limit;
        return $query;
    }

    /**
     * Очищаем строку от возможных вставок
     * @param $str string
     * @return string
     */
    public static function stringClear(string $str): string
    {
        $str = trim($str);
        $str = stripslashes($str);
        return htmlspecialchars($str);
    }
}