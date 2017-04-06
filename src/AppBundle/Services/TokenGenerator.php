<?php
/**
 * @author Rangocard
 */

namespace AppBundle\Services;

class TokenGenerator
{
    /**
     * Generate token
     * @param int $min
     * @param int $max
     * @return string
     */
    public function generate($min = 10000, $max = 99999)
    {
        return rand($min, $max);
    }

    /**
     * Generate a more difficult token
     * @param int $min
     * @param int $max
     * @return string
     */
    public function generatePlus($length = 6, $min = 10000, $max = 99999)
    {
        return substr(md5(uniqid($this->generate($min, $max), true)), 10, $length);
    }
}