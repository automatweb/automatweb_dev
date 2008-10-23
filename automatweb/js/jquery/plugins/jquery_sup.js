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
			if (typeof(url) == "undefined")
			{
				url = document.location+"";
			}
			param = param.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
			var regexS = "([\\?&]"+param+"=)([^&#]*)";
			var regex = new RegExp( regexS );
			var results = regex.exec( window.location.href );
			if( results == null )
			{
				if ( url.indexOf( "?", url ) > 0)
				{
					url += "&"+param+"="+value;
				}
				else
				{
					url += "?"+param+"="+value;
				}
			}
		  	else
			{
				url = url.replace(results[0], results[1]+value);
			}
			return url;
        }
    });
})(jQuery);
