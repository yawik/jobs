<?php

namespace Jobs\Form;

use Zend\Form\Fieldset;
use Zend\Validator\StringLength as StringLengthValidator;
use Zend\Validator\EmailAddress as EmailAddressValidator;
use Zend\Validator\ValidatorInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Core\Repository\Hydrator;
use Core\Entity\Hydrator\EntityHydrator;
use Core\Entity\Hydrator\Strategy\ArrayToCollectionStrategy;

class JobFieldset extends Fieldset implements InputFilterProviderInterface
{
    
    public function getHydrator()
    {
        if (!$this->hydrator) {
            $hydrator = new EntityHydrator();
            /*
            $datetimeStrategy = new Hydrator\DatetimeStrategy();
            $datetimeStrategy->setHydrateFormat(Hydrator\DatetimeStrategy::FORMAT_MYSQLDATE);
            $hydrator->addStrategy('datePublishStart', $datetimeStrategy);
             */
            $this->setHydrator($hydrator);
        }
        return $this->hydrator;
    }
    
    public function getInputFilterSpecification()
    { 
        return array(
            'company' => array(
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                    new StringLengthValidator(1),
                ),
            ),
            'title' => array(
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                    new StringLengthValidator(5),
                ),
            ),
            'link' => array(
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                    new StringLengthValidator(5),
                ),
            ),
            'contactEmail' => array(
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),          
                'allow_empty' => True
            ),
            'datePublishStart' => array(
                ),
            'reference' => array(
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),          
                'allow_empty' => True
            ),
            
            'status' => array(
                'filters'  => array(
                    array('name' => 'Zend\Filter\StringTrim'),
                ),          
                'allow_empty' => True
            ),
            
            'camEnabled' => array(
                'filters'  => array(
                ),          
                'allow_empty' => True
            ),
        );
    }
    
    public function init()
    {
        $this->setName('job');
        $this->setAttribute('id', 'job');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'id'
        ));
        
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'applyId',
            'options' => array(
                'label' => 'applyId'
            ),
            'attributes' => array(
            //'id' => 'contact-title',
            )
        ));
        
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'company',
            'options' => array(
                'label' => 'company'
            ),
            'attributes' => array(
            //'id' => 'contact-title',
            ),
           
        ));
        
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'title',
            'options' => array(
                'label' => 'title'
            ),
            'attributes' => array(
            //'id' => 'contact-title',
            )
        ));
       
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'link',
            'options' => array(
                'label' => 'link'
            ),
            'attributes' => array(
            )
        ));
       
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'location',
            'options' => array(
                'label' => 'location'
            ),
            'attributes' => array(
            )
        ));
       
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'contactEmail',
            'options' => array(
                'label' => 'contactEmail'
            ),
            'attributes' => array(
            )
        ));
       
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'datePublishStart',
            'options' => array(
                'label' => 'datePublishStart'
            ),
            'attributes' => array(
            )
        ));
       
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'status',
            'options' => array(
                'label' => 'status'
            ),
            'attributes' => array(
            )
        ));
       
       $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'reference',
            'options' => array(
                'label' => 'reference'
            ),
            'attributes' => array(
            )
        ));
       
       $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'camEnabled',
            'options' => array(
                'label' => 'cam enabled',
                'value_options' => array(0,1, True, False)
            ),
            'attributes' => array(
            ),
        ));
    }
}