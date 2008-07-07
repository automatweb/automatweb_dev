jQuery.aw_releditor = function(arr) {
	var i_releditor_form_index = arr["start_from_index"];
	var i_releditor_edit_index = false; // if being edited
	var i_releditor_edit_index_last_edit = false; // for multiple change button clicks if not saved between
	var is_edit_mode = false; // edit or new data
	var s_alert_on_delete;
	$.get("/orb.aw?class=releditor&action=js_get_delete_confirmation_text", function(data){
		s_alert_on_delete = data;
	});
	
	$(document).ready(function() {
   		_handle_events();
 	});
	
	function _handle_events()
	{
		// add/change btn events
		$("input[name="+arr["releditor_name"]+"]").click(function() {
			if (is_edit_mode)
			{
				// change btn name to 'add'
				change_btn_name = $.get("/orb.aw?class=releditor&action=js_get_button_name", function(change_btn_name){
					btn = $("input[name="+arr["releditor_name"]+"]");
					btn.attr("value", change_btn_name);
				});
			}
			_renew_and_save_form();
			return false;
		});
		
		handle_change_links();
		handle_delete_links();
	}

	/*
		gets data to be edited to form
	*/
	function do_edit()
	{
		data = $("#"+arr["releditor_name"]+"_data").serialize();
		
		// change button name when editing not adding
		$.get("/orb.aw?class=releditor&action=js_get_button_name&is_edit=1", function(change_btn_name){
			btn = $("input[name="+arr["releditor_name"]+"]");
			btn.attr("value", change_btn_name);
		});
		
		$.ajax({
			type: "POST",
			url: "/orb.aw?class=releditor&action=js_change_data&releditor_name="+arr["releditor_name"]+"&edit_index="+i_releditor_edit_index,
			data: data,
			success: function(msg){
				eval(msg);
				do_edit_fill_form(edit_data)
			}
		});
	}
	
	/*
		fills the form for editing
	*/
	function do_edit_fill_form(edit_data)
	{
		//name = arr["releditor_name"]+"["+i_releditor_form_index+"]["+key;
		//alert("[name^="+name+"][type!=submit]");
		if (is_edit_mode)
		{
			current_index = i_releditor_edit_index_last_edit;
		}
		else
		{
			current_index = i_releditor_form_index;
		}
		form = $("[name^="+arr["releditor_name"]+"\["+current_index+"][type!=submit]").not("a");
		form.each(function(){
			$(this).reset();
			s_prop_name = _get_prop_name($(this).attr("name"));
			if ($(this).attr("multiple"))
			{
				$("option", $(this)).each(function(){
					for (key in edit_data[s_prop_name])
					{
						if ($(this).val() == edit_data[s_prop_name][key])
						{
							$(this).attr("selected", true)
						}
					}
					
				});
			}
			else if ($(this).attr("type") == "checkbox")
			{
				if (edit_data[s_prop_name] == 1)
				{
					this.checked = true;
					$(this).attr("value", 1);
				}
			}
			else
			{
				$(this).attr("value", edit_data[s_prop_name]);
			}
			$(this).attr("name", arr["releditor_name"]+"["+i_releditor_edit_index+"]"+s_prop_name);
		});
		i_releditor_edit_index_last_edit = i_releditor_edit_index;
		is_edit_mode = true;
	}
	
	/*
		adds delete events to delete links
	*/
	function handle_delete_links()
	{
		$("a[name^="+arr["releditor_name"]+"_delete_]").click(function() {
			if(confirm(s_alert_on_delete))
			{
				//delete_data = $("input[name^="+arr["releditor_name"]+"_delete_][type=checkbox][checked]").serialize();
				s_form_extension = $("#"+arr["releditor_name"]+"_data").serialize();
				s_form_extension_class_info = $("[name^="+arr["releditor_name"]+"_reled_data][type=hidden]").serialize();
				data = s_form_extension+"&"+s_form_extension_class_info+"&"+arr["releditor_name"]+"_delete_index="+_get_delete_index($(this).attr("name"));
				s_form_cfgform = $("[name^=cfgform][type=hidden]").serialize();
	                        data = s_form_cfgform.length>0 ? data+"&"+s_form_cfgform : data;
				data += "&id="+arr["id"];
				$.ajax({
					type: "POST",
					url: "/orb.aw?class=releditor&action=js_delete_rows",
					data: data,
					success: function(msg){
						$("#releditor_"+arr["releditor_name"]+"_table_wrapper").html(msg);
						handle_change_links();
						handle_delete_links();
					}
				});
			}
			return false;
		});
	}
	
	function handle_change_links()
	{
		// and add edit btn events
		$("a[name^="+arr["releditor_name"]+"_edit]").click(function() {
			i_releditor_edit_index = _get_form_index_from_edit_button($(this).attr("name"));
			do_edit();
			return false;
		});
	}
	
	/*
		send data to be edited
	*/
	function _renew_and_save_form()
	{
		var a_elements = new Array();
		if (is_edit_mode)
		{
			tmp_index = i_releditor_form_index;
			i_releditor_form_index = i_releditor_edit_index
			// class info
		}
		form = $("[name^="+arr["releditor_name"]+"\["+i_releditor_form_index+"][type!=submit]").not("a");
		s_form = form.serialize();
		s_form_extension = $("#"+arr["releditor_name"]+"_data").serialize();
		data = s_form_extension.length>0 ? s_form+"&"+s_form_extension : s_form;
		s_form_extension_class_info = $("[name^="+arr["releditor_name"]+"_reled_data][type=hidden]").serialize();
		s_form_cfgform = $("[name^=cfgform][type=hidden]").serialize();
		data = s_form_extension_class_info.length>0 ? data+"&"+s_form_extension_class_info : data;
		data = s_form_cfgform.length>0 ? data+"&"+s_form_cfgform : data;
		data += "&id="+arr["id"];
		$.ajax({
			type: "POST",
			url: "/orb.aw?class=releditor&action=handle_js_submit",
			data: data,
			success: function(msg){
				$("#releditor_"+arr["releditor_name"]+"_table_wrapper").html(msg);
				handle_change_links();
				handle_delete_links();
				location.href="#"+arr["releditor_name"];
			}
		});
		
		if (is_edit_mode)
		{
			i_releditor_form_index = tmp_index;
		}
		
		form.each(function()
		{
			s_prop_name = _get_prop_name($(this).attr("name"));
			if (is_edit_mode)
			{
				next_index = i_releditor_form_index;
			}
			else
			{
				next_index = i_releditor_form_index*1.0+1;
			}
			$(this).attr("name", arr["releditor_name"]+"["+next_index+"]"+s_prop_name);
			$(this).reset();
		});
		
		if (is_edit_mode)
		{
			is_edit_mode = false;	
		}
		else
		{
			i_releditor_form_index++
		}
	}
	
	/*
		gets last part of name element
	*/
	function _get_delete_index(s_input_name)
	{
		// i don't undrestand why I had to doublescape: \\[
		var re  =  new RegExp(".*_(.*)$", "g").exec(s_input_name);
		return re[1];
	}


	/*
		gets last part of name element
	*/
	function _get_prop_name(s_input_name)
	{
		// i don't undrestand why I had to doublescape: \\[
		var re  =  new RegExp("^.+\\[[0-9]+\\](.*)$", "g").exec(s_input_name);
		return re[1];
	}
	
	/*
		gets the last part (form index) from releditors edit link name
	*/
	function _get_form_index_from_edit_button(s_name)
	{
		// i don't undrestand why I had to doublescape: \\[
		var re  =  new RegExp("^.*_.*_(.*)$", "g").exec(s_name);
		return re[1];
	}
}; 
