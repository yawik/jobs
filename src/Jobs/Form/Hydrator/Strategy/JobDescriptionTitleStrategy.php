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

class JobDescriptionTitleStrategy implements StrategyInterface
{
    public function extract($value)
    {
        $result = null;
        if (isset($value->templateValues)) {
            $result = $value->templateValues->title;
        }
        return $result;
    }

    public function hydrate($value, $object = null)
    {
        if (isset($value['description-title'])) {
            $object->templateValues->title = $value['description-title'];
        }
        return;
    }
}
