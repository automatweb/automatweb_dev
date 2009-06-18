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
			if($("div[name='"+prop+"']").size() > 0)
			{
				get_property_data["view_property"] = prop;

				$.ajax({
					url: "orb.aw",
					data: get_property_data,
					success: function(html){
						$("div[name='"+prop+"']").html(html);
					}
				});
			}
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
			if($("div[id='"+layout_+"_outer']").size() > 0)
			{
				get_property_data["view_layout"] = layout;

				$.ajax({
					url: "orb.aw",
					data: get_property_data,
					success: function(html){
						$("div[id='"+layout+"_outer']").html(html);
					}
				});
			}
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
}