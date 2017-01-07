<?php
	
	if ( !defined('BASEPATH'))
		{ exit('No direct script access allowed'); }
	
	/**
	 * XML parser class for reading and 
	 * 
	 *  Author: Jason Carl Soltys
	 *    Date: 26 December 2016
	 * Version: 2
	 * 
	 * Notes: see included read me for documentation.
	 * 
	 */
	
	define( 'zXML_URL' , 'url' );
	define( 'zXML_SURL', 'ssl' );
	define( 'zXML_FILE', 'doc' );
	define( 'zXML_DATA', 'dat' );
	
	// error codes
	define( 'zXML_UNKNOWN'    , -1 );
	define( 'zXML_NO_ERROR'   ,  0 );
	define( 'zXML_PARSE_ERROR',  5 );
	define( 'zXML_URL_ERROR'  , 10 );
	define( 'zXML_NO_DATA'    , 25 );
	define( 'zXML_FILE_ERROR1', 50 );
	
	// -------------------------------------------------------------------------
	
	/**
	 * Include parent file if needed
	 */
	
	if ( !class_exists('zet_class_base'))
		{ include( BASEPATH . 'core/variable.class.inc.php' ); }
	
	if ( !function_exists('array_value'))
		{ include( BASEPATH . 'core/functions.global.inc.php' ); }
	
	// -------------------------------------------------------------------------
	
	class zet_xml_parser extends zet_class_base
	{
		private $parser;
		
		// ----------------------------------------------------------------------
		
		/**
		 * Error functions
		 */
		
		function set_error_page( $code, $file )
		{
			$this->value_set( "error/page/{$code}", $file );
		}
		
		function set_error_folder( $folder )
		{
			$this->value_set( "error/dir", $folder );
		}
		
		protected function set_error_data( $value )
		{
			$this->value_set( 'error/code', $value );
			
			if ( zXML_PARSE_ERROR == $value )
			{
				$this->value_set( 'error/data/line  '
									 , xml_get_current_line_number( $this->parser ));
				$this->value_set( 'error/data/column'
									 , xml_get_current_column_number( $this->parser ));
			}
		}
		
		function get_error_page()
		{
			$code = $this->value_get('error/code');
			$file = $this->value_get("error/page/{$code}");
			$path = $this->value_get("error/dir");
			
			if ( false !== ( $path = get_local_file("{$path}/{$file}")))
			{ return file_get_contents( $path ); }
		}
		
		function has_error()
		{
			return( zXML_NO_ERROR !== $this->value_get('error/code'));
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * C-Sting functions
		 */
		
		function cstring( $arr, $name, $tag = 'DATA', $idx = 0, $default = false )
		{
			$path = ( is_null( $idx ))
				? "{$name}/{$tag}"
				: "{$name}/{$idx}/{$tag}";
			
			return( $this->value_sub( $arr, $path, $default ));
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * Certificate functions
		 */
		
		function set_ssl_certificate( $value )
		{
			$this->value_set( 'setting/certificate', $value );
		}
		
		function get_ssl_certificate()
		{
			return( $this->value_get('setting/certificate'));
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * Parsing functions
		 */
		
		private function build_path( $append = null )
		{
			$path = '';
			
			if ( false !== ( $data = $this->value_get('parse_path')))
			{
				foreach ( $data as $tarr )
				{
					if ( !empty( $path ))
						{ $path .= '/'; }
					
					$path .= $tarr['name'];
					
					if ( isset( $tarr['idex'] ))
						{ $path .= '/' . $tarr['idex']; }
				}
			}
			
			if ( !is_null( $append ))
			{
				if ( !empty( $path ))
					{ $path .= '/'; }
				
				$path .= $append;
			}
			
			return( $path );
		}
		
		private function path_append( $tag )
		{
			$idx1 = $this->value_count( 'parse_path', true, 0 );
			$idx2 = 0;
			
			if ( false !== ( $data = $this->value_get("parse_path/{$idx1}")))
			{
				$idx2 = $this->value_count( $this->build_path( $tag ), false, 0 );
				
				if ( $tag != array_value( $data, 'name' ))
					{ $idx1++; }
			}
			
			$data['name'] = $tag;
			$data['idex'] = $idx2;
			
			$this->value_set( "parse_path/{$idx1}", $data );
		}
		
		private function path_remove( $tag )
		{
			$offset = $this->value_count( 'parse_path', true );
			$data   = $this->value_get("parse_path/{$offset}");
			
			if ( $tag == array_value( $data, 'name' ))
				{ $this->clear_value("parse_path/{$offset}"); }
		}
		
		private function parse_open_tag( &$parser, $tag, $attr )
		{
			$this->path_append( $tag );
			
			if ( !empty( $attr ))
			{
				$path = $this->build_path();
				$this->value_set( "{$path}/attr", $attr, true );
			}
		}
		
		private function parse_close_tag( &$parser, $tag )
		{
			$this->path_remove( $tag );
		}
		
		private function parse_cstring( &$parser, $text )
		{
			if ( '' === trim( $text ))
				{ return; }
			
			$path = $this->build_path();
			$this->value_set( "{$path}/data", $text );
		}
		
		protected function parse( $text )
		{
			$this->parser = xml_parser_create();
			
			xml_set_object( $this->parser, $this );
			xml_set_element_handler( $this->parser, 'parse_open_tag', 'parse_close_tag' );
			xml_set_character_data_handler( $this->parser, 'parse_cstring' );
			
			return( xml_parse( $this->parser, $text, true ))
				? zXML_NO_ERROR
				: zXML_PARSE_ERROR;
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * File reading functions
		 */
		
		function open( $data, $method = XML_FILE )
		{
			$err  = zXML_NO_ERROR;
			$cert = null;
			
			switch ( $method )
			{
				
				case XML_SURL:
					$cert = $this->get_ssl_certificate();
				case XML_URL:
					if ( false === ( $data = send_curl_request( $data, false, $cert )))
						{ $err = zXML_URL_ERROR; }
				  break;
				
				case XML_FILE:
					if ( !is_file( $data ) || !is_readable( $data ))
						{ $err = zXML_FILE_ERROR1; break; }
					
					$data = file_get_contents( $data );
				  break;
				
			}
			
			$err = ( !empty( $data ))
				? $this->parse( $data )
				: zXML_NO_DATA;
			
			$this->set_error_data( $err );
			
			return( zXML_NO_ERROR === $err );
		}
		
		// ----------------------------------------------------------------------
		
		/**
		 * Constructor
		 */
		
		function zet_xml_parser( $data = null, $method = XML_FILE )
		{
			$this->set_error_folder('htdocs/template/error');
			
			$this->set_error_page( zXML_PARSE_ERROR, 'xml-error-parsing' );
			$this->set_error_page( zXML_URL_ERROR  , 'xml-error-bad-url' );
			$this->set_error_page( zXML_NO_DATA    , 'xml-error-no-data' );
			$this->set_error_page( zXML_FILE_ERROR1, 'xml-error-no-file' );
			$this->set_error_page( zXML_NO_ERROR   , 'xml-error-none'    );
			$this->set_error_page( zXML_UNKNOWN    , 'xml-error-unknown' );
			
			if ( !is_null( $data ))
				{ return( $this->open( $data, $method )); }
		}
	}
	
?>