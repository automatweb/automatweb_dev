<?php
// $Header: /home/cvs/automatweb_dev/classes/crm/crm_profession.aw,v 1.5 2004/09/20 14:33:35 kristo Exp $
// crm_profession.aw - Ameti nimetus 
/*
@classinfo syslog_type=ST_CRM_PROFESSION relationmgr=yes
@tableinfo kliendibaas_amet index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@classinfo no_status=1

@property name_in_plural type=textbox table=kliendibaas_amet
@caption Nimi mitmuses
@comment Ametinimetus mitmuses

@property trans type=callback store=no callback=callback_gen_trans group=trans
@caption Tõlkimine

@groupinfo trans caption="Tõlkimine"

@reltype SIMILARPROFESSION value=1 clid=CL_CRM_PROFESSION
@caption Sarnane amet

*/

class crm_profession extends class_base
{
	function crm_profession()
	{
		$this->init(array(
			"clid" => CL_CRM_PROFESSION
		));
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "trans":
				$this->create_translations($arr);
				break;

		}
		return $retval;
	}	

	function callback_gen_trans($arr)
	{
		$rv = array();

		$l = get_instance("languages");
		$langinfo = $l->get_list(array(
			"key" => "acceptlang",
			"all_data" => true,
		));

		$tprop = array("name","name_in_plural");

		// now - how do I get property information for the current class?

		$props = $this->get_property_group(array());
		$translatable = array();
		foreach($props as $key => $val)
		{
			if (in_array($key,$tprop))
			{
				$translatable[$key] = $val;
			};
		};

		$prefix = $arr["prop"]["name"];

		$o = $arr["obj_inst"];
		$act_lang = $o->lang();
		
		$conns = $o->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));

		$translated = array();
		$translated[$act_lang] = $o;

		obj_set_opt("no_auto_translation", 1);

		foreach($conns as $conn)
		{
			$to = $conn->to();
			$translated[$to->lang()] = $to;
		};

		foreach($langinfo as $langdata)
		{
			$lid = $langdata["id"];
			$l_accept = $langdata["acceptlang"];
			$rv["cap_$lid"] = array(
				"name" => "cap_$lid",
				"type" => "text",
				"subtitle" => 1,
				"caption" => $langdata["name"],
			);

			$current_translation = $translated[$l_accept];

			foreach($translatable as $key => $val)
			{
				$elname = $val["name"];
				$rv["${prefix}_${lid}_" . $elname] = array(
					"name" => "${prefix}[$l_accept][" . $elname . "]",
					"type" => $val["type"],
					"caption" => $val["caption"],
					"cols" => $val["cols"],
					"rows" => $val["rows"],
					"value" => ($current_translation) ? $current_translation->prop($elname) : "",
				);
			};

		};
		
		obj_set_opt("no_auto_translation", 0);


		// and this is the place where I should show the existing connections

		return $rv;
	}

	function create_translations($arr)
	{
		$eldata = $arr["prop"]["value"];
		$o = $arr["obj_inst"];
		
		obj_set_opt("no_auto_translation", 1);

		$tr_conns = $o->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));

		$translated = array();

		foreach($tr_conns as $tr_conn)
		{
			$to = $tr_conn->to();
			$translated[$to->lang()] = $to;
		};

		$act_lang = $o->lang();

		foreach($eldata as $lang => $lang_data)
		{
			if ($lang == $act_lang)
			{
				foreach($lang_data as $prop_key => $prop_val)
				{
					$o->set_prop($prop_key,$prop_val);
				};
			}
			else
			{
				if (!$translated[$lang])
				{
					$clone = new object($o->properties());
				}
				else
				{
					$clone = new object($translated[$lang]);
				};

				$fields_with_values = 0;

				foreach($lang_data as $prop_key => $prop_val)
				{
					if ($prop_val)
					{
						$fields_with_values++;
					};
					//print "setting $prop_key to $prop_val<br>";
					$clone->set_prop($prop_key,$prop_val);
				};

				// ignore empty data
				if (0 == $fields_with_values)
				{
					continue;
				};

				if ($translated[$lang])
				{
					$clone->save();
				}
				else
				{
					$clone->set_lang($lang);

					// needed for ds_auto_translation
					$clone->set_flag(OBJ_HAS_TRANSLATION,OBJ_HAS_TRANSLATION);
					$clone->save_new();
					
					$o->connect(array(
						"to" => $clone->id(),
						"reltype" => RELTYPE_TRANSLATION,
					));

					$clone->connect(array(
						"to" => $o->id(),
						"reltype" => RELTYPE_ORIGINAL,
					));
				};
			};
		};
		obj_set_opt("no_auto_translation", 0);
	}
};
?>
