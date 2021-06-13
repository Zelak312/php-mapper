<?php
namespace Zelak\Mapper\Tests;

class ProductNoMapDto {
    public string $fromType = "product";

    public string $name;
    public NotMappedClassDto $buyer;
}