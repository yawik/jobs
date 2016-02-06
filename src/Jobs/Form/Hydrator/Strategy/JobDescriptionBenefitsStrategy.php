<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    weitz@cross-solution.de
 */

namespace Jobs\Form\Hydrator\Strategy;

use Zend\Hydrator\Strategy\StrategyInterface;

class JobDescriptionBenefitsStrategy implements StrategyInterface
{
    public function extract($value)
    {
        $result = null;
        if (isset($value->templateValues)) {
            $result = $value->templateValues->benefits;
        }
        return $result;
    }

    /**
     * @param mixed $value
     * @param null  $object
     */
    public function hydrate($value, $object = null)
    {
        if (isset($value['description-benefits'])) {
            $object->templateValues->benefits = $value['description-benefits'];
        }
        return;
    }
}
