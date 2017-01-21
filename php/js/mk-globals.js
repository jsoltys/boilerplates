/* =========================================================
// Global JavaScript file for mk functions
// 
//  Author: Jason Soltys
//    Date: 5-2-2016
// Version: 1
//    Site: 
// 
*/

var mk_debug = false;

/* =========================================================
// universal functions
*/
function mk_minIEVersion( minVersion )
{
	var curIE = document.documentMode;
	var result = true;
	
	if ( undefined !== curIE )
	{
		result = ( minVersion < curIE );
	}
	
	return result;
}

/* class checkers */
function mk_hasClass( obj, className )
{
	if ( undefined === obj | null === obj | undefined === obj.className )
		{ return false; }
	
	var list = obj.className.split(' ');
	var result = false;
	
	for ( var i = 0; i < list.length; i++ )
	{
		result = ( list[i] == className );
		
		if ( result )
		{
			break;
		}
	}
	
	return( result );
}

function mk_appendClass( obj, newClass )
{
	if ( !mk_hasClass( obj, newClass ))
	{
		( '' !== obj.className )
				? obj.className += ' ' + newClass
				: obj.className  = newClass;
	}
}

function mk_removeClass( obj, className )
{
	var list = obj.className.split(' ');
	var result = '';
	
	for ( var i = 0; i < list.length; i++ )
	{
		if ( list[i] != className )
		{
			( '' !== result )
					? result += ' ' + list[i]
					: result  = list[i];
		}
	}
	
	obj.className = result;
}

function mk_replaceClass( obj, oldName, newName )
{
	var list = obj.className.split(' ');
	var result = '';
	
	for ( var i = 0; i < list.length; i++ )
	{
		if ( list[i] != oldName )
		{
			( '' !== result )
					? result += ' ' + list[i]
					: result  = list[i];
		}
		else
		{
			( '' !== result )
					? result += ' ' + newName
					: result  = newName;
		}
	}
	
	obj.className = result;
}

/* =============================================================================
// node functions
*/
function mk_createElement( pElement, elmType, elmClass, elmName, elmID )
{
	var temp = document.createElement( elmType );
	
	temp.type = elmType;
	
	if ( undefined != elmName )
	{
		temp.name = elmName;
		
		if ( undefined != elmID )
		{
			temp.id = elmID;
		}
		else
		{
			temp.id = elmName;
		}
	}
	else if ( undefined != elmID )
	{
		temp.id = elmID;
	}
	
	if ( undefined != elmClass )
	{
		temp.className = elmClass;
	}
	
	pElement.appendChild( temp );
	
	return temp;
}

function mk_createClassElement( elementTag, classTag, idTag )
{
	var temp = document.createElement( elementTag );
	
	if ( undefined !== idTag )
	{
		temp.id = idTag;
	}
	
	mk_appendClass( temp, classTag )
	
	return temp;
}

function mk_createInputElement( pHandle, elmType, elmName, elmValue, elmClass )
{
	var temp = document.createElement('input');
	
	temp.type  = elmType;
	temp.name  = elmName;
	temp.value = elmValue;
	
	mk_appendClass( temp, elmClass );
	
	pHandle.appendChild( temp );
	
	return temp;
}

// check to see if obj is a string and return a reference to a DOM element
function mk_getElementHandle( obj )
{
	return ( 'object' === typeof( obj ))
			? obj
			: document.getElementById( obj );
}

// set the innerHTML of an DOM element
function mk_setElementContent( content, obj, appendContent )
{
	var objH = mk_getElementHandle( obj );
	var objC;
	
	if ( 'object' === typeof( content ))
	{
		( content.documentElement.textContent )
				? objC = content.documentElement.textContent
				: objC = content.documentElement.text;
	}
	else
	{
		objC = content;
	}
	
	( appendContent )
			? objH.innerHTML += objC
			: objH.innerHTML  = objC;
}

