<?php

class infrastructure_import_obj extends _int_object
{
	public function invoke()
	{
		$countries_parent = $this->prop("countries_parent");
		if(!is_oid($countries_parent) || !$this->can("add", $countries_parent))
		{
			die("Ei saa riikide kausta alla salvestada!");
		}

		$jdata = file_get_contents($this->prop("countries_json.file"));
		$data = json_decode($jdata);
		$this->data = array();
		foreach($data as $o)
		{
			switch($o->model)
			{
				case "places.country":
					$estnames["country"][$o->pk] = $this->data["country"][$o->pk]["name"]["et"] = iconv("UTF-8", aw_global_get("charset"), $o->fields->et_name);
					$this->data["country"][$o->pk]["name"]["en"] = iconv("UTF-8", aw_global_get("charset"), $o->fields->en_name);
					break;

				case "places.county":
					$this->data["county"][$o->pk]["country"] = 49;	// Eesti
					$estnames["county"][$o->pk] = $this->data["county"][$o->pk]["name"]["et"] = iconv("UTF-8", aw_global_get("charset"), $o->fields->name);
					break;

				case "places.municipality":
					switch($o->fields->m_type)
					{
						/*
						('p', _('parish')), # vald
						('r', _('parish town')), # alevvald
						('b', _('borough')), # alev
						('s', _('small borough')), # alevik
						*/

						case "c":	# maakonnakeskus, suurlinn
						case "t":	# linn
							$this->data["city"][$o->pk]["country"] = 49;	// Eesti
							$this->data["city"][$o->pk]["county"] = $o->fields->county;
							$estnames["city"][$o->pk] = $this->data["city"][$o->pk]["name"]["et"] = iconv("UTF-8", aw_global_get("charset"), $o->fields->name);
							break;
					}
					break;
			}
		}
		$clids = array(
			"country" => CL_CRM_COUNTRY,
			"county" => CL_CRM_COUNTY,
			"city" => CL_CRM_CITY,
		);
		exit;
		foreach($clids as $type => $clid)
		{
			$objects = $this->data[$type];
			$ol = new object_list(array(
				"class_id" => $clid,
				"name" => $estnames[$type],
				"lang_id" => array(),
				"site_id" => array(),
			));
			$name_to_object = array_flip($ol->names());
			foreach($objects as $object)
			{
				if(isset($name_to_object[$object["name"]["et"]]))
				{
					$o = obj($name_to_object[$object["name"]["et"]]);
				}
				else
				{
					$o = obj();
					$o->set_class_id($clid);
					$o->set_name($name["et"]);
				}
				switch($type)
				{
					case "country":
						$o->set_parent($countries_parent);
						break;

					case "county":
						$o->set_parent($countries_parent);
						break;

					case "city":
						if(isset($this->data["county"][$object["county"]]["oid"]) && is_oid($this->data["county"][$object["county"]]["oid"]))
						{
							$o->set_parent($this->data["county"][$object["county"]]["oid"]);
							$o->set_prop("country", $this->data["country"][$object["country"]]["oid"]);
							$o->set_prop("county", $this->data["county"][$object["county"]]["oid"]);
						}
						else
						{
							echo arr($object);
						}
						break;
				}
				// Translation
				if(isset($object["name"]["en"]))
				{
					$m = $o->meta("translations");
					$m[2]["name"] = $object["name"]["en"];
					$o->set_meta("trans_2_status", 1);
					$o->set_meta("translations", $m);
				}
				$o->save();
			}
		}

		die("DONE");
	}
}

?>
