var get_property_data = $.gpnv_as_obj();

function reload_property(props, params)
{
	insert_params(params);

	if(typeof props != "object")
	{
		props = [props];
	}

	for(var i = 0; i < props.length; i++)
	{
		property = props[i];
		(function(prop)
		{
			get_property_data["view_property"] = prop;

			$.ajax({
				url: "http://kaarel.dev.struktuur.ee/automatweb/orb.aw",
				data: get_property_data,
				success: function(html){
					$("div[name='"+prop+"']").html(html);
				}
			});
		})(property);
	}
}

function reload_layout(layouts, params)
{
	insert_params(params);

	if(typeof layouts != "object")
	{
		layouts = [layouts];
	}

	for(var i = 0; i < layouts.length; i++)
	{
		layout_ = layouts[i];
		(function(layout)
		{
			get_property_data["view_layout"] = layout;

			$.ajax({
				url: "http://kaarel.dev.struktuur.ee/automatweb/orb.aw",
				data: get_property_data,
				success: function(html){
					$("div[id='"+layout+"_outer']").html(html);
				}
			});
		})(layout_);
	}
}

function insert_params(params)
{
	if(typeof params == "object")
	{
		for(var i in params)
		{
			get_property_data[i] = params[i];
		}
	}
//	get_property_data["action"] = "get_object_data";
}