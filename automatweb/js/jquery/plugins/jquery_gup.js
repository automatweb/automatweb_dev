/**
 * @version 1
 * Example: alert ($.gup("class"));
 */
(function($){
    $.extend({
        gup: function(param){
            return new $.gup(param);
        },
        gup: function(param){
			param = param.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
			var regexS = "[\\?&]"+param+"=([^&#]*)";
			var regex = new RegExp( regexS );
			var results = regex.exec( window.location.href );
			if( results == null )
			{
		    	return "";
			}
		  	else
			{
				return results[1];
			}
        }
    });
})(jQuery);
