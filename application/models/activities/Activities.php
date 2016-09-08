<?php
class Activities extends CI_Model
{
	function __construct()
	{
		parent::__construct();

		$this->_table = 'activities';
	}
	
	function save($data)
	{
		// Timestamps and Creators
		timestamp_create($data);
		
		$this->db->set($data);
		return $this->db->insert($this->_table);
	}	
}