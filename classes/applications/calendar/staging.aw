<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/staging.aw,v 1.7 2005/03/18 12:12:41 ahti Exp $
// staging.aw - Lavastus 
/*

@classinfo syslog_type=ST_STAGING relationmgr=yes

@default table=objects
@default group=general

@property start1 type=date_chooser field=start table=planner
@caption Algab

@default field=meta 
@default method=serialize

@property img type=releditor reltype=RELTYPE_PICTURE use_form=emb rel_id=first
@caption Pilt

layout grid1 type=grid cols=2 rows=5 group=general
default layout=grid1

property utextbox1 type=textbox no_caption=1

property utextbox2 type=textbox no_caption=1

property utextbox3 type=textbox no_caption=1

property utextbox4 type=textbox no_caption=1

property utextbox5 type=textbox no_caption=1

property utextbox6 type=textbox no_caption=1

property utextbox7 type=textbox no_caption=1

property utextbox8 type=textbox no_caption=1

@property utextarea1 type=textarea cols=90 rows=10 trans=1
@caption Kirjeldus

@property project_selector type=project_selector store=no group=projects all_projects=1
@caption Projektid

@property trans type=translator store=no group=trans props=name,utextarea1
@caption Tõlkimine

@property times type=callback store=no callback=callback_gen_times group=times
@caption Ajad

@tableinfo planner index=id master_table=objects master_index=brother_of

@reltype PICTURE value=1 clid=CL_IMAGE
@caption Pilt

// copies are individual objects, they have no relation to the original
// after they are created - besides that connection - which - is used
// to overwrite the copied events
@reltype COPY value=2 clid=CL_STAGING
@caption Koopia

@groupinfo projects caption="Projektid"
@groupinfo trans caption="Tõlkimine"
@groupinfo times caption="Ajad"

@classinfo trans=1

*/

class staging extends class_base
{
	function staging()
	{
		$this->init(array(
			"tpldir" => "applications/calendar/staging",
			"clid" => CL_STAGING
		));
	}


