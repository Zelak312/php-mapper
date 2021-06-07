<?php
namespace Zelak\Mapper\Tests;

class Buyer {
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}