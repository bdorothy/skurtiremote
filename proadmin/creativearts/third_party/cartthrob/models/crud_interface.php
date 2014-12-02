<?php
/**
 * Crud_interface
 *
 * requires standard C.R.U.D functions be available in consistent format
 * create
 * read
 * update
 * delete
 * 
 * @package crud
 * @author Chris Newton
 * @version 2.1 11/15/08
 **/

interface Crud_interface
{
	public function create($array);
	public function read($id=NULL,$order_by=NULL,$order_direction='asc',$field_name=NULL,$string=NULL,$limit=NULL,$offset=NULL);
	public function update($id,$array);
	public function delete($id);
}
 // END interface 