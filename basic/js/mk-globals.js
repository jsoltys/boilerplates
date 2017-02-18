/**
 * Global JavaScript file for mk functions
 * 
 *  Author: Jason Soltys
 *    Date: 20 January 2016
 * Revised: 5 Februrary 2017
 * Version: 3
 * 
 */

/**
 * Socket function
 */

var get = function( url )
{
	// Returns a new promise
	return new Promise( function ( resolve, reject )
	{
		// Create a socket and set url to request
		var socket;

		( window.XMLHttpRequest )
			? socket = new window.XMLHttpRequest()
			: socket = new ActiveXObject('Microsoft.XMLHTTP');
		
		socket.open( 'GET', url );
		
		socket.onload = function()
		{
			if ( 200 === socket.status )
			{
				resolve( socket.response );
			}
			else
			{
				reject( Error( socket.statusText ));
			}
		};
		
		// send request
		socket.send();
	} );
};

/**
 * My custom object with functions
 */

function library( selector )
{
	/**
	 * Initialization functions
	 */
	
	function check_selector( selector )
	{
		switch( typeof selector )
		{
			
			case 'string':
				switch( selector.substr( 0, 1 ))
				{
					
					case '#':
						selector = document.getElementById( selector.substr(1));
					  break;
					case '.':
						selector = document.getElementsByClassName( selector.substr(1));
					  break;
					case ':':
						selector = document.getElementsByName( selector.substr(1));
					  break;
					default:
						selector = document.getElementsByTagName( selector );
					  break;
					
				}
			  break;
			case 'object':
			  break;
			default:
				selector = document.body;
			  break;
			
		}
		
		return( selector );
	}
	
	var verifier = function( selector )
	{
		selector = check_selector( selector );
		
		if ( HTMLCollection.prototype.isPrototypeOf( selector ) ||
			  NodeList.prototype.isPrototypeOf( selector ) ||
			  Array.isArray( selector ))
		{
			for ( var i = 0; i < selector.length; i++ )
			{
				this[i] = selector[i];
			}
			
			this.length = selector.length;
		}
		else
		{
			this[0] = selector;
			
			this.length = 1;
		}
	}
	
	/**
	 * Private functions available only to this function
	 *
	 * These functions are used to validate indexes and keys
	 *
	 */
	
	function is_valid_index( obj, idx )
	{
		if ( undefined === idx )
				{ idx = 0; }
		
		return( undefined !== obj[ idx ] )
				? idx
				: false;
	}
	
	function is_valid_key( obj, path )
	{
		path = path.split('/');
		
		if ( path.forEach )
		{
			path.forEach( function( key )
			{
				obj = obj[ key ];
				
				if ( undefined === obj )
						{ return; }
			} );
		}
		else
		{
			for ( var i = 0; i < path.length; i++ )
			{
				obj = obj[ key ];
				
				if ( undefined === obj )
						{ return; }
			}
		}
		
		return( obj );
	}
	
	function is_valid_item( obj, idx, path )
	{
		if ( false !== ( idx = is_valid_index( obj, idx )))
				{ return( is_valid_key( obj[ idx ], path )); }
		
		return false;
	}
	
	function add_listener( obj, eventType, callback )
	{
		( obj.addEventListener )
				? obj.addEventListener( eventType, callback )
				: obj.attachEvent( 'on'+eventType, callback );
	}
	function remove_listener( obj, eventType, callback )
	{
		( obj.removeEventListener )
				? obj.removeEventListener( eventType, callback )
				: obj.detachEvent( 'on'+eventType, callback );
	}
	
	function set_value( obj, idx, value, path )
	{
		if ( false !== ( idx = is_valid_index( obj, idx )))
		{
			path = path.split('/');
			
			obj = obj[idx];
			
			for ( var i = 0; i < ( path.length - 1 ); i++ )
			{
				if ( undefined !== obj[ path[i]] )
				{
					obj = obj[ path[i]];
				}
			}
			
			obj[ path[ path.length - 1 ]] = value;
		}
	}
	
	/**
	 * Public functions available using:
	 *
	 * library().name_of_function
	 */
	
	verifier.prototype = {
		
		/**
		 * Functions for checking operating systems and browsers
		 */
		
		is_ios: function( min_version )
		{
			var list = ['iPad', 'iPhone', 'iPod'];
			var passed = false;
			
			if ( -1 < list.indexOf( navigator.platform ))
			{
				if ( min_version )
				{
					var version = navigator.appVersion.match(/OS ([\d_]+)/i);
					
					if ( 1 < version.length )
					{
						min_version = String( min_version ).split('.');
						cur_version = version[1].split('_');
						
						for ( var i = 0; i < min_version.length; i++ )
						{
							if ( undefined === cur_version[i] )
									{ cur_version[i] = 0; }
							
							min_version[i] = Number( min_version[i] );
							cur_version[i] = Number( cur_version[i] );
							
							if ( cur_version[i] > min_version[i] )
							{
								return true;
							}
							else if ( cur_version[i] === min_version[i] )
							{
								passed = true;
							}
							else
							{
								return false;
							}
						}
					}
				}
				else
				{
					passed = true;
				}
			}
			
			return( passed );
		},
		
		ms_document_mode: function( min_version )
		{
			var doc_version = document.documentMode;
			
			return( undefined !== doc_version )
					? ( min_version < doc_version )
					: true;
		},
		
		/**
		 * Viewport value functions
		 */
		
		screen_width: function()
		{
			var result = 0;
			
			if ( undefined === ( result = is_valid_key( window, 'innerWidth' )))
			{
				if ( undefined === ( result = is_valid_key( document, 'documentElement/clientWidth' )))
				{
					result = document.body.clientWidth;
				}
			}
			
			return( result );
		},
		screen_height: function()
		{
			var result = 0;
			
			if ( undefined === ( result = is_valid_key( window, 'innerHeight' )))
			{
				if ( undefined === ( result = is_valid_key( document, 'documentElement/clientHeight' )))
				{
					result = document.body.clientHeight;
				}
			}
			
			return( result );
		},
		
		/**
		 * Document value functions
		 */
		
		url_query_value: function( key )
		{
			if ( '' === window.location.search )
			{
				return ( undefined === key )
						? ''
						: {};
			}
			
			var data = window.location.search.substr(1).split('&');
			var obj = {};

			for ( var i = 0; i < data.length; i++ )
			{
				var temp = data[i].split('=');

				obj[ temp[0]] = temp[1];
			}
			
			return ( undefined !== value )
					? obj[ value ]
					: obj;
		},
		
		scroll_x: function()
		{
			var result;
			
			if ( undefined === ( result = is_valid_key( window, 'pageXOffset' )))
			{
				if ( undefined === ( result = is_valid_key( document.body, 'scrollLeft' )))
				{
					if ( undefined === ( result = is_valid_key( document, 'documentElement/scrollLeft' )))
					{
						result = 0;
					}
				}
			}
			
			return( result );
		},
		scroll_y: function()
		{
			var result;
			
			if ( undefined === ( result = is_valid_key( window, 'pageYOffset' )))
			{
				if ( undefined === ( result = is_valid_key( document.body, 'scrollTop' )))
				{
					if ( undefined === ( result = is_valid_key( document, 'documentElement/scrollTop' )))
					{
						result = 0;
					}
				}
			}
			
			return( result );
		},
		document_height: function( exclude_viewport )
		{
			var html = document.getElementsByTagName('html')[0];
			var result = ( exclude_viewport )
					? is_valid_key( html, 'scrollHeight' ) - this.screen_height()
					: is_valid_key( html, 'scrollHeight' );
			
			
			return( 0 > result )
					? 0
					: result;
		},
		document_width: function( exclude_viewport )
		{
			var html   = document.getElementsByTagName('html')[0];
			var result = ( exclude_viewport )
					? is_valid_key( html, 'scrollWidth' ) - this.screen_width()
					: is_valid_key( html, 'scrollWidth' );
			
			
			return( 0 > result )
					? 0
					: result;
		},
		
		/**
		 * Dom functions
		 */
		
		on: function( eventType, callback, idx )
		{
			if ( undefined === idx )
			{
				for( var i = 0; i < this.length; i++ )
				{
					add_listener( this[i], eventType, callback );
				}
			}
			else
			{
				if ( false !== ( idx = is_valid_index( idx )))
				{
					add_listener( this[idx], eventType, callback );
				}
			}
			
			return this;
		},
		off: function( eventType, callback, idx )
		{
			if ( undefined === idx )
			{
				for( var i = 0; i < this.length; i++ )
						{ remove_listener( this[i], eventType, callback ); }
			}
			else
			{
				if ( false !== ( idx = is_valid_index( idx )))
						{ remove_listener( this[idx], eventType, callback ); }
			}
			
			return this;
		},
		
		has_style: function( key, idx )
		{
			return( is_valid_item( this, idx, 'style/'+key ))
		},
		style: function( key, idx, value )
		{
			var result = [];
			
			for ( var i = 0; i < this.length; i++ )
			{
				if ( undefined !== idx && i !== idx )
						{ continue; }
				
				if ( undefined === value )
				{
					result.push( this.has_style( key, i ));
				}
				else
				{
					set_value( this, i, value, 'style/'+key );
				}
			}
			
			if ( undefined === value && undefined !== idx )
					{ result = result[0]; }
			
			return( undefined !== value )
					? this
					: result;
		},
		
		left: function( idx )
		{
			return( is_valid_item( this, idx, 'offsetLeft' ));
		},
		top: function( idx )
		{
			return( is_valid_item( this, idx, 'offsetTop' ));
		},
		width: function( idx, offset_width )
		{
			return( offset_width )
					? is_valid_item( this, idx, 'clientWidth' ) + this.left()
					: is_valid_item( this, idx, 'clientWidth' );
		},
		height: function( idx, offset_height )
		{
			return( offset_height )
					? is_valid_item( this, idx, 'clientHeight' ) + this.top()
					: is_valid_item( this, idx, 'clientHeight' );
		},
		
		on_screen: function( idx )
		{
			var result = [];
			
			for( var i = 0; i < this.length; i++ )
			{
				if ( undefined !== idx && i !== idx )
						{ continue; }
				
				if ((( this.left(i) - this.scroll_x()) < this.screen_width()) &&
					  ( this.width( i, true ) > this.scroll_x()) &&
					 (( this.top(i) - this.scroll_y()) < this.screen_height()) &&
					  ( this.height( i, true ) > this.scroll_y()))
				{
					result.push( this[i] );
				}
			}
			
			return( result );
		},
		
		dataset: function( key, idx, value )
		{
			var result = [];
			
			for ( var i = 0; i < this.length; i++ )
			{
				if ( undefined !== idx && i !== idx )
						{ continue; }
				
				if ( undefined === value )
				{
					( this[i].dataset )
							? result.push( this[i].dataset[key] )
							: result.push( this[i].getAttribute( 'data-'+key ));
				}
				else
				{
					( this[i].dataset )
							? this[i].dataset[key] = value
							: this[i].getAttribute( 'data-'+key, value );
				}
			}
			
			if ( undefined === value && undefined !== idx )
					{ result = result[0]; }
			
			return( undefined !== value )
					? this
					: result;
		},
		
		html: function( idx, newContent, doAppend )
		{
			var result = [];
			
			for ( var i = 0; i < this.length; i++ )
			{
				if ( undefined !== idx && i !== idx )
						{ continue; }
				
				if ( undefined === newContent )
				{
					result.push( this[i].innerHTML );
				}
				else
				{
					( doAppend )
							? this[i].innerHTML += newContent
							: this[i].innerHTML  = newContent;
				}
			}
			
			if ( undefined === newContent && undefined !== idx )
					{ result = result[0]; }
			
			return( undefined !== newContent )
					? this
					: result;
		},
		
		has_class: function( class_name, idx )
		{
			var result = [];
			
			for ( var i = 0; i < this.length; i++ )
			{
				if ( undefined !== idx && i !== idx )
						{ continue; }
				
				( this[i].classList )
						? result.push( this[i].classList.contains( class_name ))
						: result.push( this[i].className.indexOf( ' ' + class_name + ' ' ));
			}
			
			return( result );
		},
		'class': function( class_name, idx, do_append )
		{
			var result = [];
			
			for ( var i = 0; i < this.length; i++ )
			{
				if ( undefined === class_name )
				{
					result.push( this[i].className.split(' '));
				}
				else
				{
					if ( '' === ( class_name = class_name.trim()))
							{ return this; }
					
					if ( undefined !== idx && i !== idx )
							{ continue; }
					
					if ( this[i].classList )
					{
						( do_append )
								? this[i].classList.add( class_name )
								: this[i].classList = class_name;
					}
					else
					{
						if ( do_append )
						{
							if ( -1 === this[i].className.split(' ').indexOf( class_name ))
							{
								this[i].className = ( '' !== this[i].className )
										? ' ' + class_name
										: class_name;
							}
						}
						else
						{
							this[i].className = class_name;
						}
					}
				}
			}
			
			if ( undefined !== idx )
					{ result = result[ idx ]; }
			
			return( undefined === class_name )
					? result
					: this;
		},
		replace_class: function( oldClass, newClass, idx, do_append )
		{
			newClass = newClass.trim();
			
			for ( var i = 0; i < this.length; i++ )
			{
				if ( undefined !== idx && i !== idx )
						{ continue; }
				
				if ( this[i].classList )
				{
					do_append = do_append || ( '' !== newClass && !this[i].classList.contains( newClass ));
					
					this[i].classList.remove( oldClass );
					
					if ( do_append && '' !== newClass )
							{ this[i].classList.add( newClass ); }
				}
				else
				{
					var list = this[i].className.split(' ');
					var lidx;
					
					do_append = ( do_append && ( -1 === list.indexOf( newClass )));
					
					if ( -1 !== ( lidx = list.indexOf( oldClass )))
					{
						if ( '' !== newClass )
						{
							list[lidx] = newClass;
						}
						else if ( do_append && ( '' !== newClass ))
						{
							list.push( newClass );
						}
						else
						{
							list.splice( lidx );
						}
					}
					else
					{
						if ( do_append && '' !== newClass )
								{ list.push( newClass ); }
					}
					
					this[i].className = list.join(' ');
				}
			}
			
			return this;
		},
		remove_class: function( oldClass, idx )
		{
			this.replace_class( oldClass, '', idx );
			
			return this;
		},
		
		/**
		 * Smooth scrolling functions
		 *
		 * https://cferdinandi.github.io/smooth-scroll/#smooth-scroll-top
		 * ^ possible features to look into later
		 */
		
		scroll_cancel: function()
		{
			clearInterval( window.smooth_scroll );
		},
		scroll_to: function( x, y, milliseconds )
		{
			if ( undefined === x )
					{ x = this.scroll_x(); }
			if ( undefined === y )
					{ y = this.scroll_y(); }
			if ( undefined === milliseconds )
					{ milliseconds = 250; }
			
			if ( x > this.document_width( true ))
					{ x = this.document_width( true ); }
			if ( y > this.document_height( true ))
					{ y = this.document_height( true ); }
			
			milliseconds = ( milliseconds / 1000 );
			
			// cancel any scroll currently in progress.
			var shortcut = this
			this.scroll_cancel();
			
			window.smooth_scroll = setInterval( function()
			{
				var x_jump = Math.ceil(( x - shortcut.scroll_x()) * milliseconds );
				var y_jump = Math.ceil(( y - shortcut.scroll_y()) * milliseconds );
				
				if ( shortcut.scroll_x() !== x )
				{
					if ( x_jump >  35 ) { x_jump =  35; }
					if ( x_jump < -35 ) { x_jump = -35; }
					if ( x_jump == -0 ) { x_jump =  -1; }
				}
				
				if ( shortcut.scroll_y() !== y )
				{
					if ( y_jump >  35 ) { y_jump =  35; }
					if ( y_jump < -35 ) { y_jump = -35; }
					if ( y_jump == -0 ) { y_jump =  -1; }
				}
				
				// -12 -3 0 8 0 8
				console.log( x, x_jump, shortcut.scroll_x(), y, y_jump, shortcut.scroll_y());
				
				window.scrollBy( x_jump, y_jump );
				
				if ( shortcut.scroll_y() === y && shortcut.scroll_x() === x )
						{ clearInterval( window.smooth_scroll ); }
			}, 20 );
		},
		scroll_to_dom: function( idx, milliseconds )
		{
			if ( false !== ( idx = is_valid_index( this, idx )))
			{
				var x = this.left( idx );
				var y = this.top( idx );
				
				this.scroll_to( x, y, milliseconds );
			}
		}
		
		
	};
	
	return( new verifier( selector ));
};
