<?php

/** vim: ff=unix fenc=utf8 tw=80 ts=4 sw=4 et ai
 *
 * Bloom filter implementation using PHP's GMP extension
 *
 * @category    Zend
 * @package     Zend_Bloom
 * @copyright   Copyright (c) 2010 Telefónica I+D (http://www.tid.es)
 * @author      Iván -DrSlump- Montes <imv@tid.es>
 * @version     $Id$
 */
class Tid_Zend_BloomFilter_Classic_Gmp extends Tid_Zend_BloomFilter_Abstract
{
    protected $_gmp;

    protected function _init()
    {
        $this->_gmp = gmp_init(0, 2);
    }

    public function add($value)
    {
        $hashes = $this->_hashes($value);
        foreach ($hashes as $hash) {
            gmp_setbit($this->_gmp, $hash);
        }
    }

    public function has($value)
    {
        $hashes = $this->_hashes($value);
        if (function_exists('gmp_testbit')) {
            foreach ($hashes as $hash) {
                if (!gmp_testbit($this->_gmp, $hash)) return false;
            }
        } else {
            foreach ($hashes as $hash) {
                if (gmp_scan1($this->_gmp, $hash) !== $hash) return false;
            }
        }
        return true;
    }

    public function export($hex = false)
    {
        $result = gmp_strval($this->_gmp, 16);
        if (strlen($result) % 2) {
            $result = '0' . $result;
        }
        
        if (!$hex) {
            $result = pack('H*', $result);
        }
        return $result;
    }

    public function import($data, $hex = false)
    {
        if (!$hex) {
            $data = bin2hex($data);
        }

        $this->_gmp = gmp_init($data, 16);
    }
}