// extract a function name using regular expressions
// https://regex101.com/r/eS8dN6/1
function mk_getFuncName( func )
{
	var regEx = /^function[ (){]*([^ ]+)[ ]*\(/;
	var regMatch = String( func ).match( regEx );
	
	var funcName = ( regMatch )
			? regMatch[1]
			: 'unknown function';
	
	return funcName;
}

/* =========================================================
// listener functions
*/

// apply a listener to an object and return true on success
function mk_setupListener( obj, elMethod, aeMethod, func )
{
	var mk_result = false;
	var mk_msg;
	
	try
	{
		mk_msg  = mk_getFuncName( func )
			    + ' added to ';
		mk_msg += ( obj.id )
				? obj.id
				: obj;
		
		if ( obj.addEventListener )
		{
			obj.addEventListener( elMethod, func );
			mk_msg += ' using ' + elMethod;
		}
		else
		{
			obj.attachEvent( aeMethod, func );
			mk_msg += ' using ' + aeMethod;
		}
		
		if ( window.console & mk_debug ) { console.log( mk_msg + '.' ); }
		
		mk_result = true;
	}
	catch ( msg )
	{
		if ( window.console & mk_debug ) { console.log( msg ); }
	}
	
	return mk_result;
}

// apply a listener to multiple objects
function mk_setupListenerArray( objs, elMethod, aeMethod, func, fallbackFunc )
{
	for ( var idx = 0; idx < objs.length; idx++ )
	{
		var el = objs[idx];
		
		if ( !mk_setupListener( el, elMethod, aeMethod, func ))
		{
			if ( fallbackFunc )
				{ fallbackFunc( el, elMethod, aeMethod, func ); }
		}
	}
}

// clear a listener from an object and return true on success
function mk_clearListener( obj, elMethod, aeMethod, func )
{
	var mk_result = false;
	var mk_msg;
	
	try
	{
		mk_msg  = mk_getFuncName( func )
			    + ' removed from ';
		mk_msg += ( obj.id )
				? obj.id
				: obj;
		
		if ( obj.removeEventListener )
		{
			obj.removeEventListener( elMethod, func );
			mk_msg += ' using ' + elMethod;
		}
		else
		{
			obj.detachEvent( aeMethod, func );
			mk_msg += ' using ' + aeMethod;
		}
		
		console.log( mk_msg + '.' );
		
		mk_result = true;
	}
	catch ( msg )
	{
		console.log( msg );
	}
	
	return mk_result;
}

// remove a listener from multiple objects
function mk_clearListenerArray( objs, elMethod, aeMethod, func, fallbackFunc )
{
	for ( var idx = 0; idx < objs.length; idx++ )
	{
		var el = objs[idx];
		
		if ( fallbackFunc && !mk_clearListener( el, elMethod, aeMethod, func ))
		{
			fallbackFunc( el, elMethod, aeMethod, func );
		}
	}
}

/* =========================================================
// socket functions
*/

// check sockets readyState and status then send to function for processing
function mk_socketResponce( socket, url, func )
{
	( 4 === socket.readyState && 200 === socket.status )
			? func(( socket.response ) ? socket.response : socket.responseText )
			: console.log( 'readyState: ' + socket.readyState + ', status: ' + socket.status );
}

// create a tcp socket
function mk_setupSocket( url, func )
{
	var socket;
	
	( window.XMLHttpRequest )
			? socket = new window.XMLHttpRequest()
			: socket = new ActiveXObject('Microsoft.XMLHTTP');
	
	if ( console )
			console.log( socket + ' created' );
	
	if ( func )
	{
		var tmpFunc = function(){ mk_socketResponce( socket, url, func ); }
		
		if ( !mk_setupListener( socket, 'load', 'onload', tmpFunc ))
		{
			socket.onreadystatechange = tmpFunc;
		}
	}
	
	if ( url )
	{
		/* please note ie returns error on local run */
		socket.open( 'GET', url );
		socket.send();
	}
	
	return socket;
}
