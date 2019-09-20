<?php 
namespace CreditData\Cake\Model\Behavior;

use \ArrayObject;
use Cake\ORM\Behavior;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
/**
 * This behavior capitalizes on your inability TO TYPE
 * THINGS IN ALL CAPS. That was a joke. It capitalizes
 * the values of specific fields on an entity before 
 * it's saved to the database.
 * 
 */
class CapitalizeBehavior extends Behavior
{
    /**
     * @var array fields An array contain names of fields to capitalize
     */
    protected $_fields = null;

    public function initialize(array $config)
    {
        $this->_fields = $config['fields'];
    }
    /**
     * Before save functionality
     * 
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        foreach($this->_fields as $field) {
            $entity->set($field, \strtoupper($entity->get($field)));
        }

        return $event;
    }
}
