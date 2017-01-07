<?php
	
	define( 'BASEPATH', '../../' );
	
	if ( !defined('BASEPATH'))
		{ exit('No direct script access allowed'); }
	
	/**
	 * Blank class for 
	 * 
	 *  Author: Jason Carl Soltys
	 *    Date: 
	 * Version: 
	 * 
	 * Notes: see included read me for documentation.
	 * 
	 */
	
	// -------------------------------------------------------------------------
	
	/**
	 * Include parent file if needed
	 * 
	 * core/{file-name.php}
	 * core/child-class/{file-name.php}
	 * 
	 */
	if ( !class_exists('zet_class_base'))
	{
		include( BASEPATH . 'core/variable.class.inc.php' );
	}
	
	// -------------------------------------------------------------------------
	
	class zet_custom_class extends zet_parent_class
	{
		
	}
	
	// -------------------------------------------------------------------------
	
	// $temp = new zet_custom_class();
	// array_output( $temp );
	
	/*
	
	include_once('php/class_children/database.blog.class.php');
	
	$db = new mk_db_mysql_base;
	
	$db->connect( '' );
	$db->blog_initialize();
	
	value_set( $path, $value, $append = false, $delimiter = '/' )
	value_sub( $arr, $path, $default = false, $delimiter = '/' )
	value_get( $path, $default = false, $delimiter = '/' )
	value_count( $path, $zerobase = false, $default = false, $delimiter = '/' )
	clear_value( $path, $delimiter = '/' )
	export_string()
	export( $filepath, $write_over = true )
	import_string( $value )
	import( $filepath )
	clear_all()
	
	*/
	
?>