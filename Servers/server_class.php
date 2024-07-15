<?php

class Server
{
    public $server;
    public $port;
    public $base;
    public $user;
    public $bdpassword;
    public $comment;
    public $httpport;

    private $connection;


    function __construct($fserver, $fport, $fbase, $fuser, $fbdpassword, $fcomment = "", $httpport = 80)
    {
        $this->server = $fserver;
        $this->port = $fport;
        $this->base = $fbase;
        $this->user = $fuser;
        $this->bdpassword = $fbdpassword;
        $this->comment = $fcomment;
        $this->httpport = $httpport;
    }


    function __get($name)
    {
        if ($name == "connection") {


            return !$this->connection ? mysqli_connect($this->server, $this->user, $this->bdpassword, $this->base) : !$this->connection;
        }

    }
}
