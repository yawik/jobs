<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2015 Cross Solution (http://cross-solution.de)
 * @author cbleek
 * @license   MIT
 */

namespace Jobs\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class ChannelOptions
 *
 * Jobs can be published on channels
 *
 * @package Jobs\Options
 */
class ChannelOptions extends AbstractOptions {

    /**
     * Unique key of the channel
     *
     * @var int $key
     */
    protected $key;

    /**
     * Price of the channel
     *
     * @var int $price
     */
    protected $price;

    /**
     * Currency of the price
     *
     * @var string $currency
     */
    protected $currency="€";

    /**
     * Tax of the channel
     *
     * @var int $tax
     */
    protected $tax=19;

    /**
     * Label of the Channel.
     *
     * @var string
     */
    protected $label;

    /**
     * days to publish a job posting
     *
     * @var int $publishDuration
     */
    protected $publishDuration=30;


    /**
     * Sets the unique key of a channel
     *
     * @param string $key
     * @return ChannelOptions
     */
    public function setKey($key)
    {
        $this->key=$key;
        return $this;
    }

    /**
     * Gets the unique key of a channel
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets the price of a channel
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Sets the price of a channel
     *
     * @param int $price
     * @return ChannelOptions
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Gets the currency of a price
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the currency of a price
     *
     * @param int $currency
     * @return ChannelOptions
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Gets the tax for a price
     *
     * @return string
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Sets the tax for a price
     *
     * @param int $tax
     * @return ChannelOptions
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
        return $this;
    }

    /**
     * Gets the label of a channel. Eg. "YAWIK Jobboard"
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the label of a channel
     *
     * @param string $label
     * @return ChannelOptions
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }


    /**
     * Gets the publish duration of a channel in days
     *
     * @return int
     */
    public function getPublishDuration()
    {
        return $this->publishDuration;
    }

    /**
     * Sets the publish duration of a job posting for a channel in days
     *
     * @param string $publishDuration
     * @return ChannelOptions
     */
    public function setPublishDuration($publishDuration)
    {
        $this->publishDuration = $publishDuration;
        return $this;
    }

}