<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube Token Class
 *
 * This class is used to generate a random token string
 *
 * Reference: http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string
 *
 * @package		InsQube
 * @subpackage	Libraries
 * @category	Libraries
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */

class Token
{
    /** @var string */
    protected $alphabet;

    /** @var int */
    protected $alphabetLength;


    /**
     * @param string $alphabet
     */
    public function __construct($alphabet = '')
    {
        if ('' !== $alphabet) {
            $this->setAlphabet($alphabet);
        } else {
            $this->setAlphabet(
                  implode(range('a', 'z'))
                . implode(range('A', 'Z'))
                . implode(range(0, 9))
            );
        }
    }

    /**
     * @param string $alphabet
     */
    public function setAlphabet($alphabet)
    {
        $this->alphabet = $alphabet;
        $this->alphabetLength = strlen($alphabet);
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    protected function getRandomInteger($min, $max)
    {
        $range = ($max - $min);

        if ($range < 0) {
            // Not so random...
            return $min;
        }

        $log = log($range, 2);

        // Length in bytes.
        $bytes = (int) ($log / 8) + 1;

        // Length in bits.
        $bits = (int) $log + 1;

        // Set all lower bits to 1.
        $filter = (int) (1 << $bits) - 1;

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));

            // Discard irrelevant bits.
            $rnd = $rnd & $filter;

        } while ($rnd >= $range);

        return ($min + $rnd);
    }

    /**
     * Generate Unique ID of supplied length
     *
     * @version 2
     * @param int $length
     * @return string
     */
    public static function v1($length)
    {
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $randomKey = $this->getRandomInteger(0, $this->alphabetLength);
            $token .= $this->alphabet[$randomKey];
        }

        return $token;
    }


    /**
     * Generate Unique ID of supplied length
     * without repeated ID
     *
     *
     * @version 2
     * @param int $lenght
     * @return string
     */
    public static function v2($lenght = 13)
    {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes"))
        {
            $bytes = random_bytes(ceil($lenght / 2));
        }
        elseif (function_exists("openssl_random_pseudo_bytes"))
        {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        }
        else
        {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }
}