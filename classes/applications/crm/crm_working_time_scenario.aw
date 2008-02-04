<?php
// crm_working_time_scenario.aw - Tööaja tsenaarium
/*

@classinfo syslog_type=ST_CRM_WORKING_TIME_SCENARIO relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects


@reltype CURRENCY value=1 clid=CL_CURRENCY
@caption valuuta


*/

class crm_working_time_scenario extends class_base
{
	function crm_working_time_scenario()
	{
		$this->init(array(
			"tpldir" => "applications/crm/crm_working_time_scenario",
			"clid" => CL_CRM_WORKING_TIME_SCENARIO
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "bills":
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "bills":
			{
				break;
			}
		}
		return $retval;
	}
	function callback_mod_reforb($arr)
	{
		$arr["add_bill"] = "";	
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	/**
		@attrib name=make_worker_table params=name all_args=1
	**/
	function make_worker_table($arr)
	{
		//extract($arr);
		if(is_array($_POST["bron"]))
		{

			//siin objektide tegemine jne
			die("<script type='text/javascript'>
				window.close();
				</script>
			");
		}
		$start = date_edit::get_timestamp($arr["start"]);
		$end = date_edit::get_timestamp($arr["end"]);



		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
		));
		$s = $start;
		while($s < $end)
		{
			$t->define_field(array(
				"name" => $s,
				"caption" => date("d.m.Y h:i" , $s),
			));
			$s = $s + 24*3600;	
		}


		
		$t->define_data(array(
			"value" => html::submit(array(
				"value" => t("Salvesta"),
			)),
		));	
		
		$t->define_data(array(
			"value" => html::hidden(array(
				"name" => "bron[id]",
				"value" => $id,
			)),
		));
		die($err.html::form(array("method" => "POST", "content" => $t->draw())));
	}

}

?>
