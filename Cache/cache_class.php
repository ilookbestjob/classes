<?php
require "../classes/Servers/servers_class.php";
require "../classes/Debug/debug.class.php";


class Cache


{
    private $connection;
    private $debug;
    private $server;
    private $cachename;
    private $daysactual;

    function __construct($server = "zod1", $cachename = "zchb", $cachestruct = false, $daysactual = 30)
    {
        $servers = new Servers();
        $this->debug = new Debug();
   $this->daysactual = $daysactual;



        $this->server = $server;
        $this->cachename = $cachename;
        $this->connection = $servers->servers[$server]->connection;

        $baseexists = $this->checkcache("cache");
        $tableexists = $this->checkcache("cache", $cachename);

        $this->debug->addLog($baseexists ? "Обнаружена БД с кешем" : "БД с кешем не обнаружена");

        if (!$baseexists) {


            mysqli_query($this->connection, "CREATE DATABASE IF NOT EXISTS cache");
            $this->debug->addLog("Создаем базу с кэшем");
        }

        $this->debug->addLog($tableexists ? "Обнаружена таблица $cachename в БД с кешем" : "в БД с кешем таблица $cachename не обнаружена");


        if (!$tableexists) {

            if (!$cachestruct) {



                mysqli_query($this->connection, " CREATE TABLE `cache`.`$cachename` ( `id` INT NOT NULL AUTO_INCREMENT,`date` DATETIME NULL,`name` VARCHAR(255) NULL,`data` TEXT NULL,PRIMARY KEY (`id`));");
                $this->debug->addLog("Создаем таблицу $cachename с кэшем");
            }
        }
    }

    function checkcache($base, $table = false)
    {



        if ($table) {
            $sql = "SELECT count(*) quantity FROM information_schema.tables WHERE table_schema = '" . $base . "' AND table_name = '" . $table . "'";
            $sqlresult = mysqli_query($this->connection, $sql);
            $this->debug->addLog("Проверяем наличие таблицы  $table в кэше (function checkcache): $sql");
            $sql_row = mysqli_fetch_array($sqlresult);

            if ($sql_row['quantity'] == 0) return false;
            return true;
        }

        $sql = "SELECT count(*) quantity FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $base . "'";
        $sqlresult = mysqli_query($this->connection, $sql);
        $this->debug->addLog("Запрос на проверку наличия БД с кешем (function checkcache): $sql");
        $sql_row = mysqli_fetch_array($sqlresult);

        if ($sql_row['quantity'] == 0) return false;

        return true;
    }


    function checkrecord($param, $daysactual = 30)
    {
        $this->debug->addLog("Проверяем запись checkrecord($param, $daysactual)");
        $sql = "SELECT count(*) quantity FROM cache." . $this->cachename . " WHERE name = '" . $param . "'";
        $sqlresult = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sqlresult);
    

        if ($sql_row["quantity"] == 0) {

            $this->debug->addLog("Запись параметра $param отсутвует: $sql");
            return 1;
        }

        $sql = "SELECT count(*) quantity FROM cache." . $this->cachename . " WHERE name = '" . $param . "' and date<= now()- interval $daysactual day";
        $sqlresult = mysqli_query($this->connection, $sql);
        $sql_row = mysqli_fetch_array($sqlresult);

        if (($sql_row["quantity"] != 0) || ($daysactual == 0)) {

            $this->debug->addLog("Запись параметра $param не актуальна: $sql");
            return 2;
        }

        $this->debug->addLog("Запись параметра $param присутвует и актуальна");
       
    }


    function add($param, $data)
    {
        $this->debug->addLog("Запуск функции add($param, data) значение  \$this->daysactual: $this->daysactual");
        switch ($this->checkrecord($param, $this->daysactual)) {
            case 1:
                $sql = "insert into cache." . $this->cachename . " set name = '" . $param . "', date= now(), data='".str_replace('\"','\\\"',$data)."'";
                $this->debug->addLog("Добавляем парметр $param в БД: $sql");

                break;
            case 2:
                $sql = "update cache." . $this->cachename . " set  date= now(), data='".str_replace('\"','\\\"',$data)."' where name = '" . $param . "'";
                $this->debug->addLog("Обновляем парметр $param в БД: $sql");
        }
        $sqlresult = mysqli_query($this->connection,  $sql);
    }


    function addonly($param, $data)
    {
        $this->debug->addLog("Запуск функции add($param, data) значение  \$this->daysactual: $this->daysactual");
        
                $sql = "insert into cache." . $this->cachename . " set name = '" . $param . "', date= now(), data='".str_replace('\"','\\\"',$data)."'";
                $this->debug->addLog("Добавляем парметр $param в БД: $sql");

        $sqlresult = mysqli_query($this->connection,  $sql);
    }



    function get($param)
    {
        $sql = "select data from cache." . $this->cachename . " where name = '" . $param . "'";
        $sqlresult = mysqli_query($this->connection,  $sql);
        $sql_row = mysqli_fetch_array($sqlresult);
        $this->debug->addLog("Получаем парметр $param в БД($sql) : ".$sql_row["data"]);
        return $sql_row["data"] ;

    }
    function getActuality($param)
    {
        $sql = "select date from cache." . $this->cachename . " where name = '" . $param . "'";
        $sqlresult = mysqli_query($this->connection,  $sql);
        $sql_row = mysqli_fetch_array($sqlresult);
        $this->debug->addLog("Получаем парметр $param в БД($sql) : ".$sql_row["date"]);
        return $sql_row["date"] ;

    }
}
