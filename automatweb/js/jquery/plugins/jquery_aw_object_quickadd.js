/**
 * AW Object Quickadder
 *
 */
(function($) {
	jQuery.fn.AWObjectQuickAdd = function(items, options)
	{
		var settings = {
			maxresults     : 4,
			blank_gif      : "automatweb/images/aw06/blank.gif"
		};
		
        // define defaults and override with options, if available
        // by extending the default settings, we don't modify the argument
        if(options) {
			jQuery.extend(settings, options);
		};
		settings.parent = Number(settings.parent);
		
		var d_quickadd_box = this;
        var d_input = $("#"+d_quickadd_box.attr("id")+" input.text");
		var b_object_quickadd_isopen = false;
		var i_selected_index = 0;
		
		handle_object_quickadd_activation();
		handle_object_quickadd_exit();
		set_object_quickadd_locations();

		d_input.autocomplete(items, {
				minChars: 0,
				width: 310,
				matchContains: true,
				autoFill: true,
				formatItem: function(row, i, max) {
					return row.name;
				},
				formatMatch: function(row, i, max) {
					return row.name;
				},
				formatResult: function(row) {			
					return row.name;
				}
		}).result(function(event, data, formatted) {
			url = settings.baseurl+"/automatweb/"+data.url_obj.replace("--p--", settings.parent)
			//$("#test").html( !data ? "No match!" : url);
			$(this).parent().remove();
			document.location = url
		});
		
		// handles the pop up of quickadd layer
		// and document.location upon enter
		function handle_object_quickadd_activation()
		{
			$("*").not("input").keydown(function(e){
				if (b_object_quickadd_isopen===false)
				{
					if (e.ctrlKey && e.shiftKey && e.keyCode==85)
					{
						if ($("#"+d_quickadd_box.attr("id")).css("display") == "block")
						{
							
						}
						else if ($("#"+d_quickadd_box.attr("id")).css("display") == "none")
						{
							$("#"+d_quickadd_box.attr("id")).css("display", "block");
							$("#"+d_quickadd_box.attr("id")+" input.text").focus();
						}
					}
					b_object_quickadd_isopen = true
				}
				else
				{
					if (e.keyCode == 13)
					{
						if (a_active_list.length>0)
						{
							if(($("#"+d_quickadd_box.attr("id")).css("display") == "block"))
							{
								$("#"+d_quickadd_box.attr("id")+" input.text").val("");
								$("#"+d_quickadd_box.attr("id")).css("display", "none");
								url = options["baseurl"]+"/automatweb/"+a_active_list[i_selected_index]["url_obj"];
								url = url.replace("--p--", settings.parent)
								document.location = url
							}
						}
					}
					b_object_quickadd_isopen = false;
				}
			});
		}
		
		// handles the closing of quickadd layer
		function handle_object_quickadd_exit()
		{
			$("#"+d_quickadd_box.attr("id")+" input.text").keyup(function(e){
				if (e.keyCode==27)
				{
					if ($("#"+d_quickadd_box.attr("id")).css("display") == "block")
					{
						$("#"+d_quickadd_box.attr("id")+" input.text").val("");
						$("#"+d_quickadd_box.attr("id")).css("display", "none");
					}
				}
			});
		}
		
		// sets location of the quickadd on the screen
		function set_object_quickadd_locations()
		{
			// quicklaunch box
			d = $("#"+d_quickadd_box.attr("id"));
			d.css("left", $(window).width()/2-d.width()/2);
			d.css("top", $(window).height()/3);
			
			// quicklaunch result
			d2 = $("#aw_object_quickadd_results");
			d2.css("left", ($(window).width()/2-d.width()/2)+18+"px");
			d2.css("top", $(window).height()/3+d.height()-19+"px");
		}
    };
})(jQuery);