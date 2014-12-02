<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'crud_model.php';
require_once 'crud_interface.php';

/**
 * Generic_model
 *
 * This model is a generic public CRUD model
 *	Use it like this: 
 * 	$this->load->model('generic_model');
 *	$my_model= new Generic_model("my_table_name"); 
 *	all data is passed in through arrays
 *
 * @see crud_model
 * @package crud
 * @author Chris Newton
 * @uses crud_model
 * @uses crud_interface
 * @version 1.1 11/15/08
 **/
class Generic_model extends Crud_model implements Crud_interface{

	/**
	 * $this->table_name
	 * this is the name of the database table to act on
	 * @var string
	 **/
	protected $table_name="";
	/**
	 * constructor
	 *
	 * @param string $table_name Name of the database table
	 * @return void
	 * @author Chris Newton
	 **/
	function __construct($table_name=NULL)
	{
	#	parent::Crud_model();
		$this->table_name = $table_name;
	}
	public function create($array)
	{
		return $this->_create($array);
	}
	public function read($id=NULL,$order_by=NULL,$order_direction='asc',$field_name=NULL,$string=NULL,$limit=NULL,$offset=NULL)
	{
		return $this->_read($id,$order_by,$order_direction,$field_name,$string,$limit,$offset);
	}
	public function update($id,$array)
	{
		return $this->_update($id,$array);
	}
	public function delete($id)
	{
		return $this->_delete($id);
	}
	public function search($fields_array,$search_terms_array,$limit=NULL,$offset=NULL,$like_or="like")
	{
		return $this->_search($fields_array,$search_terms_array,$like_or,$limit,$offset);
	}
	/**
	 * get_table_name
	 *
	 * @access public
	 * @return void
	 * @author Chris Newton
	 **/
	public function get_table_name()
	{
		return $this->_get_table_name();
	}
}
