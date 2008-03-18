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