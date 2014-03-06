<?php

/** vim: ff=unix fenc=utf8 tw=80 ts=4 sw=4 et ai
 *
 * Implements a Counting Bloom filter.
 *
 * The size of the buckets (counters) is hard coded to be 8bits for performance
 * and implementation simplicity.
 *
 * @category    Zend
 * @package     Zend_Bloom
 * @copyright   Copyright (c) 2010 Telefónica I+D (http://www.tid.es)
 * @author      Iván -DrSlump- Montes <imv@tid.es>
 * @version     $Id$
 */
class Tid_Zend_BloomFilter_Counting extends Tid_Zend_BloomFilter_Abstract
{
    /** @var string */
    protected $_counters = '';

    protected function _init()
    {
        $this->_counters = str_repeat("\0", $this->_size);
    }

    public function add($value)
    {
        $hashes = $this->_hashes($value);
        foreach ($hashes as $hash) {
            $ord = min(255, ord($this->_counters[$hash])+1);
            $this->_counters[$hash] = chr($ord);
        }
    }

    public function has($value)
    {
        $hashes = $this->_hashes($value);
        foreach ($hashes as $hash) {
            if (ord($this->_counters[$hash]) === 0) {
                return false;
            }
        }

        return true;
    }

    public function remove($value)
    {
        $hashes = $this->_hashes($value);
        foreach ($hashes as $hash) {
            $ord = max(0, ord($this->_counters[$hash])-1);
            $this->_counters[$hash] = chr($ord);
        }
    }

    public function export($hex = false)
    {
        return $hex ? bin2hex($this->_counters) : $this->_counters;
    }

    public function import($data, $hex = false)
    {
        if ($hex) {
            $data = pack('H*', $data);
        }

        $this->_counters = str_pad($data, $this->_size, "\0", STR_PAD_RIGHT);
    }
}