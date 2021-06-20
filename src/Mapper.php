<?php
namespace Zelak\Mapper;

use Error;
use Exception;
use Zelak\Mapper\MappedClass;

class Mapper {

    private array $mappedClasses;

    public function __construct()
    {
        $this->mappedClasses = array();
    }
    
    public function createMap(mixed $toName): MappedClass {
        $mapping = new MappedClass($toName);
        array_push($this->mappedClasses, $mapping);

        return $mapping;
    }
    
    private function getMapper(mixed $toName): ?MappedClass {
        foreach($this->mappedClasses as $mapping) {
            if ($mapping->getTo() != $toName)
                continue;

            return $mapping;
        }

        return NULL;
    }
    
    public function map(mixed $data, mixed $toName): mixed {
        $currentMapper = $this->getMapper($toName);
        
        if ($currentMapper == NULL)
            throw new Exception("No mapping found for $toName");
        
        $wasArray = is_array($data);
        $dataArray = $wasArray ? $data : array($data);
        $toArray = array();

        foreach($dataArray as $entity) {
            $toClass = new $toName();
            array_push($toArray, $currentMapper->doMapping($entity, $toClass, $this));
        }

        if (!$wasArray) return $toArray[0];
        return $toArray;        
    }
}