<?php
	
	/**
	 * Custom functions for use with mk file add ons
	 * 
	 *  Author: Jason Carl Soltys
	 *    Date: 24 December 2016
	 * Version: 3
	 * 
	 * Notes: see included read me for more information.
	 * 
	 */
	
	// -------------------------------------------------------------------------
	
	/**
	 * File folder functions for both local and remote access.
	 * 
	 */
	
	// -------------------------------------------------------------------------
	
	/**
	 * Folder file functions
	 * 
	 */
	define( 'MK_ERROR_FOLDER', 'template/error' );
	define( 'MK_THEME_FOLDER', 'template/theme' );
	
	// locate external files in local directory
	define( 'LOCAL_LIST_DEFAULT', 'php,html,htm,text,txt,css' );
	define( 'LOCAL_LIST_IMAGE'  , 'svg,png,jpeg,jpg,gif' );
	define( 'LOCAL_LIST_VIDEO'  , 'mp4,mov,avi,wav' );
	
	function get_local_file( $filepath, $default = false, $list = null )
	{
		if ( is_null( $list ))
			{ $list = LOCAL_LIST_DEFAULT; }
		
		if ( !is_array( $list ))
			{ $list = explode( ',', $list ); }
		
		if ( !is_file( $filepath ))
		{
			foreach ( $list as $ext )
			{
				$temp = "{$filepath}.{$ext}";
				
				if ( is_file( $temp ) && is_readable( $temp ))
				{
					$default = $temp;
					break;
				}
			}
		}
		
		return( $default );
	}
	
	// locate error documents
	function get_error_file( $error_code, $dir = null )
	{
		if ( is_null( $dir ))
			{ $dir = MK_ERROR_FOLDER; }
		
		return( get_local_file("{$dir}/error-{$error_code}"));
	}
	
	// locate theme documents
	function get_theme_file( $filename, $theme = null, $import = false, $dir = null )
	{
		if ( is_null( $dir ))
			{ $dir = MK_THEME_FOLDER; }
		
		if ( is_null( $theme ))
			{ $theme = 'default'; }
		
		if ( false === ( $local = get_local_file("{$dir}/{$theme}/{$filename}")))
			{ return false; }
		
		return( $import )
			? file_get_contents( $local )
			: $local;
	}
	
	// send request to remote server with curl
	function send_curl_request( $url, $default = false, $ssl = null )
	{
		if ( false !== ( $ch = curl_init()))
		{
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			
			if ( !is_null( $ssl ))
			{
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
				curl_setopt( $ch, CURLOPT_CAINFO, getcwd() . $ssl );
			}
			
			if ( false !== ( $temp = curl_exec( $ch )))
				{ $default = $temp; }
			
			curl_close( $ch );
		}
		
		return( $default );
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Numbers functions
	 * 
	 */
	
	function increment( &$value, $step = 1 )
	{
		$value = ( $value + $step );
		
		return( $value );
	}
	
	function decrement( &$value, $step = 1 )
	{
		$value = ( $value - $step );
		
		return( $value );
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Array functions
	 * 
	 */
	
	// function for outputing an array
	function array_output( $arr )
	{
		$content = print_r( $arr, true );
		
		$content = create_element( 'pre', $content );
		$content = create_element( 'div', $content );
		
		echo( $content );
	}
	
	// function to check for a value in an array
	function array_value( $arr, $key, $default = false, $allow_empty = false )
	{
		if ( !isset( $arr[ $key ] ))
			{ return( $default ); }
		
		if (  empty( $arr[ $key ] ) && false === $allow_empty )
			{ return( $default ); }
		
		return( $arr[ $key ] );
	}
	
	// function to check for a value in the global "$_GET" variable
	function get_value( $key, $default = false, $allow_empty = false )
	{
		return( array_value( $_GET, $key, $default, $allow_empty ));
	}
	
	// function to check for a value in the global "$_POST" variable
	function post_value( $key, $default = false, $allow_empty = false )
	{
		return( array_value( $_POST, $key, $default, $allow_empty ));
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * String functions
	 * 
	 */
	
	// simple string repeater function
	function repeat_string( $text, $count = 1 )
	{
		if ( 2 > $count )
			{ return( $text ); }
		
		$ztext = $text;
		
		for ( $i = 1; $i < $count; $i++ )
			{ $ztext .= $text; }
		
		return( $ztext );
	}
	
	// html element builder
	function create_element( $tag, $content, $attr = null, $allow_condenced = false )
	{
		$list = '';
		
		if ( is_array( $attr ))
		{
			foreach( $attr as $key => $value )
				{ $list .= " {$key}=\"{$value}\""; }
		}
		elseif ( is_string( $attr ))
		{
			$list = " {$attr}";
		}
		
		$template = ( !empty( $content ) || ( false === $allow_condenced ))
			? "<^0^2>^1</^0>"
			: "<^0^2 />";
		
		$template = str_replace( '^0', $tag    , $template );
		$template = str_replace( '^1', $content, $template );
		$template = str_replace( '^2', $list   , $template );
		
		return( $template );
	}
	
?>