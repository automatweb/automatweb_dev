<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_org_search.aw,v 1.1 2004/02/17 15:09:24 duke Exp $
// crm_org_search.aw - kliendibaasi otsing 

// and pray tell .. how to I embed this into crm_db now?
/*

@default group=general
@default form=crm_search

@property name type=textbox
@caption Nimi

@property reg_nr type=textbox
@caption Reg. Nr.

@property address type=textbox
@caption Aadress

@property ceo type=textbox
@caption Firmajuht

@property ettevotlusvorm type=objpicker clid=CL_CRM_CORPFORM
@caption Ettevõtlusvorm

@property city type=objpicker clid=CL_CRM_CITY
@caption Linn

@property county type=objpicker clid=CL_CRM_COUNTY
@caption Maakond

@property search_button type=submit value=Otsi
@caption Otsi

@property search_results type=table no_caption=1
@caption Otsingutulemused 

@property no_reforb type=hidden value=1

@forminfo crm_search onload=init_search onsubmit=test method=get

*/

class crm_org_search extends class_base
{
	function crm_org_search()
	{
		$this->init();
	}

	function init_search($arr)
	{
		// search only, if this is set
		// and get_property sets it, once it figures out that there is something 
		// to search for
		$this->valid_search = false;
		/*
		print "bul?";
		arr($arr);
		*/
	}

	function get_property($arr)
	{
		$data = &$arr["prop"];
		$rv = PROP_OK;
		if (($data["type"] == "textbox" || $data["type"] == "objpicker") && !empty($arr["request"][$data["name"]]))
		{
			$this->valid_search = true;
			$data["value"] = $arr["request"][$data["name"]];
		};
		switch($data["name"])
		{
			case "search_results":
				$this->do_search($arr);
				break;
		};
		return $rv;
	}

	// okey, cool, I can get the form to display now

	// now I need to get the actual search working

	// and then embed it in the crm_db somehow, someway

	/**
		@attrib name=test all_args="1"

	**/
	function test($arr)
	{
		$arr["form"] = "crm_search";
		/*
		print "<pre>";
		print_r($arr);
		print "</pre>";
		*/
		return $this->change($arr);
	}

	function do_search($arr)
	{
		//var_dump($this->valid_search);
		$tf = &$arr["prop"]["vcl_inst"];
		$tf->define_field(array(
                        "name" => "name",
                        "caption" => "Organisatsioon",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "reg_nr",
                        "caption" => "Reg nr.",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "pohitegevus",
                        "caption" => "Põhitegevus",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "corpform",
                        "caption" => "Õiguslik vorm",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "address",
                        "caption" => "Aadress",
                        "sortable" => 1,
                ));
	
                $tf->define_field(array(
                        "name" => "email",
                        "caption" => "E-post",
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "url",
                        "caption" => "WWW",
                        "sortable" => 1,
                ));
                $tf->define_field(array(
                        "name" => "phone",
                        "caption" => 'Telefon',
                        "sortable" => 1,
                ));

                $tf->define_field(array(
                        "name" => "ceo",
                        "caption" => "Juht",
                        "sortable" => 1,
                ));

		$tf->define_chooser(array(
                        "field" => "id",
                        "name" => "sel",
                ));

		if (!$this->valid_search)
		{
			return false;
		};

		$filter = array(
			"class_id" => CL_CRM_COMPANY,
			"limit" => 100,
		);

		$req = $arr["request"];
		if (!empty($req["name"]))
		{
			$filter["name"] = "%" . $req["name"] . "%";
		};
		
		if (!empty($req["reg_nr"]))
		{
			$filter["reg_nr"] = $req["reg_nr"];
		};

		if (!empty($req["ettevotlusvorm"]))
		{
			$filter["ettevotlusvorm"] = $req["ettevotlusvorm"];
		};

		if (!empty($req["ceo"]))
		{
			// search by ceo name? first create a list of all crm_persons
			// that match the search criteria and after that create a list
			// of crm_companies that have one of the results as a ceo
			$ceo_filter = array(
				"class_id" => CL_CRM_PERSON,
				"limit" => 100,
				"name" => "%" . $req["ceo"] . "%",
			);
			$ceo_list = new object_list($ceo_filter);
			if (sizeof($ceo_list->ids()) > 0)
			{
				$filter["firmajuht"] = $ceo_list->ids();
			};
		};

		$addr_filter = array();

		if (!empty($req["city"]))
		{
			$addr_filter["linn"] = $req["city"];
		};
		
		if (!empty($req["county"]))
		{
			$addr_filter["maakond"] = $req["county"];
		};
		
		if (!empty($req["address"]))
		{
			$addr_filter["name"] = "%" . $req["address"] . "%";
		};

		if (sizeof($addr_filter) > 0)
		{
			$addr_filter["class_id"] = CL_CRM_ADDRESS;
			$addr_filter["limit"] = 100;
			$addr_list = new object_list($addr_filter);
			if (sizeof($addr_list->ids()) > 0)
			{
				$filter["contact"] = $addr_list->ids();
			};
		};

		obj_set_opt("no_cache", 1);
		$results = new object_list($filter);
		obj_set_opt("no_cache", 0);

		for ($o = $results->begin(); !$results->end(); $o = $results->next())
		{
			// aga ülejäänud on kõik seosed!
			$vorm = $tegevus = $contact = $juht = "";
			if (is_oid($o->prop("ettevotlusvorm")))
			{
				$tmp = new object($o->prop("ettevotlusvorm"));
				$vorm = $tmp->name();
			};

			if (is_oid($o->prop("pohitegevus")))
			{
				$tmp = new object($o->prop("pohitegevus"));
				$tegevus = $tmp->name();
			};
			
			if (is_oid($o->prop("contact")))
			{
				$tmp = new object($o->prop("contact"));
				$contact = $tmp->name();
			};

			if (is_oid($o->prop("firmajuht")))
			{
				$juht_obj = new object($o->prop("firmajuht"));
				$juht = $juht_obj->name();
				$juht_id = $juht_obj->id();
			};

			if (is_oid($o->prop("phone_id")))
			{
				$ph_obj = new object($o->prop("phone_id"));
				$phone = $ph_obj->name();
			};
			
			if (is_oid($o->prop("url_id")))
			{
				$url_obj = new object($o->prop("url_id"));
				$url = $url_obj->prop("url");
			};

			if (is_oid($o->prop("email_id")))
			{
				$mail_obj = new object($o->prop("email_id"));
				$mail = $mail_obj->prop("mail");

			};

			$tf->define_data(array(
				"id" => $o->id(),
				"name" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $o->id(),
					),$o->class_id()),
					"caption" => $o->name(),
				)),
				"reg_nr" => $o->prop("reg_nr"),
				"pohitegevus" => $tegevus,
				"corpform" => $vorm,
				"address" => $contact,
				"ceo" => html::href(array(
					"url" => $this->mk_my_orb("change",array(
						"id" => $juht_id,
					),CL_CRM_PERSON),
					"caption" => $juht,
				)),
				"phone" => $phone,
				"url" => html::href(array(
					"url" => $url,
					"caption" => $url,
				)),
				"email" => $mail,
			));
		}



		// now then .. I need to create an object list based on the name
		

		/*
		print "doing search?";
		print "<pre>";
		print_r($arr);
		print "</pre>";
		*/
	}


}
?>
