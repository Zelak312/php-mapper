<?php
namespace Zelak\Mapper\Tests;
use Faker;
use Exception;
use Zelak\Mapper\Mapper;
use PHPUnit\Framework\TestCase;

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
        self::$mapper->createMap(User::class, UserDto::class);
        self::$mapper->createMap(Product::class, ProductDto::class);
        self::$mapper->createMap(Product::class, ProductNoMapDto::class);
        self::$mapper->createMap(Buyer::class, BuyerDto::class);

        self::$mapper->createMap(ProductArr::class, ProductArrDto::class)
            ->specify("buyer", BuyerDto::class);

        self::$mapper->createMap(User::class, "string")
           ->from(function($from) { return $from->id . "-" . $from->username; });

        self::$mapper->createMap("string", UserDto::class)
            ->from(function($from) { 
                $userdto = new UserDto();
                $userdto->id = explode("-", $from)[0];
                $userdto->username = explode("-", $from)[1];

                return $userdto;
            });
    }

    public function testOneToOneBasicTypes(): void {
        $expected = new User(self::$faker->text(), self::$faker->userName(), self::$faker->password());
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
            $user = new User(self::$faker->text(), self::$faker->userName(), self::$faker->password());
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
        $this->expectExceptionMessage("No mapping found for Zelak\Mapper\Tests\UserDto -> Zelak\Mapper\Tests\NotMappedClass");
        self::$mapper->map(new UserDto(), NotMappedClass::class);
    }

    public function testOneToOneWithNonBasicTypes(): void {
        $buyer = new Buyer(self::$faker->name());
        $expected = new Product(self::$faker->name(), $buyer);
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
            $buyer = new Buyer(self::$faker->name());
            array_push($expected, new Product(self::$faker->name(), $buyer));
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
        $this->expectExceptionMessage("No mapping found for Zelak\Mapper\Tests\Buyer -> Zelak\Mapper\Tests\NotMappedClass");
        self::$mapper->map(new Product("nice", new Buyer("nice")), ProductNoMapDto::class);
    }

    public function testArrayToArrayBasicTypeMapping(): void {
        $expected = new ProductArr(self::$faker->name(), self::$faker->text());
        $result = self::$mapper->map($expected, ProductArrDto::class);
        
        assertNotNull($result);
        assertInstanceOf(ProductArrDto::class, $result);
        assertEquals($expected->name, $result->name);
        assertEquals($expected->buyer[0], $result->buyer[0]);
    }

    public function testArrayToArrayNonBasicTypeMapping(): void {
        $buyer = new Buyer(self::$faker->name());
        $expected = new ProductArr(self::$faker->name(), $buyer);
        $result = self::$mapper->map($expected, ProductArrDto::class);
        
        assertNotNull($result);
        assertInstanceOf(ProductArrDto::class, $result);
        assertEquals($expected->name, $result->name);
        assertEquals($expected->buyer[0]->name, $result->buyer[0]->name);
    }

    public function testNonBasicTypeToBasicType(): void {
        $user = new User(self::$faker->text(), self::$faker->userName(), self::$faker->password());
        $result = self::$mapper->map($user, "string");

        assertNotNull($result);
        assertEquals($user->id . "-" . $user->username, $result);
    }

    public function testBasicTypeToNonBasicType(): void {
        $userstr =  "test-crap";
        $result = self::$mapper->map($userstr, UserDto::class);

        assertNotNull($result);
        assertInstanceOf(UserDto::class, $result);
        assertEquals(explode("-", $userstr)[0], $result->id);
        assertEquals(explode("-", $userstr)[1], $result->username);
    }
}