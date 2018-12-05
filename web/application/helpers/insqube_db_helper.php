<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * InsQube MYSQLi DB Helper Functions
 *
 * This file contains helper functions related to store procedure call
 *
 * @package		InsQube
 * @subpackage	Helpers
 * @category	Helpers
 * @author		IP Bastola <ip.bastola@gmail.com>
 * @link
 */


// ------------------------------------------------------------------------

if ( ! function_exists('mysqli_store_procedure'))
{
	/**
	 * Run a store procedure and return result
	 *
	 * @return	string
	 */
    /**
     * Run a stored procedure and return result
     *
     * @param string $type select|insert|update|delete
     * @param string $sql
     * @param array|null $params
     * @return mixed
     */
	function mysqli_store_procedure( $type, $sql, $params = null )
	{
		$CI =& get_instance();

		/**
         * Step 1: Call Stored Procedure
         */
        $query = $CI->db->query($sql, $params);

        /**
         * Step 2: Get the Query Result
         *
         *  If select statment is there, you will get the query result,
         *  otherwise you will simply get affected rows
         */
        if(in_array($type, ['select', 'insert']))
        {
            $result = $query->result();
        }
        else
        {
            $result = $CI->db->affected_rows();
        }


        /**
         * Step 3
         *  1. Clean up the extra result of stored procedure
         *  2. Free the result
         *
         * !!! IMPORTANT: if you don't do this you will get "Commands out of sync; you can't run this command now" error
         */
        if (is_object($CI->db->conn_id))
        {
            @mysqli_next_result($CI->db->conn_id);
        }

        // Free the Result
        $query->free_result();



        return $result;
	}
}

// ------------------------------------------------------------------------



