<?php

/** vim: ff=unix fenc=utf8 tw=80 ts=4 sw=4 et ai
 *
 * Abstract class with interface and generic functionality for different
 * implementations of Bloom filters.
 *
 * @category    Zend
 * @package     Zend_Bloom
 * @copyright   Copyright (c) 2010 Telefónica I+D (http://www.tid.es)
 * @author      Iván -DrSlump- Montes <imv@tid.es>
 * @version     $Id$
 */
abstract class Tid_Zend_BloomFilter_Abstract
    implements ArrayAccess
{
    /**
     *
     * @var int
     */
    protected $_capacity = null;

    /**
     *
     * @var double
     */
    protected $_error = 0.01;

    /**
     *
     * @var int
     */
    protected $_size = null;

    /**
     *
     * @var int
     */
    protected $_k = null;

    /** @var int */
    protected $_hashSplitLen;
    /** @var int */
    protected $_hashTotalLen;
    /** @var int */
    protected $_hashRepeatFactor;

    /**
     *
     * @param int $capacity
     * @param double $error
     */
    public function __construct($capacity, $error = 0.01)
    {
        $this->_capacity = $capacity;
        $this->_error = $error;

        // Calculate the total bucket size and the needed hashes
        $this->_size = ceil(($capacity * log($error)) / log(1.0 / pow(2.0, log(2.0))));
        $this->_k = ceil(log(2.0) * $this->_size / $capacity);

        // Calculate how many hexadecimal digits we need from the hash
        $this->_hashSplitLen = strlen(dechex($this->_size));
        // Calculate the total size needed of hash string
        $this->_hashTotalLen = $this->_hashSplitLen * $this->_k;
        // Calculate how many times we need to repeat the hash string
        $this->_hashRepeatFactor = ceil($this->_hashTotalLen / 40);

        $this->_init();
    }

    /**
     * Internal method to initialize the implementation details
     */
    abstract protected function _init();

    /**
     * Adds a value to the filter
     *
     * @param string $value
     */
    abstract public function add($value);

    /**
     * Tests if a value is present in the filter
     *
     * @param string $value
     * @return boolean
     */
    abstract public function has($value);

    /**
     * Removes a value from the filter.
     *
     * Note: Only "counting" filters allow this operation
     *
     * @param string $value
     */
    public function remove($value)
    {
        throw new Exception('Unable to remove an element from a Bloom Filter');
    }

    /**
     * Exports the contents of the filter
     *
     * @param bool $hex If true the result is given as an hex string
     * @return string
     */
    abstract public function export($hex = false);

    /**
     * Imports the contents of the filter
     *
     * @param string $data
     * @param bool $hex If true the data should be an hex string
     */
    abstract public function import($data, $hex = false);

    /**
     * Internal function to compute the set of hashes for a value
     *
     * The strategy is to compute an expensive, cryptographically secure, hash
     * which is then splitted to generate the needed number of hashes (k). If
     * the hash is not long enough it'll be rotated.
     *
     * @param string $value
     * @return array
     */
    protected function _hashes($value)
    {
        $hash = sha1($value);
        $hash = str_repeat($hash, $this->_hashRepeatFactor);
        $hash = substr($hash, 0, $this->_hashTotalLen);
        $hashes = str_split($hash, $this->_hashSplitLen);

        foreach ($hashes as &$hash) {
            $hash = hexdec($hash) % $this->_size;
        }

        return $hashes;
    }

    /**
     * Obtain the filter capacity
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->_capacity;
    }

    /**
     * Obtain the size of the filter (number of buckets)
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * Obtain the error rate supported by the filter
     *
     * @return double
     */
    public function getErrorRate()
    {
        return $this->_error;
    }

    /**
     * Obtain the number of hash functions used in the filter
     *
     * @return int
     */
    public function getHashes()
    {
        return $this->_k;
    }


    // ArrayAccess implementation

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->has($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->add($offset);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}