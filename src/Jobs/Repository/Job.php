<?php

namespace Jobs\Repository;

use Core\Repository\AbstractRepository;
use Core\Entity\EntityInterface;
use Core\Repository\EntityBuilder\EntityBuilderAwareInterface;
use Core\Repository\PaginatorAdapter;
use Zend\ServiceManager\ServiceLocatorInterface;

class Job extends AbstractRepository implements EntityBuilderAwareInterface
{
    
    
    protected $builders;
    
    public function setEntityBuilderManager(ServiceLocatorInterface $entityBuilderManager)
    {
        $this->builders = $entityBuilderManager;
        return $this;
    }
     
    public function getEntityBuilderManager()
    {
        return $this->builders;
    }
	

	public function find($id, $mode = self::LOAD_LAZY)
    {
        $entity = $this->getMapper('job')->find($id);
        return $entity;
    }
    
    public function fetch()
    {
        $collection = $this->getMapper('job')->fetch();
        return $collection;
    }
    
    public function getPaginatorAdapter(array $propertyFilter, $sort)
    {
    
        $query = array();
        foreach ($propertyFilter as $property => $value) {
            if (in_array($property, array('applyId'))) {
                $query[$property] = new \MongoRegex('/^' . $value . '/');
            }
        }
        $cursor = $this->getMapper('job')->getCursor($query); //, array('cv'), true);
        $cursor->sort($sort);
        return new PaginatorAdapter($cursor, $this->builders->get('job'));
    }
    
    public function save(EntityInterface $entity)
    {
        $this->getMapper('job')->save($entity);
    }
    
    
}