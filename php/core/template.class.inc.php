<?php
	
	/* ********************************
	// Base class template
	// Version: 2
	// 
	//  Author: Jason Soltys
	//    Date: Oct 3, 2016
	*/
	
	class mk_class_base
	{
		private $internal = null;
		
		/* ************************* */
		function output_data( $content = null )
		{
			echo('<div><pre>');
			
			( !is_null( $content ))
					? print_r( $content )
					: print_r( $this );
			
			echo('</pre></div>');
		}
		
		/* ************************* */
		protected function value_format( $subject, $values )
		{
			if ( is_array( $values ))
			{
				$keys = array_keys( $values );
				
				foreach ( $keys as $tag => $key )
				{
					$keys[ $tag ] = "/%{$key}%/i";
				}
				
				$subject = preg_replace( $keys, $values, $subject );
			}
			
			return $subject;
		}
		
		/* ************************* */
		function value_clear( $path, $delimiter = '/' )
		{
			$local = explode( $delimiter, strtoupper( $path ));
			$temp = &$this->internal;
			$max = count( $local ) - 1;
			
			foreach ( $local as $uid => $child )
			{
				if ( $uid == $max )
				{
					unset( $temp[ $child ] );
				}
				else
				{
					$temp = &$temp[ $child ];
				}
			}
		}
		
		/* data reading functions ***************************** */
		function sub_value( $array, $path, $default = false, $delimiter = '/' )
		{
			$local = explode( $delimiter, strtoupper( $path ));
			
			$temp = ( is_null( $array ))
					? $this->internal
					: $array;
			
			foreach ( $local as $child )
			{
				if ( !isset( $temp[ $child ] ))
				{
					$temp = $default;
					
					break;
				}
				
				$temp = $temp[ $child ];
			}
			
			return $temp;
		}
		
		/* ************************* */
		function value_get( $path, $default = false, $delimiter = '/' )
		{
			$local = explode( $delimiter, strtoupper( $path ));
			$temp = $this->internal;
			
			foreach ( $local as $child )
			{
				if ( !isset( $temp[ $child ] ))
				{
					$temp = $default;
					
					break;
				}
				
				$temp = $temp[ $child ];
			}
			
			return $temp;
		}
		
		/* ************************* */
		function value_set( $path, $value = null, $append = false, $delimiter = '/' )
		{
			$local = explode( $delimiter, strtoupper( $path ));
			$temp = &$this->internal;
			$max = count( $local ) - 1;
			
			foreach ( $local as $uid => $child )
			{
				if ( $append && $uid == $max )
				{
					if ( isset( $temp[ $child ] ) && !is_array( $temp[ $child ] ))
					{
						$data = $temp[ $child ];
						unset( $temp[ $child ] );
						
						$temp[ $child ][0] = $data;
					}
					
					$eid = ( isset( $temp[ $child ] ))
							? count( $temp[ $child ] )
							: 0;
					
					$temp[ $child ][ $eid ] = $value;
				}
				else
				{
					if ( $uid == $max )
					{
						$temp[ $child ] = $value;
					}
					else
					{
						if ( !isset( $temp[ $child ] )) $temp[ $child ] = null;
						
						$temp = &$temp[ $child ];
					}
				}
			}
		}
		
		/* ************************* */
		function value_count( $path, $default = false )
		{
			$result = $default;
			
			if ( false !== ( $data = $this->value_get( $path, $default = false )))
			{
				if ( is_array( $data ))
				{
					$result = count( $data );
				}
				else
				{
					$result = 0;
				}
			}
			
			return $result;
		}
		
		/* ************************* */
		function clear_all()
		{
			unset( $this->internal );
		}
		
		/* ************************* */
		function export( $encode = true, $path = null )
		{
			$value = ( is_null( $path ))
					? $this->internal
					: $this->value_get( $path );
			
			return ( empty( $encode ))
					? $value
					: serialize( $value );
		}
		
		/* ************************* */
		function import( $value, $encode = true, $path = null )
		{
			$value = ( empty( $decode ))
					? $value
					: unserialize( $value );
			
			( is_null( $path ))
					? $this->internal = $value
					: $this->value_set( $path, $value );
		}
		
		/* ************************* */
		function open( $path )
		{
			if ( !function_exists('cache_document')) return( false );
			
			if ( false !== ( $temp = cache_document( $path ))) $this->import( $temp );
			
			return( false !== $temp );
		}
		
		/* ************************* */
		function save( $path, $override = false )
		{
			if ( !empty( $override ) || ( false != $this->value_get('debugger/cashe/enabled')))
			{
				if ( function_exists('cache_document')) return( cache_document( $path, $this->export()));
			}
		}
	}
	
?>