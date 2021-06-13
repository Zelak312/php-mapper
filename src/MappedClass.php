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

    private array $mappingClosures;
    private array $mappingIgnore;
    private array $mappingSpecify;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;

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
            $closure($from, $to);
        }

        foreach($from as $key => $value) {
            if (isset($to->{$key}) ||
                !property_exists($to, $key) ||
                in_array($key, $this->mappingIgnore, true))
                continue;

            $valueAssign = $value;
            if (is_object($value)) {
                $rp = new ReflectionProperty($to::class, $key);
                $other = new ($rp->getType()->getName())();
                $valueAssign = $mapper->map($value, $other);
            }

            if (is_array($value) && count($value) != 0 && is_object($value[0])) {
                if (!isset($this->mappingSpecify[$key]))
                    throw new Exception("No specified mapping found for " . $key . " -> Unknown, From " . $this->from);
                    
                $valueAssign = $mapper->map($value, new ($this->mappingSpecify[$key])());
            }
            
            $to->{$key} = $valueAssign;
        }

        return $to;
    }
}