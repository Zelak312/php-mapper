<?php
namespace Zelak\Mapper\Tests;
use Faker;
use Exception;
use Zelak\Mapper\Mapper;
use PHPUnit\Framework\TestCase;
use stdClass;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertNotNull;

final class MapperTest extends TestCase
{
    private static Faker\Generator $faker;
    private static Mapper $mapper;

    public static function setUpBeforeClass(): void {
        self::$faker = Faker\Factory::create();
        self::$mapper = new Mapper();
        self::$mapper->createMap(UserDto::class);
        self::$mapper->createMap(ProductDto::class);
        self::$mapper->createMap(ProductNoMapDto::class);
        self::$mapper->createMap(BuyerDto::class);

        self::$mapper->createMap(ProductArrDto::class)
            ->specify("buyer", BuyerDto::class);
    }

    public function testOneToOneBasicTypes(): void {
        $expected = new stdClass();
        $expected->id = self::$faker->text();
        $expected->username = self::$faker->userName();
        $expected->password = self::$faker->password();

        $result = self::$mapper->map($expected, UserDto::class);
        
        assertNotNull($result);
        assertInstanceOf(UserDto::class, $result);
        assertEquals($expected->id, $result->id);
        assertEquals($expected->username, $result->username);
        assertEquals($expected->password, $result->password);
    }

    public function testOneToOneBasicTypesArray(): void {
        $expected = array();
        $nbrOfUsers = self::$faker->numberBetween(20, 40);

        for($i = 0; $i < $nbrOfUsers; $i++) {
            $user = new stdClass();
            $user->id = self::$faker->text();
            $user->username = self::$faker->userName();
            $user->password = self::$faker->password();
            array_push($expected, $user);
        }

        $results = self::$mapper->map($expected, UserDto::class);
        
        assertNotNull($results);
        assertIsArray($results);
        assertEquals($nbrOfUsers, count($results));

        for($i = 0; $i < $nbrOfUsers; $i++) {
            assertInstanceOf(UserDto::class, $results[$i]);
            assertEquals($expected[$i]->id, $results[$i]->id);
            assertEquals($expected[$i]->username, $results[$i]->username);
            assertEquals($expected[$i]->password, $results[$i]->password);
        }
    }
    
    public function testMappingNotFound(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No mapping found for Zelak\Mapper\Tests\NotMappedClassDto");
        self::$mapper->map(new UserDto(), NotMappedClassDto::class);
    }

    public function testOneToOneWithNonBasicTypes(): void {
        $buyer = new stdClass();
        $buyer->name = self::$faker->name();
        $expected = new stdClass();
        $expected->name = self::$faker->name();
        $expected->buyer = $buyer;

        $result = self::$mapper->map($expected, ProductDto::class);
        
        assertNotNull($result);
        assertInstanceOf(ProductDto::class, $result);
        assertEquals($expected->name, $result->name);
        assertEquals($expected->buyer->name, $result->buyer->name);
    }

    public function testOneToOneWithNonBasicTypesArray(): void {
        $expected = array();
        $nbr = self::$faker->numberBetween(20, 40);

        for($i = 0; $i < $nbr; $i++) {
            $buyer = new stdClass();
            $buyer->name = self::$faker->name();
            $prod = new stdClass();
            $prod->name = self::$faker->name();
            $prod->buyer = $buyer;

            array_push($expected, $prod);
        }

        $results = self::$mapper->map($expected, ProductDto::class);
        
        assertNotNull($results);
        assertIsArray($results);
        assertEquals($nbr, count($results));

        for($i = 0; $i < $nbr; $i++) {
            assertInstanceOf(ProductDto::class, $results[$i]);
            assertEquals($expected[$i]->name, $results[$i]->name);
            assertEquals($expected[$i]->buyer->name, $results[$i]->buyer->name);
        }
    }

    public function testMappingNotFoundFromProperty(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No mapping found for Zelak\Mapper\Tests\NotMappedClassDto");

        $prod = new stdClass();
        $prod->name = self::$faker->name();
        $prod->buyer = new stdClass();

        self::$mapper->map($prod, ProductNoMapDto::class);
    }

    public function testArrayToArrayBasicTypeMapping(): void {
        $buyer = new stdClass();
        $buyer->name = self::$faker->name();

        $expected = new stdClass();
        $expected->name = self::$faker->name();
        $expected->buyer = array($buyer);

        $result = self::$mapper->map($expected, ProductArrDto::class);
        
        assertNotNull($result);
        assertInstanceOf(ProductArrDto::class, $result);
        assertEquals($expected->name, $result->name);
        assertEquals($expected->buyer[0]->name, $result->buyer[0]->name);
    }

    public function testArrayToArrayNonBasicTypeMapping(): void {
        $buyer = new stdClass();
        $buyer->name = self::$faker->name();

        $expected = new stdClass();
        $expected->name = self::$faker->name();
        $expected->buyer = array($buyer);
        $result = self::$mapper->map($expected, ProductArrDto::class);
        
        assertNotNull($result);
        assertInstanceOf(ProductArrDto::class, $result);
        assertEquals($expected->name, $result->name);
        assertEquals($expected->buyer[0]->name, $result->buyer[0]->name);
    }
}