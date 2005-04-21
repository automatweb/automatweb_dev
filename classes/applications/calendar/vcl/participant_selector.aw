<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/vcl/participant_selector.aw,v 1.4 2005/04/21 08:39:14 kristo Exp $
class participant_selector extends core
{
	function participant_selector()
	{
		$this->init("");
	}


	function callb_human_name($arr)
	{
		return html::href(array(
			"url" => $this->mk_my_orb("change",array(
				"id" => $arr["id"],
				"return_url" => urlencode($this->request_uri),
				),CL_CRM_PERSON),
			"caption" => $arr["name"],
		));
	}

	
	function init_vcl_property($arr)
	{
		classload("vcl/table");
		$rtrn = array();
		$table = new vcl_table();

		// cause I don't want to use aw_global_get in callb_human_name
		$this->request_uri = aw_global_get("REQUEST_URI");
		
		$table->define_field(array(
			'name' => 'name',
			'caption' => t('Nimi'),
			'sortable' => '1',
			'callback' => array(&$this,'callb_human_name'),
			'callb_pass_row' => true,
		));

		$table->define_field(array(
			'name' => 'phone',
			'caption' => t('Telefon'),
			'sortable' => '1',
		));

		$table->define_field(array(
			'name' => 'email',
			'caption' => t('E-post'),
			'sortable' => '1',
		));

		$table->define_field(array(
			'name' => 'rank',
			'caption' => t('Ametinimetus'),
			'sortable' => '1',
		));

		if (!$_GET["get_csv_file"])
		{
			$table->define_chooser(array(
				'name' => 'check',
				'field' => 'id',
				'caption' => 'X',
			));
		}

		$conns = $arr['obj_inst']->connections_to(array());
		//arr($conns);
		$person = get_instance(CL_CRM_PERSON);
		foreach($conns as $conn)
		{
			if($conn->prop('from.class_id')==CL_CRM_PERSON)
			{
				$from = $conn->prop("from");
				$data = $person->fetch_person_by_id(array('id'=>$from));
				$table->define_data(array(
					'id' => $from,
					'name' => $data['name'],
					'phone' => $data['phone'],
					'email' => $data['email'],
					'rank' => $data['rank'],
				));
			}
		}

		$propname = $arr["prop"]["name"];

		$tbl = $arr["prop"];
		$tbl["type"] = "table";
		$tbl["vcl_inst"] = &$table;

		if ($_GET["get_csv_file"])
		{
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: inline; filename=".t("osalejad.xls").";");
			$table->sort_by();
			die($table->draw());
		}

		return array(
			$propname => $tbl,
		);
	}

};
?>
