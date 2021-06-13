<?php
namespace Zelak\Mapper\Tests;

class ProductDto {
    public string $fromType = "product";

    public string $name;
    public BuyerDto $buyer;
}