	function callback_gen_times($arr)
	{
		$rv = array();
		$prefix = $arr["prop"]["name"];
		// ja see asi siin peab arvestama tehtud seoseid
		$o = $arr["obj_inst"];
		$o = $o->get_original();

		// check if this is a copy and then show connections from the original object
		$copy_conns = $o->connections_to(array(
			"type" => "RELTYPE_COPY",
		));

		if (sizeof($copy_conns) > 0)
		{
			$first = reset($copy_conns);
			$o = $first->from();
		};
		
		$start1 = $o->prop("start1");
		$conns = $o->connections_from(array(
			"type" => "RELTYPE_COPY",
		));

		/*
		$rv["active_" . $prefix] = array(
			"type" => "datetime_select",
			"name" => $prefix . "[active]",
			"value" => $o->prop("start1"),
			"caption" => "Aktiivne objekt",
		);
		*/
		
		$rv["active_" . $prefix] = array(
			"type" => "text",
			"name" => $prefix . "[active]",
			"value" => date("d.m.Y H:i",$o->prop("start1")),
			"caption" => t("Originaalobjekt"),
		);

		$empty_slots = 50;
		foreach($conns as $conn)
		{
			$to = $conn->to();
			$id = $to->id();
			$caption = t("Koopia");
			if ($id == $arr["obj_inst"]->id())
			{
				$caption .= t(" (Aktiivne)");
			};
				
			$rv["existing_" . $prefix . $id] = array(
				"type" => "datetime_select",
				"name" => $prefix . "[existing][" . $id . "]",
				"caption" => $caption,
				"group" => $arr["prop"]["group"],
				"value" => $to->prop("start1"),
				"day" => "text",
				"month" => "text",
			);
			$empty_slots--;
		}

		$rv["sbx"] = array(
			"type" => "text",
			"subtitle" => 1,
			"caption" => t("Uued"),
		);

		for ($i = 1; $i <= $empty_slots; $i++)
		{
			// esimese väärtus peab olema eventi enda alguse aeg
			$rv["new_" . $prefix . $i] = array(
				"type" => "datetime_select",
				"name" => $prefix . "[new][" . $i . "]",
				"caption" => sprintf(t("Uus %s"), $i),
				"group" => $arr["prop"]["group"],
				"value" => $start1,
				"day" => "text",
				"month" => "text",
			);

			$rv["newx_" . $prefix . $i] = array(
				"type" => "checkbox",
				"name" => $prefix . "[newx][" . $i . "]",
				"caption" => sprintf(t("Tee sündmus %s"), $i),
				"group" => $arr["prop"]["group"],
				"value" => 1,
			);
		};
		return $rv;
	}



	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "times":
				$this->create_copies($arr);
				break;

		}
		return $retval;
	}	

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{




		};
		return $retval;
	}

	/**
		@attrib name=fixxer all_args="1"
		@param group optional acl="edit,view"
		@param group2 required type="int"
	**/
	function fixxer($arr)
	{
		print "fixing events, eh?";
		obj_set_opt("no_auto_translation", 1);
		$ol = new object_list(array(
			"class_id" => CL_STAGING,
		));
		print "<pre>";
		foreach($ol->arr() as $o)
		{
			if ($o->prop("start1") == 0)
			{
				// trying to fix the thing
				$conns = $o->connections_from(array(
					"type" => RELTYPE_ORIGINAL,
				));
				printf("%s\t\t\t%s\t%s\t%s\t%s\n",$o->name(),$o->lang(),$o->prop("start1"),$o->id(),$o->brother_of());
				if (sizeof($conns) > 0)
				{
					$first = reset($conns);	
					$fo = $first->to();
					if ($fo->prop("start1") != 0)
					{
						$o->set_prop("start1",$fo->prop("start1"));
						$o->save();
					};
					/*
					print "has " . sizeof($conns) . " translations<br>";
					print "rs = " . $fo->prop("start1");
					*/
				};
			};
		};
		print "</pre>";
		die();
	}


	function create_copies($arr)
	{
		$times = $arr["prop"]["value"];

		load_vcl("date_edit");
		$de = new date_edit();

		$o = $arr["obj_inst"];
		$o = $o->get_original();
		
		// check if this is a copy and then show connections from the original object
		$copy_conns = $o->connections_to(array(
			"type" => "RELTYPE_COPY",
		));

		if (sizeof($copy_conns) > 0)
		{
			$first = reset($copy_conns);
			$o = $first->from();
		};

		// see raisk võtab jah originaalist

		$brother_list = new object_list(array(
			"brother_of" => $o->id(),
		));

		$o_id = $o->id();
		$original_parent = $o->parent();

		// nii aga iga venna kohta mul vaja teada tõlgete id-sid no less.

		$blist = array();
		$xblist = array();
		foreach($brother_list->arr() as $brother)
		{
			$bparent = $brother->parent();

			if ($brother->id() == $o_id)
			{
				$original_parent = $brother->parent();
			};

			$xblist[$bparent] = $this->_get_translations_for($bparent);
			// xblist annab mulle ainult selle info kuhu alla vennad teha.
			// tõlked tuleb ikka igast koopiast eraldi rajada

			$blist[$bparent] = 1;
		};

		$object_translations = $this->_get_translations_for($o->id());


		// siin loome (mitte ei uuenda) koopiaid - koopia tegemisel
		// 1. teha uus objekt
		// 2. kanda info üle
		// 3. teha tõlgete objektid
		// 4. kanda info üle

		// update times of existing objects
		if (is_array($times["existing"]))
		{
			$ext = $times["existing"];
			foreach($ext as $obj_id => $date_data)
			{
				$ts = $de->get_timestamp($date_data);

				$obj_copy = new object($obj_id);
				$obj_copy->set_prop("start1",$ts);

				$obj_copy->save();


				if ($obj_id == $arr["obj_inst"]->id())
				{
					$arr["obj_inst"]->set_prop("start1",$ts);
				};
				

			};
		}
		
		$news = $times["newx"];
		if (!is_array($news))
		{
			$news = array();
		};
		

		if (is_array($times["new"]))
		{
			$nw = $times["new"];
			foreach($nw as $idx => $date_data)
			{
				$ts = $de->get_timestamp($date_data);
				if ($news[$idx])
				{
					// loome uue koopia objekti
					//parent jääb samaks

					$new_obj = $o;
					

					$new_obj->set_prop("start1",$ts);
					$new_obj->save_new();



					// connection from original to the copy so that the original
					// can later manage copies
					$o->connect(array(
						"to" => $new_obj->id(),
						"reltype" => "RELTYPE_COPY",
					));

					obj_set_opt("no_auto_translation",1);
					foreach($xblist as $orig_brother_id => $items)
					{
						// loome vennad sinna kuhu vaja
						$new_obj->create_brother($orig_brother_id);
					};

					foreach($object_translations as $lang_id => $item)
					{
						// nüüd tuleb teha iga asja jaoks tõlge
						$translation_obj = new object($item);
						$translation_obj->set_lang($lang_id);
						$translation_obj->save_new();
						
						$new_obj->connect(array(
							"to" => $translation_obj->id(),
							"reltype" => RELTYPE_TRANSLATION,
						));

						$translation_obj->connect(array(
							"to" => $new_obj->id(),
							"reltype" => RELTYPE_ORIGINAL,
						));

						// ja nüüd kui tõlked on tehtud, tuleb tõlkest teha veel vennad
						// sinna kuhu vaja.

						foreach($xblist as $xb_key => $xb_val)
						{
							// sest see on juba tehtud
							if ($xb_key != $original_parent)
							{
								$prx = $xb_val[$lang_id];
								$brot_id = $translation_obj->create_brother($prx);
								$brot_obj = new object($brot_id);
								$brot_obj->set_lang($lang_id);
								$brot_obj->save();

							}
						};
					};
					obj_set_opt("no_auto_translation",0);
				};
					

			};

		};

		// start time of the original object is also shown in that form so 
		// update this as well
		/*
		$valx = $de->get_timestamp($times["active"]);
		$o->set_prop("start1",$valx);
		*/


	}

	function _get_translations_for($id,$ids = false)
	{
		$obj = new object($id);
		obj_set_opt("no_auto_translation", 1);

		$tr_conns = $obj->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));

		$rv = array();

		foreach($tr_conns as $tr_conn)
		{
			$tr_obj = $tr_conn->to();
			$argstr = $ids ? $tr_obj->lang_id() : $tr_obj->lang();
			$rv[$argstr] = $tr_obj->id();
		};

		obj_set_opt("no_auto_translation",0);

		return $rv;
	}


	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	/**
		@attrib name=fixxer2
	**/
	function fixer($arr)
	{
		$sql = "select objects.oid,name from objects left join planner on (objects.brother_of = planner.id) where planner.start >= 1125179999";
		$this->db_query($sql);
		$queries = array();
		while($row = $this->db_next())
		{
			arr($row);
			$queries[] = "UPDATE objects SET status = 0 WHERE oid = " . $row["oid"];
		}
		arr($queries);

		foreach($queries as $query)
		{
			$this->db_query($query);
		};



		print "<h1>all done</h1>";


		//arr($queries);
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	// how do I get the mass entering of events to work?

	// 1. how do I save those objects so that they show up as normal events 
	// in the calendar

	// 2. if I edit one of those cloned objects then should the other events also change?

	// 3. perhaps I should get the recurrency working properly .. this would allow me 
	// to create better .. oh yes .. I think that is the solution


	// I should just be able to enter custom dates and be done with it .. YES!
}
?>
