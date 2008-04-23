jQuery.aw_releditor = function(arr) {
	var a_original_form;
	var i_releditor_form_index = 0;
	var i_releditor_edit_index = false; // if being edited
	var is_edit_mode = false;
	
	$(document).ready(function() {
   		_handle_events();
 	});
	
	//a_original_form = _clone_form();
	//_get_form_items();

	//handle_object_quickadd_exit();
	//set_object_quickadd_locations();
	
	function _handle_events()
	{
		// add/change btn events
		$("input[name="+arr["releditor_name"]+"]").click(function() {
			if (is_edit_mode)
			{
				change_btn_name = $.get("/orb.aw?class=releditor&action=js_get_button_name", function(change_btn_name){
					btn = $("input[name="+arr["releditor_name"]+"]");
					btn.attr("value", change_btn_name);
				});
			}
			_renew_and_save_form();
			// change btn name to 'add'

			//temp_save_form();
			i_releditor_form_index++;
			return false;
		});
		
		$("input[name^=releditor_del_button]").click(function() {
			if(confirm('Kustutada valitud objektid?'))
			{
				s_name = $(this).attr("name");
				var re  =  new RegExp("^releditor_del_button_(.*)$", "g").exec(s_name);
				oid = re[1];
				if  (!$("input[name="+arr["releditor_name"]+"]").attr("delete_index"))
				{
					$("input[name="+arr["releditor_name"]+"]").attr("delete_index", 0)
					new_index = 0
				}
				else
				{
					current_index = $("input[name="+arr["releditor_name"]+"]").attr("delete_index");
					$("input[name="+arr["releditor_name"]+"]").attr("delete_index", (current_index*1.0)+1);
					new_index = (current_index*1.0)+1;
				}
				$("input[name="+arr["releditor_name"]+"]").after("<input type=hidden name=releditor[delete_oid]["+new_index+"] value="+oid+">");
				del_row($(this))
			}
			return false;
		});
	}
	
	function temp_save_form()
	{
		
	}
	
	function del_row(that)
	{
		that.parent().parent().remove();
		return true;
	}
	
	// todo: delete
	function _clone_form()
	{
		var elements = new Array();
		$("[name^="+arr["releditor_name"]+"]").each(function()
		{
			var type = $(this).attr("type")
			tag = $(this).get(0).tagName.toLowerCase();
			if ($(this).attr("type")!="submit")
			{
				element = $(this).clone();
				if (type == 'text' || type == 'password' || tag == 'textarea')
				{
					element.value = $(this).get(0).value;
				}
				else if (type == 'checkbox' || type == 'radio')
				{
					element.checked = $(this).get(0).checked
				}
				else if (tag == 'select')
				{
					element.get(0).selectedIndex = $(this).get(0).selectedIndex;
				}
				elements[elements.length] = element;
			}
		});
		return elements;
	}

	/*
		gets data to be edited to form
	*/
	function do_edit()
	{
		data = $("#"+arr["releditor_name"]+"_data").serialize();
		
		// change button name when editing not adding
		change_btn_name = $.get("/orb.aw?class=releditor&action=js_get_button_name&is_edit=1", function(change_btn_name){
			btn = $("input[name="+arr["releditor_name"]+"]");
			btn.attr("value", change_btn_name);
			btn.attr("is_edit", 1);
			is_edit_mode = true;
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
	
	function do_edit_fill_form(edit_data)
	{
		for (key in edit_data)
		{
			name = arr["releditor_name"]+"["+i_releditor_form_index+"]["+key;
			//alert("[name^="+name+"][type!=submit]");
			$("[name^="+arr["releditor_name"]+"\["+i_releditor_form_index+"][type!=submit]").not("a").each(function(){
				s_prop_name = _get_prop_name($(this).attr("name"));
				$(this).attr("value", edit_data[s_prop_name]);
				$(this).attr("name", arr["releditor_name"]+"["+i_releditor_edit_index+"]"+s_prop_name);
			});
			
		}
	}
	
	function get_input_type()
	{
		
	}
	
	// finds all non submit form elements with prefix s_releditor_name + '['
	function _renew_and_save_form()
	{
		var a_elements = new Array();
		if (is_edit_mode)
		{
			i_releditor_form_index = i_releditor_edit_index
			tmp_index = i_releditor_form_index;
		}
		form = $("[name^="+arr["releditor_name"]+"\["+i_releditor_form_index+"][type!=submit]").not("a");
		s_form_extension = $("#"+arr["releditor_name"]+"_data").serialize();
		s_form = form.serialize();
		data = s_form_extension.length>0 ? s_form+"&"+s_form_extension : s_form;
		$.ajax({
			type: "POST",
			url: "/orb.aw?class=releditor&action=handle_js_submit",
			data: data,
			success: function(msg){
				$("#releditor_"+arr["releditor_name"]+"_table_wrapper").html(msg);
				
				// and add edit btn events
				$("a[name^="+arr["releditor_name"]+"_edit]").click(function() {
					i_releditor_edit_index = _get_form_index_from_edit_button($(this).attr("name"));
					do_edit();
					return false;
				});
			}
		});
		
		if (is_edit_mode)
		{
			i_releditor_form_index = tmp_index;
			is_edit_mode = false;
		}
		
		form.each(function()
		{
			s_prop_name = _get_prop_name($(this).attr("name"));
			$(this).attr("name", arr["releditor_name"]+"["+(i_releditor_form_index*1.0+1)+"]"+s_prop_name);
			$(this).reset();
		});
		
/*
		a_elements[a_elements.length] = $("[name^="+arr["releditor_name"]+"\["+i_releditor_form_index+"][type!=submit]").not("a").each(function()
		{
			s_prop_name = _get_prop_name($(this).attr("name"));
			
			// put the array index to custom attribute arr_index
			if ($(this).attr("arr_index"))
			{
				$(this).attr("arr_index", ($(this).attr("arr_index")*1.0)+1);
			}
			else
			{
				///alert ($("input[name="+arr["releditor_name"]+"][").get(0));
				//max_index = ;
				$(this).attr("arr_index", 1);
			}

			if ($(this).attr("type")!="submit")
			{
				element = $(this).clone().css("display", "block");;
				//s_prop_name  = _get_prop_name(element.attr("value"));
				//alert (s_prop_name );
				a_elements[s_prop_name] = element;
				$("#releditor_education_edit_table_wrapper").after(element);
				$(this).attr("name", arr["releditor_name"]+"["+$(this).attr("arr_index")+"]"+s_prop_name);
				$(this).reset();
				return a_elements;
			}
			
		});
		
		//alert(a_elements.length);
		
		var s_tr = "<tr>";
		var s_td = "";
		//for (key in a_elements)
		for (k=0;k<10;k++)
		{
			s_td += "<td class='awmenuedittabletext'>tt</td>";
		}
		s_td += "<td class='awmenuedittabletext'>";
		s_td += "";
		s_td += "</td>";
		s_tr += "";
		s_tr += "</tr>";
		//o_tr = $(this).html();
		$("#releditor_education_edit_table_wrapper table").append(s_tr);
		*/
	}
	
	function _get_element_values(e)
	{
		
	}

	function _get_nice_form_element_value(e)
	{
		var type = e.attr("type");
		//tag = e.get(0).tagName.toLowerCase();
		if (type == 'text' || type == 'password')
		{
			if (e.attr("value"))
			{
				return e.attr("value");
			}
		}
		return "";
	}
	
	function _save_data()
	{
		for(key in a_items)
		{
			item = a_items[key];
			//item.css("display", "none");
			$("input[type=submit]").after(item);
		}
	}
	
	function _get_prop_name(s_input_name)
	{
		// i don't undrestand why I had to doublescape: \\[
		var re  =  new RegExp("^.+\[[0-9]+\\](.*)$", "g").exec(s_input_name);
		return re[1];
	}
	
	
	function _get_prop_name2(s_input_name)
	{
		// i don't undrestand why I had to doublescape: \\[
		var re  =  new RegExp("^.+\[[0-9]+\]\\[([a-z_]*).*$", "g").exec(s_input_name);
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


/*
jQuery.aw_releditor = function(arr) {

	_handle_events();

	function _handle_events()
	{
		$("input[name="+arr["releditor_name"]+"]").click(function() {
			_renew_and_save_form();
			return false;
		});
	}

	// save object
	function _renew_and_save_form()
	{
		elements = $("[name^="+arr["releditor_name"]+"\[0][type!=submit]").not("a").serialize();
		$.ajax({
			type: "POST",
			url: "http://hannes.dev.struktuur.ee/automatweb/orb.aw?class=releditor&action=process_new_releditor&releditor_name="+arr["releditor_name"]+"&id="+arr["id"]+"&reltype="+arr["reltype"]+"&clid="+arr["clid"],
			data: elements,
			arr: arr,
			success: function(msg){
				$("#releditor_"+arr["releditor_name"]+"_table_wrapper").html(msg);
				$("input[name^="+arr["releditor_name"]+"][type=text]").not("a").reset();
			}
			
 		});
	}

};
*/

