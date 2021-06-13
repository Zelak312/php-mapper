<?php
namespace Zelak\Mapper;

use Exception;
use phpDocumentor\Reflection\Types\Null_;
use Zelak\Mapper\MappedClass;

class Mapper {

    private array $mappedClasses;

    public function __construct()
    {
        $this->mappedClasses = array();
    }
    
    public function createMap(string $from, string $to): MappedClass {
        $mapping = new MappedClass($from, $to);
        array_push($this->mappedClasses, $mapping);

        return $mapping;
    }
    
    private function getMapper(mixed $to): ?MappedClass {
        if (is_array($to)) $to = $to[0];
        if (!isset($to->fromType)) return NULL;

        foreach($this->mappedClasses as $mapping) {
            if ($mapping->getFrom() != $to->fromType || $mapping->getTo() != $to::class)
                continue;

            return $mapping;
        }

        return NULL;
    }
    
    public function map(mixed $data, mixed $to): mixed {
        $currentMapper = $this->getMapper($to);
        
        if ($currentMapper == NULL) {
            throw new Exception("No mapping found for " . $data::class . " -> ". $to::class);
        }
        
        $wasArray = is_array($data);
        $dataArray = $wasArray ? $data : array($data);
        $toArray = array();

        foreach($dataArray as $entity) {
            $toClass = new $to();
            array_push($toArray, $currentMapper->doMapping($entity, $toClass, $this));
        }

        if (!$wasArray) return $toArray[0];
        return $toArray;        
    }
}