<?php
namespace Zelak\Mapper;

use Closure;
use ReflectionClass;
use ReflectionProperty;
use Exception;
use Zelak\Mapper\Mapper;

class MappedClass {

    private string $to;

    private array $mappingClosures;
    private array $mappingIgnore;
    private array $mappingSpecify;

    public function __construct(string $toName)
    {
        $this->to = $toName;

        $this->mappingClosures = array();
        $this->mappingIgnore = array();
        $this->mappingSpecify = array();
    }

    public function getTo(): string {
        return $this->to;
    }

    public function from(Closure $closure): MappedClass {
        array_push($this->mappingClosures, $closure);
        return $this;
    }

    public function ignore(string $propName): MappedClass {
        array_push($this->mappingIgnore, $propName);
        return $this;
    }

    public function specify(string $propName, string $toClass): MappedClass {
        $this->mappingSpecify[$propName] = $toClass;
        return $this;
    }

    public function doMapping(mixed $data, mixed $toObject, Mapper $mapper): mixed {
        foreach($this->mappingClosures as $closure) {
            $closure($data, $toObject);
        }

        foreach($data as $key => $value) {
            if (isset($toObject->{$key}) ||
                !property_exists($toObject, $key) ||
                in_array($key, $this->mappingIgnore, true))
                continue;

            $valueAssign = $value;
            if (is_object($value)) {
                $rp = new ReflectionProperty($toObject, $key);
                $other = $rp->getType()->getName();
                $valueAssign = $mapper->map($value, $other);
            }

            if (is_array($value) && count($value) != 0 && is_object($value[0])) {
                if (!isset($this->mappingSpecify[$key]))
                    throw new Exception("No specified mapping found for " . $key . " -> Unknown, From " . $this->to);
                    
                $valueAssign = $mapper->map($value, $this->mappingSpecify[$key]);
            }
            
            $toObject->{$key} = $valueAssign;
        }

        return $toObject;
    }
}