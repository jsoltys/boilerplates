var js_loader = function( type, url )
{
   var script_hdl = document.createElement( type );
   
   script_hdl.src = url;
   
   if ( document.getElementsByTagName )
   {
      var el = document.getElementsByTagName('head');
      
      if ( el[0] !== undefined )
      {
         el[0].appendChild( script_hdl );
      }
   }
}

/**
 * This is a good spot to add global documents, especially if they use the full
 * path to the document.
 */
js_loader( 'script', 'http://jsoltys4.web.csit.jccc.edu/shared/js/mk-library.js');

/**
 * Add the following function is a script of the html document right after the
 * closing body tag.
 *

   <script>
      // Add scripts here that you do not want blocking the loading of the
      // page, or you can add global pages right into the document.
      
      js_loader('script','path/to/file.ext');
   </script>

 */