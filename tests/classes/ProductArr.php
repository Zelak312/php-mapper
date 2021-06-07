<?php
namespace Zelak\Mapper\Tests;

class ProductArr {
    public string $name;
    public array $buyer;

    public function __construct(string $name, mixed $buyer)
    {
        $this->name = $name;
        $this->buyer = array($buyer);
    }
}