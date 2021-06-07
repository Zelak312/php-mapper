<?php
namespace Zelak\Mapper\Tests;

class User {

    public string $id;
    public string $username;
    public string $password;

    public function __construct(string $id, string $username, string $password)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
    }
}