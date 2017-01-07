<?php
	
	define( 'BASEPATH', '../../' );
	
	if ( !defined('BASEPATH'))
		{ exit('No direct script access allowed'); }
	
	/**
	 * Blank class for 
	 * 
	 *  Author: 
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
	
?>
