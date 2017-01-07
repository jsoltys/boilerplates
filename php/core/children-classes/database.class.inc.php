<?php
	
	if ( !defined('BASEPATH'))
		{ exit('No direct script access allowed'); }
	
	/**
	 * Database class for handling variables
	 * 
	 *  Author: Jason Carl Soltys
	 *    Date: 25 December 2016
	 * Version: 2
	 * 
	 * Notes: see included read me for documentation.
	 * 
	 */
	
	// -------------------------------------------------------------------------
	
	/**
	 * Include parent file if needed
	 * 
	 */
	
	if ( !class_exists('zet_class_base'))
		{ include( BASEPATH . 'core/variable.class.inc.php' ); }
	
	if ( !function_exists('array_value'))
		{ include( BASEPATH . 'core/functions.global.inc.php' ); }
	
	// -------------------------------------------------------------------------
	
	class zet_database extends zet_class_base
	{
		private $db_link = null;
		private $db_name = null;
		
		function last_query()
		{
			return( $this->value_get('last_query'));
		}
		
		// query calls
		function query( $text )
		{
			$this->value_set( 'last_query', $text );
			
			$result = ( function_exists('mysqli_query'))
					? mysqli_query( $this->db_link, $text )
					: mysql_query( $text, $this->db_link );
			
			return( $result);
		}
		
		function fetch_array( $res, $type = MYSQL_ASSOC )
		{
			return( function_exists('mysqli_fetch_array'))
					? mysqli_fetch_array( $res, $type )
					: mysql_fetch_array( $res, $type );
		}
		
		protected function update( $name, $data, $keys = '', $use_keys = false, $default = 'OR' )
		{
			$query = 'UPDATE ^0 SET ^1^2';
			
			if ( is_array( $data ))
			{
				if ( $use_keys )
				{
					foreach( $data as $tkey => $tvalue )
					{
						$data[ $tkey ] = "{$tkey}={$tvalue}";
					}
				}
				
				$data = join( ', ', $data );
			}
			
			if ( is_array( $keys ))
			{
				$keys = join( ' ', $keys );
			}
			
			$keys = " WHERE {$keys}";
			
			$query = str_replace( '^0', $name, $query );
			$query = str_replace( '^1', $data, $query );
			$query = str_replace( '^2', $keys, $query );
			
			return( $this->query( $query ));
		}
		
		//protected 
		function record( $name, $data, $trim = '', $group = '', $order = '', $join = '', $limit = '', $offset = '' )
		{
			$query = 'SELECT ^1 FROM ^0^2^3^4^5^6^7';
			
			if ( is_array( $data )) { $data = join( ', ', $data ); }
			if ( !empty( $join  ) && is_array( $join )) { $join = ' ' . join( ' ', $join ); }
			if ( !empty( $trim  ) && is_array( $trim  )) { $trim  = ' WHERE ' . join( ' ', $trim ); }
			if ( !empty( $group ) && is_array( $group )) { $group = ' GROUP BY ' . join( ', ', $group ); }
			if ( !empty( $order ) && is_array( $order )) { $order = ' ORDER BY ' . join( ', ', $order ); }
			if ( !empty( $limit ))
			{
				$limit = " LIMIT {$limit}";
				if ( !empty( $offset )) { $offset = " OFFSET {$offset}"; }
			}
			
			$query = str_replace( '^0', $name, $query );
			$query = str_replace( '^1', $data, $query );
			$query = str_replace( '^2', $join, $query );
			$query = str_replace( '^3', $trim, $query );
			$query = str_replace( '^4', $group, $query );
			$query = str_replace( '^5', $order, $query );
			$query = str_replace( '^6', $limit, $query );
			$query = str_replace( '^7', $offset, $query );
			
			return( $this->query( $query, true ));
		}
		
		protected function insert( $name, $data, $use_keys = false, $multiple = false )
		{
			$result = false;
			
			if ( is_array( $data ))
			{
				// create multi-dimensional array
				if ( !$multiple ) { $tdata = $data; unset( $data ); $data[] = $tdata; }
				
				$query = 'INSERT INTO ^0^2 VALUES (^1);';
				
				foreach( $data as $rec )
				{
					$keys = '';
					
					if ( $use_keys )
					{
						foreach( $rec as $tkey => $tvalue )
						{
							$keys[] = $tkey;
							$rec[ $tkey ] = "{$tvalue}";
						}
						
						$keys = join( ',', $keys ); $keys = " ({$keys})";
					}
					
					$rec = join( "','", $rec ); $rec = "'{$rec}'";
					
					$tquery .= str_replace( '^0', $name, $query );
					$tquery  = str_replace( '^1', $rec , $tquery );
					$tquery  = str_replace( '^2', $keys, $tquery );
				}
				
				$result = $this->query( $tquery );
			}
			
			return( $result );
		}
		
		protected function create_table( $name, $data, $extra = '', $use_keys = false )
		{
			$query = 'CREATE TABLE IF NOT EXISTS ^0 (^1)^2;';
			
			if ( is_array( $data ))
			{
				if ( $use_keys )
				{
					foreach( $data as $tkey => $tvalue )
					{
						$data[ $tkey ] = "{$tkey} {$tvalue}";
					}
				}
				
				$data = join( ', ', $data );
			}
			
			if ( !empty( $extra )) { $extra = " {$extra}"; }
			
			$query = str_replace( '^0', $name, $query );
			$query = str_replace( '^1', $data, $query );
			$query = str_replace( '^2', $extra, $query );
			
			return( $this->query( $query ));
		}
		
		protected function truncate( $name )
		{
			return( $this->query("TRUNCATE {$name};"));
		}
		
		protected function remove( $name, $data )
		{
			$query = "DELETE FROM ^0 WHERE ^1;";
			
			if ( is_array( $data ))
				{ $data = join( ' ', $data ); }
			
			$query = str_replace( '^0', $name, $query );
			$query = str_replace( '^1', $data, $query );
			
			return( $this->query( $query ));
		}
		
		function drop( $name, $is_database = false )
		{
			return( $is_database )
				? $this->query("DROP DATABASE {$name};")
				: $this->query("DROP TABLE {$name};");
		}
		
		function select_database( $db_name )
		{
			if ( $this->query("USE {$db_name};"))
				{ $this->db_name = $db_name; }
		}
		
		// constructure
		function zet_database( $username, $password, $database = null, $host = 'localhost' )
		{
			$this->db_link = ( function_exists('mysqli_connect'))
				? @mysqli_connect( $host, $username, $password )
				: @mysql_connect( $host, $username, $password );
			
			if ( false !== $this->db_link )
				{ $this->select_database( $database ); }
			
			return( false !== $this->db_link );
		}
	}
	
?>