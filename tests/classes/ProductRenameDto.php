<?php
namespace Zelak\Mapper\Tests;

class ProductRenameDto {
    public string $name;
    public BuyerDto $otherBuyer;
}

class ProductRenameWrongDto {
    public string $name;
    public BuyerDto $otherBuyer;
}