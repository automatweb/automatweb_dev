/**
 * AW Object Quickadder
 *
 */
(function($) {
	jQuery.fn.AWObjectQuickAdd = function(items, options)
	{
		var settings = {
			maxresults     : 4,
			blank_gif      : "automatweb/images/aw06/blank.gif",
		};
		
        // define defaults and override with options, if available
        // by extending the default settings, we don't modify the argument
        if(options) {
			jQuery.extend(settings, options);
		};
		
		var d_quickadd_box = this;
        var d_input = $("#"+d_quickadd_box.attr("id")+" input.text");
		var a_items = items;
		var a_active_list = new Array();
		var b_object_quickadd_isopen = false;
		var i_selected_index = 0;
		
		handle_object_quickadd_activation();
		handle_object_quickadd_exit();
		set_object_quickadd_locations();

		return d_input.keyup(function(){
			$("#aw_object_quickadd_results").html("");
			a_active_list = get_list();
			$("#aw_object_quickadd_results").css("display", "block");
			// todo
			//render_list()
			handle_selection();
			
			function handle_selection()
			{
				if (a_active_list.length>0)
				{
					//$("#"+d_quickadd_box.attr("id")+" .icon img").attr("src", settings.baseurl+"/automatweb/"+a_active_list[i_selected_index]["url_icon"]);
					$("#"+d_quickadd_box.attr("id")+" .icon img").attr("src", a_active_list[i_selected_index]["url_icon"]);
					$("#"+d_quickadd_box.attr("id")+" .selected_object_name").html(a_active_list[i_selected_index]["name"]);
				}
				else
				{
					// todo .. pilt.. default
					$("#"+d_quickadd_box.attr("id")+" .icon img").attr("src", settings.baseurl+"/"+settings.blank_gif);
					$("#"+d_quickadd_box.attr("id")+" .selected_object_name").html("");
				}
			}
		});
		
		function render_list()
		{
			$tmp = "";
			i=1;
			s_class = "";
			for (key in a_active_list)
			{
				if (s_class=="")
				{
					if (a_active_list.length%2==0)
					{
						s_class = "even";
					}
					else
					{
						s_class = "odd";
					}
				}
				else
				{
					if (s_class=="even")
					{
						s_class = "odd";
					}
					else
					{
						s_class = "even";
					}
				}
				$("#aw_object_quickadd_results").append('<a class="resultitem resultitem_'+i+' '+s_class+'" href="http://www.neti.ee">'+a_active_list[key]["name"]+'</a>');
				$("#aw_object_quickadd_results .resultitem_"+i).css("background-image", "url("+settings.baseurl+"/automatweb/"+a_active_list[key]["url_icon"]+")");
				if (i==settings.maxresults)
				{
					return;
				}
				i++;
			}
			
		}
		
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


		// gets currently active filtered list of objects
		function get_list()
		{
			var a_list = new Array();
			for (key in a_items)
			{
				if (d_input.get(0).value.substr(0,1)==key)
				{
					for (key2 in a_items[key])
					{
						s_input_value_part = d_input.get(0).value;
						re = new RegExp(s_input_value_part, "i"); 
						s_name = a_items[key][key2]["name"].toString();
						if (s_name.match(re))
						{
							a_list[a_list.length] = {
								name       : s_name,
								url_icon   : a_items[key][key2]["url_icon"],
								url_obj    : a_items[key][key2]["url_obj"],
							}
						}
					}
				}
			}
			return a_list;
		}
    };
})(jQuery);