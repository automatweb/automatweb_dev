<?php
// $Header: /home/cvs/automatweb_dev/classes/vcl/translator.aw,v 1.2 2004/10/08 15:59:46 duke Exp $
class translator extends  core
{
	function translator()
	{
		$this->init("");
	}

	function init_vcl_property($arr)
	{
		$prop = &$arr["property"];
		$this->obj = $arr["obj_inst"];


		$i = $this->obj->instance();

		$rv = array();
		$l = get_instance("languages");
                $langinfo = $l->get_list(array(
                        "key" => "acceptlang",
                        "all_data" => true,
                ));

		// XXX: be more intelligent and retrieve all properties with trans=1 
                $tprop = $prop["props"];
		if (!is_array($tprop))
		{
			$tprop = array($tprop);
		};
		
                $props = $i->get_property_group(array());

                $translatable = array();
                foreach($props as $key => $val)
                {
                        if (in_array($key,$tprop))
                        {
                                $translatable[$key] = $val;
                        };
                };

                $prefix = $arr["property"]["name"];


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


			// new translations should be default be active
			$rv["act_$lid"] = array(
				"name" => "trans[" . $l_accept . "][status]",
				"type" => "checkbox",
				"ch_value" => STAT_ACTIVE,
				"value" => ($current_translation) ? $current_translation->status() == STAT_ACTIVE : 1,
				"caption" => "Aktiivne",
			);
                };

                obj_set_opt("no_auto_translation", 0);
		return $rv;
	}

	function process_vcl_property($arr)
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
                $o->set_flag(OBJ_HAS_TRANSLATION,OBJ_HAS_TRANSLATION);

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
					
					// ja vot -- siit ongi puudu koopiate tõlkimine
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

		// nii aga nüüd on vaja sisse lugeda ka objekti koopiad ja ka nende tõlked uuendada. mnjaa. vot.
		// või siis vajadusel tõlke objektid tekitada

		// now - ask for all existing translations
		$trans_conns = $o->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));

		$translation_objects = array();

		foreach($trans_conns as $trans_conn)
		{
			$translation_objects[$trans_conn->prop("lang.id")] = $trans_conn->to();
		};
		

		$clones = $o->connections_from(array(
			"type" => "RELTYPE_COPY",
		));

		//arr($translation_objects);

		foreach($clones as $clone)
		{

			$clone_obj = $clone->to();


			/*
			print "<h2>clone</h2>";
			arr($clone);

			$clone_trans = $clone_obj->connections_from(array(
				"type" => "RELTYPE_TRANSLATION",
			));
			*/

			// see on vist sünkroniseerimiseks mõeldud eks?

			// aga kuidas ma neid sünkroniseerin?

			/*
			print "<h2>trans</h2>";
			arr($clone_trans);
			*/
			// now figure out which translations does the clone have

			// update existing ones
			// create new ones .. 

			// simpel, isn't it?


		};
                obj_set_opt("no_auto_translation", 0);


	}
};
?>
