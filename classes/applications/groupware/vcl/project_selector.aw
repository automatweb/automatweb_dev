<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/groupware/vcl/project_selector.aw,v 1.1 2004/10/12 14:05:31 duke Exp $
class project_selector extends core
{
	function project_selector()
	{
		$this->init("");
	}

	function init_vcl_property($arr)
	{
		// see annab connectionid kõigist projektidest, mis viitavad sellele sündmusele
		// which of course is bad.
		// I need a list of all brothers of this object!
		// so that I can show active ones

		$orig = $arr["obj_inst"]->get_original();

		$olist = new object_list(array(
			"brother_of" => $orig->id(),
		));

		$prjlist = array();
		for($o =& $olist->begin(); !$olist->end(); $o =& $olist->next())
		{
			$xlist[$o->parent()] = 1;
		};

		$all_props = array();
		$prop = $arr["prop"];


		// väga lahe - nüüd tuleb veel grupeerimine teha
		if (1 == $prop["all_projects"])
		{
			$olist = new object_list(array(
				"class_id" => CL_PROJECT,
			));

			$by_parent = array();
			$first = true;

			for($o =& $olist->begin(); !$olist->end(); $o =& $olist->next())
			{
				$pr = new object($o->parent());
				if ($first)
				{
					$first_project = $o->id();
					$first = false;
				};

				// aah, but that IS the bloody problem .. I can't enter events in that way

				// now how do I get that grouping shit to work?
				$all_props["prj_" . $o->id()] = array(
					"type" => "checkbox",
					"name" => "prj" . "[" .$o->id() . "]",
					"caption" => html::href(array(
						"url" => $this->mk_my_orb("change",array("id" => $o->id()),CL_PROJECT),
						"caption" => "<font color='black'>" . $o->name() . "</font>",
					)),
					"ch_value" => $xlist[$o->id()],
					"value" => 1,
				);

			};
			$pr = get_instance(CL_PROJECT);
			$pr->_recurse_projects(0,$first_project);

		}
		else
		{
			// ajaa .. aga see on nüüd see asi et näitab ainult neid projekte kus ma ise olen
			// mul aga on vaja et ta näitaks kõiki projekte

			$users = get_instance("users");
			$user = new object($users->get_oid_for_uid(aw_global_get("uid")));
			$conns = $user->connections_to(array(
				"from.class_id" => CL_PROJECT,
				"sort_by" => "from.name",
			));


			foreach($conns as $conn)
			{
				$all_props["prj_" . $conn->prop("from")] = array(
					"type" => "checkbox",
					"name" => "prj" . "[" .$conn->prop("from") . "]",
					"caption" => html::href(array(
						"url" => $this->mk_my_orb("change",array("id" => $conn->prop("from")),"project"),
						"caption" => "<font color='black'>" . $conn->prop("from.name") . "</font>",
					)),
					"ch_value" => $xlist[$conn->prop("from")],
					"value" => 1,
				);
			};
		};

		return $all_props;
	}

	function process_vcl_property($arr)
	{
		$event_obj = $arr["obj_inst"];
		// 1) retreieve all connections that this event has to projects
		// 2) remove those that were not explicitly checked in the form
		// 3) create new connections which did not exist before
		global $awt;
		$awt->start("retr-project-connections");

		$orig = $arr["obj_inst"]->get_original();

		// figure out all current brothers
		$olist = new object_list(array(
			"brother_of" => $orig->id(),
		));

		// determine all projects that this event is part of,
		// compare that list to the selected items in the form
		// and put the event (create a brother) into all the projects
		// that it wasn't already a part of
		$xlist = array();
		foreach($olist->arr() as $o)
		{
			// hm, originaali näidatakse aga listi ei panda. Ongi nii või?
			if ($o->id() != $o->brother_of())
			{
				$xlist[$o->id()] = $o->parent();
			};
		};

		//arr($xlist);

		$awt->stop("retr-project-connections");

		$new_ones = array();
		if (is_array($arr["request"]["prj"]))
		{
			$new_ones = $arr["request"]["prj"];
		};

		unset($new_ones[$event_obj->parent()]);

		$prj_inst = get_instance(CL_PROJECT);
		$awt->start("disconnect-from-project");

		foreach($xlist as $obj_id => $folder_id)
		{
			if (!$new_ones[$obj_id])
			{
				$bo = new object($obj_id);
				$bo->delete();
			};
			unset($new_ones[$obj_id]);
		};

		$awt->stop("disconnect-from-project");
		$awt->start("connect-to-project");

		$clones = $transx = array();

		// what if this thing itself is a copy?
		$event_clones = $orig->connections_from(array(
			"type" => "RELTYPE_COPY",
		));

		foreach($event_clones as $event_clone)
		{
			$clones[] = $event_clone->prop("to");
		};

		obj_set_opt("no_auto_translation", 1);
		$translations = $orig->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));

		foreach($translations as $translation)
		{
			$transx[] = $translation->prop("to");
		};
		obj_set_opt("no_auto_translation", 0);

		// uut venda ei looda kui ta juba olemas on

		foreach($new_ones as $new_id => $whatever)
		{

			// kurat, siin on ju kamm sees, ta üritab luua venda projekti enda juurde. GRÄH
			$event_obj->create_brother($new_id);

			// aga vend tuleb luua iga keele alla kuhu sündmus pandud on!
			// ja tuleks ka kontrollida kas see seos on juba olemas või ei
			// seos projektist sündmusse

			// mida fakki ma sin teen .. mind ei huvita ju absoluutselt see case .. tõlked on ikkagi
			// originaalil ja mitte vennal.

			foreach($transx as $trans_item)
			{
				/*
				obj_set_opt("no_auto_translation", 1);
				$trans_obj = new object($trans_item);
				obj_set_opt("no_auto_translation", 0);
			
				dbg::p1("translation is " . $trans_obj->id() . "/" . $trans_obj->lang());
				
				$trans_brother = $trans_obj->create_brother($new_id);
				$trans_brother_obj = new object($trans_brother);
				*/

				// by default tehakse tõlge ju aktiivse keele koodiga
				/*
				$trans_brother_obj->set_lang($trans_obj->lang());
				$trans_brother_obj->save();
				*/

				// kloonil pole alguskuupäeva, vaat mis :(
				//dbg::p1("created translation with id " . $trans_brother_obj->id());
			};

			foreach($clones as $clone_item)
			{
				/*
				$clone_obj = new object($clone_item);
				$clone_obj->create_brother($new_id);
				*/

				// ja tõlked on ju kaaa vaja kloonida! ooh fuck
				/*
				$prj_inst->connect_event(array(
					"id" => $new_id,
					"event_id" => $clone_item,
				));
				*/
			};

			// lisaks tuleb koopiad korralikult ära connectida
		};

		$awt->stop("connect-to-project");

	}





	

};
?>
