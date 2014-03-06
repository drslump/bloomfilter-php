<?php

/** vim: ff=unix fenc=utf8 tw=80 ts=4 sw=4 et ai
 *
 * Bloom filter implementation using a simple string as backend.
 *
 * @category    Zend
 * @package     Zend_Bloom
 * @copyright   Copyright (c) 2010 Telefónica I+D (http://www.tid.es)
 * @author      Iván -DrSlump- Montes <imv@tid.es>
 * @version     $Id$
 */
class Tid_Zend_BloomFilter_Classic_String extends Tid_Zend_BloomFilter_Abstract
{
    /** @var string */
    protected $_bits = '';
    /** @var int */
    protected $_bytes = 0;

    protected function _init()
    {
        $bytes = ceil($this->getSize() / 8);
        $this->_bits = str_repeat("\0", $bytes);
        // To be compatible with the GMP implementation we count the bytes backwards
        $this->_bytes = $bytes - 1;
    }

    public function add($value)
    {
        $hashes = $this->_hashes($value);
        foreach ($hashes as $hash) {
            $byte = $this->_bytes - floor($hash/8);
            $mask = 1 << ($hash % 8);
            $this->_bits[$byte] = chr(ord($this->_bits[$byte]) | $mask);
        }
    }

    public function has($value)
    {
        $hashes = $this->_hashes($value);
        foreach ($hashes as $hash) {
            $byte = $this->_bytes - floor($hash/8);
            $mask = 1 << ($hash % 8);
            if (!(ord($this->_bits[$byte]) & $mask)) {
                return false;
            }
        }
        return true;
    }

    public function export($hex = false)
    {
        return $hex ? bin2hex($this->_bits) : $this->_bits;
    }

    public function import($data, $hex = false)
    {
        if ($hex) {
            $data = pack('H*', $data);
        }

        $bytes = ceil($this->_size / 8);
        $this->_bits = str_pad($data, $bytes, "\0", STR_PAD_RIGHT);
    }
}