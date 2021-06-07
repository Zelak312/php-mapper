<?php
namespace Zelak\Mapper;

use Closure;
use ReflectionClass;
use ReflectionProperty;
use Exception;
use Zelak\Mapper\Mapper;

class MappedClass {

    private string $from;
    private string $to;
    private int $propertyLevel;

    private array $mappingClosures;
    private array $mappingIgnore;
    private array $mappingSpecify;

    public function __construct(string $from, string $to, int $propertyLevel)
    {
        $this->from = $from;
        $this->to = $to;
        $this->propertyLevel = $propertyLevel;

        $this->mappingClosures = array();
        $this->mappingIgnore = array();
        $this->mappingSpecify = array();
    }

    public function getFrom(): string {
        return $this->from;
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

    public function doMapping(mixed $from, mixed $to, Mapper $mapper): mixed {
        foreach($this->mappingClosures as $closure) {
            $return  = $closure($from, $to);
            if (isset($return)) return $return;
        }

        if ((!is_object($from) && !is_array($from)) ||
            (!is_object($to) && !is_array($to)))
            throw new Exception("No from (function) registered that returns the data for $this->from -> $this->to conversion");

        $reflect = new ReflectionClass($from);
        $props   = $reflect->getProperties($this->propertyLevel);
        foreach($props as $prop) {
            $prop->setAccessible(true);
            $key = $prop->name;
            $value = $prop->getValue($from);

            if (isset($to->{$key}) ||
                !property_exists($to, $key) ||
                in_array($key, $this->mappingIgnore, true))
                continue;

            $valueAssign = $value;
            if (is_object($value)) {
                $rp = new ReflectionProperty($to::class, $key);
                $valueAssign = $mapper->map($value, $rp->getType()->getName());
            }

            if (is_array($value) && count($value) != 0 && is_object($value[0])) {
                if (!isset($this->mappingSpecify[$key]))
                    throw new Exception("No specified mapping found for " . $key . " -> Unknown, From " . $this->from);
                    
                $valueAssign = $mapper->map($value, $this->mappingSpecify[$key]);
            }
            
            $to->{$key} = $valueAssign;
        }

        return $to;
    }
}