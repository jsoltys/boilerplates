<?php
	
	/**
	 * Base class for handling variables
	 * 
	 *  Author: Jason Carl Soltys
	 *    Date: 24 December 2016
	 * Version: 3
	 * 
	 * Notes: see included read me for documentation.
	 * 
	 */
	
	// -------------------------------------------------------------------------
	
	class zet_class_base
	{
		private $internal = null;
		
		// private internal function for returning a reference to the value
		private function &navigate_tree( $temp, $path, $null_fail = false, $delimiter = '/' )
		{
			$local = explode( $delimiter, strtoupper( $path ));
			
			if ( is_null( $temp ))
				{ $temp  = &$this->internal; }
			
			foreach ( $local as $child )
			{
				$test = $temp;
				
				if ( !isset( $test[ $child ] ) && $null_fail )
					{ return false; }
				
				$temp  = &$temp[ $child ];
			}
			
			return( $temp );
		}
		
		function value_set( $path, $value, $append = false, $delimiter = '/' )
		{
			$handle = &$this->navigate_tree( null, $path, false, $delimiter );
			
			if ( $append )
			{
				if ( !is_null( $handle ) && !is_array( $handle ))
				{
					$temp = $handle;
					
					$handle = null;
					
					$handle[] = $temp;
				}
				
				$handle[] = $value;
			}
			else
			{
				$handle = $value;
			}
		}
		
		function value_sub( $arr, $path, $default = false, $delimiter = '/' )
		{
			if ( false !== ( $handle =  $this->navigate_tree( $arr, $path, true, $delimiter )))
				{ $default = $handle; }
			
			return( $default );
		}
		function value_get( $path, $default = false, $delimiter = '/' )
		{
			if ( false !== ( $handle =  $this->navigate_tree( null, $path, true, $delimiter )))
				{ $default = $handle; }
			
			return( $default );
		}
		
		function value_count( $path, $zerobase = false, $default = false, $delimiter = '/' )
		{
			if ( false === ( $handle =  $this->navigate_tree( null, $path, true, $delimiter )))
				{ return( $default ); }
			
			if ( is_array( $handle ) && $zerobase )
				{ return count( $handle ) - 1; }
			
			return count( $handle );
		}
		
		function clear_value( $path, $delimiter = '/' )
		{
			if ( '' === $path )
			{
				$this->clear_all();
				return true;
			}
			
			do
			{
				$list = explode( $delimiter, strtoupper( $path ));
				$last = array_pop( $list );
				$path = join( $delimiter, $list );
				
				if ( false === ( $handle = &$this->navigate_tree( null, $path, true, $delimiter )))
					{ return false; }
				
				if ( !isset( $handle[ $last ] ))
					{ return false; }
				
				unset( $handle[ $last ] );
			}
			while ( 0 <= count( $handle ));
			
			return true;
		}
		
		function export_string()
		{
			return serialize( $this->internal );
		}
		function export( $filepath, $write_over = true )
		{
			if ( file_exists( $filepath ) && ( true !== $write_over ))
				{ return false; }
			
			@file_put_contents( $filepath, $this->export_string());
			
			return file_exists( $filepath );
		}
		
		function import_string( $value )
		{
			$this->internal = unserialize( $value );
		}
		function import( $filepath )
		{
			if ( !file_exists( $filepath ) || !is_readable( $filepath ))
				{ return false; }
			
			$this->import_string( file_get_contents( $filepath ));
			
			return true;
		}
		
		function clear_all()
		{
			$this->internal = null;
		}
		
		function zet_class_base()
		{
			$this->clear_all();
		}
	}
	
?>