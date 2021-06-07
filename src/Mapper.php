<?php
namespace Zelak\Mapper;

use Exception;
use Zelak\Mapper\MappedClass;

class Mapper {

    private int $defaultPropertyLevel;
    private array $mappedClasses;

    public function __construct(int $defaultPropertyLevel = 1)
    {
        $this->defaultPropertyLevel = $defaultPropertyLevel;
        $this->mappedClasses = array();
    }

    private function getMapper(string $fromClassName, string $toClassName): ?MappedClass {
        foreach($this->mappedClasses as $mapping) {
            if ($mapping->getFrom() != $fromClassName || $mapping->getTo() != $toClassName)
                continue;

            return $mapping;
        }

        return NULL;
    }

    public function createMap(string $from, string $to, int $propertyLevel = NULL): MappedClass {
        if (!isset($propertyLevel)) $propertyLevel = $this->defaultPropertyLevel;

        $mapping = new MappedClass($from, $to, $propertyLevel);
        array_push($this->mappedClasses, $mapping);

        return $mapping;
    }
    
    public function map(mixed $from, string $to): mixed {
        $fromClass = is_object($from) || is_array($from) ? 
            (is_array($from) ?
                $from[0]::class : $from::class) : gettype($from);
        $currentMapper = $this->getMapper($fromClass, $to);
        
        if ($currentMapper == NULL) {
            throw new Exception("No mapping found for " . $fromClass . " -> $to");
        }
        
        $wasArray = is_array($from);
        $fromArray = $wasArray ? $from : array($from);
        $toArray = array();

        foreach($fromArray as $entity) {
            $toClass = class_exists($to) ? new $to() : "";
            array_push($toArray, $currentMapper->doMapping($entity, $toClass, $this));
        }

        if (!$wasArray) return $toArray[0];
        return $toArray;        
    }
}