var get_property_data = $.gpnv_as_obj();

function reload_property(props, params) {
	insert_params(params);

	if(typeof props != "object") {
		props = [props];
	}

	for(var i = 0; i < props.length; i++) {
		property = props[i];
		(function(prop) {
			$("div[name='"+prop+"']").each(function(){
				div = $(this);
				$.please_wait_window.show({
					"target": div
				});
				$.ajax({
					url: "orb.aw",
					data: $.extend({view_property: prop}, get_property_data),
					success: function(html){
						div.html(html);
						$.please_wait_window.hide();
					}
				});
			});
		})(property);
	}
}

function reload_layout(layouts, params) {
	console.log(layouts);
	insert_params(params);

	if(typeof layouts != "object") {
		layouts = [layouts];
	}

	for(var i = 0; i < layouts.length; i++) {
		layout_ = layouts[i];
		(function(layout) {
			$("div[id='"+layout_+"_outer']").each(function(){
				div = $(this);
				console.log(div);
				$.please_wait_window.show({
					"target": div
				});
				$.ajax({
					url: "orb.aw",
					data: $.extend({view_layout: layout}, get_property_data),
					success: function(html){
						div.after(html).remove();
						$.please_wait_window.hide();
					}
				});
			});
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