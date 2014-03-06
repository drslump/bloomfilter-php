<?php

/** vim: ff=unix fenc=utf8 tw=80 ts=4 sw=4 et ai
 *
 * Implements a Bloom filter in its more classical notion.
 *
 * This class is a proxy, the actual implementation to use depends on the
 * availability of the GMP extension. If not found the bit operations are
 * performed manually over a PHP string.
 *
 * @category    Zend
 * @package     Zend_Bloom
 * @copyright   Copyright (c) 2010 Telefónica I+D (http://www.tid.es)
 * @author      Iván -DrSlump- Montes <imv@tid.es>
 * @version     $Id$
 */
class Tid_Zend_BloomFilter_Classic extends Tid_Zend_BloomFilter_Abstract
{
    /** @var string */
    static public $implementation = null;

    protected $_instance;

    protected function _init()
    {
        $implementation = self::$implementation;
        if (empty($implementation)) {
            if (function_exists('gmp_init')) {
                $implementation = 'Tid_Zend_BloomFilter_Classic_Gmp';
            } else {
                $implementation = 'Tid_Zend_BloomFilter_Classic_String';
            }
        }

        $this->_instance = new $implementation($this->_capacity, $this->_error);
    }

    public function add($value)
    {
        $this->_instance->add($value);
    }

    public function has($value)
    {
        return $this->_instance->has($value);
    }

    public function export($hex = false)
    {
        return $this->_instance->export($hex);
    }

    public function import($data, $hex = false)
    {
        $this->_instance->import($data, $hex);
    }
}