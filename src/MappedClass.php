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
    private array $mappingRename;
    private array $mappingToArrayProp;

    public function __construct(string $toName)
    {
        $this->to = $toName;

        $this->mappingClosures = array();
        $this->mappingIgnore = array();
        $this->mappingSpecify = array();
        $this->mappingRename = array();
        $this->mappingToArrayProp = array();
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

    public function renameProp(string $initialName, string $renamedName): MappedClass {
        $this->mappingRename[$initialName] = $renamedName;
        return $this;
    }

    public function toArrayProp(string $propName, string $propToUse): MappedClass {
        $this->mappingToArrayProp[$propName] = $propToUse;
        return $this;
    }

    public function doMapping(mixed $data, mixed $toObject, Mapper $mapper): mixed {
        foreach($this->mappingClosures as $closure) {
            $closure($data, $toObject);
        }

        foreach($data as $key => $value) {
            // Rename
            $dtoPropName = $key;
            if (isset($this->mappingRename[$key]))
                $dtoPropName = $this->mappingRename[$key];

            if (isset($toObject->{$dtoPropName}) ||
                !property_exists($toObject, $dtoPropName) ||
                in_array($key, $this->mappingIgnore, true))
                continue;


            $valueAssign = $value;
            if (is_object($value)) {
                $rp = new ReflectionProperty($toObject, $dtoPropName);
                $other = $rp->getType()->getName();
                $valueAssign = $mapper->map($value, $other);
            }

            if (is_array($value) && count($value) != 0 && is_object(array_values($value)[0])) {
                if (!isset($this->mappingSpecify[$key]))
                    throw new Exception("No specified mapping found for " . $key . " -> Unknown, From " . $this->to);
                    
                $valueAssign = $mapper->map($value, $this->mappingSpecify[$key]);
            }
            
            if (isset($this->mappingToArrayProp[$key])) {
                $arr = $valueAssign;
                if (!is_array($valueAssign)) $arr = array_values($valueAssign);

                $arr = array_map(function ($item) use ($key) {
                    return $item->{$this->mappingToArrayProp[$key]};
                }, $arr);

                $toObject->{$dtoPropName} = $arr;  
            } else $toObject->{$dtoPropName} = $valueAssign;
        }

        return $toObject;
    }
}