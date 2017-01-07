<?php
	
	if ( !defined('BASEPATH'))
		{ exit('No direct script access allowed'); }
	
	/**
	 * Session manager for websites 
	 * 
	 *  Author: Jason Carl Soltys
	 *    Date: 28 December 2016
	 * Version: 1
	 * 
	 * Notes: see included read me for documentation.
	 * 
	 */
	
	// -------------------------------------------------------------------------
	
	/**
	 * Include parent file if needed
	 */
	 
	if ( !class_exists('zet_class_base'))
		{ include( BASEPATH . 'core/variable.class.inc.php' ); }
	
	if ( !function_exists('array_value'))
		{ include( BASEPATH . 'core/functions.global.inc.php' ); }
	
	// -------------------------------------------------------------------------
	
	class zet_session_manager extends zet_class_base
	{
		// ----------------------------------------------------------------------
		
		/**
		 * Session setting functions
		 *
		 */
		
		private function get_session_directory()
		{
			return( $this->value_get('setting/folder'));
		}
		function session_directory( $dir )
		{
			if ( !is_dir( $dir ))
				{ mkdir( $dir ); }
			
			if ( !is_writable( $dir ))
				{ die("{$dir} is not a usable folder!"); }
			
			$this->value_set( 'setting/folder', $dir );
		}
		
		protected function get_session_key()
		{
			return( $this->value_get('setting/session_key'));
		}
		function session_key( $salt = '', $algo = 'tiger192,3' )
		{
			$text   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$text  .= 'abcdefghijklmnopqrstuvwxyz';
			$text  .= '1234567890';
			
			$value  = date('YmdHis');
			$value .= $salt;
			
			for ( $i = 1; $i < 48; $i++ )
				{ $value .= substr( $text, rand( 0, strlen( $text )), 1 ); }
			
			$this->value_set( 'setting/session_key', hash( $algo, $value ));
		}
		
		protected function get_cookie_name()
		{
			return( $this->value_get('setting/cookie/name'));
		}
		function cookie_name( $value = 'session_id' )
		{
			$this->value_set( 'setting/cookie/name', $value );
		}
		
		protected function get_cookie_expire()
		{
			return( time() + $this->value_get('setting/cookie/expire'));
		}
		function cookie_expire( $days = 30, $hours = 24, $minutes = 60 )
		{
			$value = 60 * $minutes * $hours * $days;
			
			$this->value_set( 'setting/cookie/expire', $value );
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * File manager
		 */
		private function get_file_name( $key )
		{
			$path = $this->get_session_directory();
			$zkey = hash( 'ripemd128', $_SERVER['REMOTE_ADDR'].$key );
			
			return("{$path}/{$zkey}");
		}
		private function toss_session( $key )
		{
			$cookie = $this->get_cookie_name();
			$file   = $this->get_file_name( $key );
			
			setcookie( $cookie, $key, -1 );
			
			if ( is_file( $file ) && is_writable( $file ))
				{ unlink( $file ); }
		}
		private function bake_session( $mk_cookie )
		{
			$key  = $this->get_session_key();
			$file = $this->export( $this->get_file_name( $key ));
			
			if ( $file && $mk_cookie )
			{
				$cookie = $this->get_cookie_name();
				
				setcookie( $cookie, $key, $this->get_cookie_expire());
			}
		}
		
		function save_session()
		{
			$this->bake_session( false );
		}
		
		protected function open_session( $key )
		{
			return( $this->import( $this->get_file_name( $key )));
		}
		
		function valid_session( $key )
		{
			$file = $this->get_file_name( $key );
			
			return( is_file( $file ) && is_writable( $file ));
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * Custom variables
		 */
		
		function set_session_value( $idx, $value, $update = true )
		{
			$key  = $this->get_session_key();
			
			$this->value_set( "value/{$idx}", $value );
			
			if ( $update && $this->valid_session( $key ))
				{ $this->bake_session( false ); }
		}
		function get_session_value( $key )
		{
			return( $this->value_set("value/{$key}"));
		}
		function remove_session_value( $idx, $update = true )
		{
			$key  = $this->get_session_key();
			
			$this->clear_value("value/{$idx}");
			
			if ( $update && $this->valid_session( $key ))
				{ $this->bake_session( false ); }
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * Constructor
		 */
		
		function zet_session_manager( $destroy = false, $location = '../sessions', $cookie = 'session' )
		{
			$this->session_directory( $location );
			$this->cookie_name( $cookie );
			$this->cookie_expire();
			
			$key = array_value( $_COOKIE, $cookie );
			
			if ( $this->valid_session( $key ))
			{
				if ( $destroy )
				{
					$this->toss_session( $key );
					$this->session_key();
				}
				else
				{
					$this->open_session( $key );
				}
			}
			elseif ( false === $destroy )
			{
				$this->session_key();
				$this->bake_session( true );
			}
		}
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Session initialize sample
	 *
	
	$session = new zet_session_manager( $destroy = false, $location = '../sessions', $cookie = 'session' )
	
	***
	
	$session->set_session_value( $idx, $value, $update = true );
	$session->get_session_value( $key );
	$session->remove_session_value( $idx, $update = true );
	
	 */
	
?>