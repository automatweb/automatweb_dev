var get_property_data = $.gpnv_as_obj();

function reload_property(props, params) {
	insert_params(params);

	if(typeof props != "object") {
		props = [props];
	}

	for(var i = 0; i < props.length; i++) {
		property = props[i];
		(function(prop) {
			if($("div[name='"+prop+"']").size() > 0) {
				var div = $("div[name='"+prop+"']");
				get_property_data["view_property"] = prop;
				

				$.ajax({
					url: "orb.aw",
					data: get_property_data,
					success: function(html){
						div.html(html);
					}
				});
			}
		})(property);
	}
}

function reload_layout(layouts, params) {
	insert_params(params);

	if(typeof layouts != "object") {
		layouts = [layouts];
	}

	for(var i = 0; i < layouts.length; i++) {
		layout_ = layouts[i];
		(function(layout) {
			if($("div[id='"+layout_+"_outer']").size() > 0) {
				var div = $("div[id='"+layout+"_outer']");
				get_property_data["view_layout"] = layout;
				
				
				$.please_wait_window.show({
					"target": div
				});
				
				$.ajax({
					url: "orb.aw",
					data: get_property_data,
					success: function(html){
						div.html(html);
						$.please_wait_window.hide();
					}
				});
			}
		})(layout_);
	}
}

function insert_params(params) {
	if(typeof params == "object") {
		for(var i in params) {
			get_property_data[i] = params[i];
		}
	}
}