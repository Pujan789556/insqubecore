<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extending Redis Cache Driver
 *
 * This class has been extended from Codeigniter's native redis caching driver.
 * This will let us use and extend other redis features, such as getting the
 * list of keys from a certain pattern etc.
 *
 * @package CodeIgniter
 * @subpackage Libraries
 * @author IP Bastola <ip.bastola@gmail.com>
 * @link
 */
class MY_Cache_redis extends CI_Cache_redis
{


    function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------------

    /**
     * Get list of keys matching the pattern
     *
     * @param   string  $pattern    Key Pattern
     * @return  array
     */
    public function keys($pattern)
    {
        return $this->_redis->keys($pattern);
    }

}
/* End of file MY_Cache_redis.php */
/* Location: ./application/libraries/Cache/drivers/MY_Cache_redis.php */
