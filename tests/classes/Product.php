<?php
namespace Zelak\Mapper\Tests;

class Product {
    public string $name;
    public Buyer $buyer;

    public function __construct(string $name, Buyer $buyer)
    {
        $this->name = $name;
        $this->buyer = $buyer;
    }
}