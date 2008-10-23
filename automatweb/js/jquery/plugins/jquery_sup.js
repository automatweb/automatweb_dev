/**
 * @version 1
 * Example: alert ($.sup("class"));
 */
(function($){
    $.extend({
        sup: function(param, value){
            return new $.sup(param, value);
        },
        sup: function(param, value){
			param = param.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
			var regexS = "([\\?&]"+param+"=)([^&#]*)";
			var regex = new RegExp( regexS );
			var results = regex.exec( window.location.href );
			if( results == null )
			{
		    	return "";
			}
		  	else
			{
				url = document.location+"";
				return url.replace(results[0], results[1]+value);
			}
        }
    });
})(jQuery);
