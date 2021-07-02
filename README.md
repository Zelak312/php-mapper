# Zelak's Mapper

## Installation
```
composer require zelak/mapper
```
# Usage
## Creating simple DTOs
DTOs needs type hinting for the Mapper to work properly
```php
class UserDto {
    public int $id;
    public string $username;
    public string $password;
}
```
Creating the mapper, registering and using the Mapper
```php
$mapper = new Mapper(); // Creating
$mapper->createMap(UserDto::class); // Registering

// Using
$user = new stdClass();
$user->id = "1";
$user->username = "username";
$user->password = "password":

$userDto = $mapper->map($user, UserDto::class);
```
Result
```json
// Before
{
    "id": "1",
    "username": "username",
    "password": "password"
}

// After
{
    "id": 1,
    "username": "username",
    "password": "password"
}
```
---
## Creating linked DTOs
```php
class ProductDto {
    public string $name;
    public BuyerDto $buyer;
}

class BuyerDto {
    public string $name;
    public int $age;
}
```
Creating the mapper, registering and using the Mapper
```php
$mapper = new Mapper(); // Creating
$mapper->createMap(ProductDto::class); // Registering
$mapper->createMap(BuyerDto::class);

// Using
$buyer = new stdClass();
$buyer->name = "buyerName";
$buyer->age = "20";
$product = new stdClass();
$product->name = "propName";
$product->buyer = $buyer;

$productDto = $mapper->map($product, ProductDto::class);
```
Result
```json
// Before
{
    "name": "propName",
    "buyer": {
        "name": "buyerName",
        "age": "20"
    }
}

// After
{
    "name": "propName",
    "buyer": {
        "name": "buyerName",
        "age": 20
    }
}
```
---
## Creating linked array DTOs
```php
class ProductArrDto {
    public string $name;
    public array $buyer;
}

class BuyerDto {
    public string $name;
}
```
Creating the mapper and registering the Mapper
Since ProductArrDto uses an array we need to specify which class it needs to use\
We do this by using the specify function when creating the Map
```php
$mapper = new Mapper(); // Creating
$mapper->createMap(ProductArrDto::class) // Registering
    ->specify("buyer", BuyerDto::class); // <-- specifying here
$mapper->createMap(BuyerDto::class);
```
Result
```json
// Before
{
    "name": "propName",
    "buyer": [
        {
            "name": "buyerName"
        }
    ]
}

// After
{
    "name": "propName",
    "buyer": [
        {
            "name": "buyerName"
        }
    ]
}
```
# Docs
## Mapper Class
### Create a map for the a class
```
createMap(string className)
* className -> The name of the class to register the map for
```

### Map object to a registered class
```
map(mixed data, string className)
* data      -> The data to map to the class
* className -> The name of the class to map to
```

---
## MappedClass Class
### Customize the transformation from the Object to the class
```
from(Closure closure)
* closure -> A closure with first parameter being the From Object and the second parameter being the To Object
```
Example
```php
$mapper = new Mapper();
$mapper->createMap(UserDto::class)
    ->from(function ($from, $to) {
        $to->username = $from->username . " $from->id"
    };);
```
Result
```json
// Before
{
    "id": "1",
    "username": "username",
    "password": "password"
}

// After
{
    "id": "1",
    "username": "username 1",
    "password": "password"
}
```
### Ignore a property from the object
```
ignore(string propName)
* propName -> The property name to ignore when mapping
```
Example
```php
$mapper = new Mapper();
$mapper->createMap(UserDto::class)
    ->ignore("password")
```
Result
```json
// Before
{
    "id": "1",
    "username": "username",
    "password": "password"
}

// After
{
    "id": "1",
    "username": "username"
}
```
### Specify a linked array to a DTO
```
specify(string propName, string toClass)
* propName -> The property name to specify the link to
* toClass  -> The name of the class that the array represents
```

### Rename a property
```
renameProp(string propName, string renamedPropName)
* propName -> The property name to rename
* renamedPropName  -> The renamed property name
```
```php
class UserRenameDto {
    public string $id;
    public string $name;
    public string $password;
}
```
Creating the mapper, registering and using the Mapper
```php
$mapper = new Mapper(); // Creating
$mapper->createMap(UserRenameDto::class) // Registering
    ->renameProp("username", "name"); // Rename the property

// Using
$user = new stdClass();
$user->id = "1";
$user->username = "username";
$user->password = "password":

$userDto = $mapper->map($user, UserRenameDto::class);
```
Result
```json
// Before
{
    "id": "1",
    "username": "username",
    "password": "password"
}

// After
{
    "id": "1",
    "name": "username",
    "password": "password"
}
```

### Array Assoc to array with specific property
```
toArrayProp(string propName, string propToUse)
* propName -> The property name for the array
* propToUse-> The property to use when making the array
```
Creating the mapper, registering and using the Mapper
```php
$mapper = new Mapper(); // Creating
$mapper->createMap(ProductDto::class) // Registering
    ->toArrayProp("buyer", "name"); // Map to array
$mapper->createMap(BuyerDto::class);

// Using
$buyer = new stdClass();
$buyer->name = "buyerName";
$product = new stdClass();
$product->name = "propName";
$product->buyer = $buyer;

$productDto = $mapper->map($user, ProductDto::class);
```
Result
```json
// Before
{
    "name": "propName",
    "buyer": [
        {
            "name": "buyerName"
        }
    ]
}

// After
{
    "name": "propName",
    "buyer": [
        "buyerName"
    ]
}
```
