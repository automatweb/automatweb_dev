<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/calendar/staging.aw,v 1.2 2004/10/13 11:06:44 duke Exp $
// staging.aw - Lavastus 
/*

@classinfo syslog_type=ST_STAGING relationmgr=yes

@default table=objects
@default group=general

@property start1 type=datetime_select field=start table=planner
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

@property to type=text store=no group=trans
@caption To

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

		// check if this is a copy and then show connections from the original object
		$copy_conns = $o->connections_to(array(
			"type" => RELTYPE_COPY,
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

		$rv["active_" . $prefix] = array(
			"type" => "datetime_select",
			"name" => $prefix . "[active]",
			"value" => $o->prop("start1"),
			"caption" => "Aktiivne objekt",
		);

		$empty_slots = 30;
		foreach($conns as $conn)
		{
			$to = $conn->to();
			$id = $to->id();
			$rv["existing_" . $prefix . $id] = array(
				"type" => "datetime_select",
				"name" => $prefix . "[existing][" . $id . "]",
				"caption" => "Koopia",
				"group" => $arr["prop"]["group"],
				"value" => $to->prop("start1"),
			);
			$empty_slots--;
		}

		$rv["sbx"] = array(
			"type" => "text",
			"subtitle" => 1,
			"caption" => "Uued",
		);

		for ($i = 1; $i <= $empty_slots; $i++)
		{
			// esimese väärtus peab olema eventi enda alguse aeg
			$rv["new_" . $prefix . $i] = array(
				"type" => "datetime_select",
				"name" => $prefix . "[new][" . $i . "]",
				"caption" => "Uus $i",
				"group" => $arr["prop"]["group"],
				"value" => $start1,
			);

			$rv["newx_" . $prefix . $i] = array(
				"type" => "checkbox",
				"name" => $prefix . "[newx][" . $i . "]",
				"caption" => "Tee sündmus $i",
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
			case "to":
				$prop["value"] = $this->gen_to_overview($arr);
				break;




		};
		return $retval;
	}

	function gen_to_overview($arr)
	{
		$rv = "";
		$o = $arr["obj_inst"];	
		obj_set_opt("no_auto_translation", 1);
		$trans = $o->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));
		foreach($trans as $t)
		{
			$to = $t->to();
			$rv .= "tõlge: " . $to->id() . " / " . $to->name() . " / ". $to->lang() . "<br>";

		};

		$copies = $o->connections_from(array(
			"type" => "RELTYPE_COPY",
		));

		$rv .= "Koopiad<br>";
		foreach($copies as $c)
		{
			$to = $c->to();
			$rv .= "koopia: " . $to->id() . " / " . $to->name() . " / ". $to->lang() . "<br>";

			$trans = $to->connections_from(array(
				"type" => RELTYPE_TRANSLATION,
			));
			foreach($trans as $t)
			{
				$tt = $t->to();
				$rv .= "koopia tõlge: " . $tt->id() . " / " . $tt->name() . " / ". $tt->lang() . "<br>";

			};

			$ol = new object_list(array(
				"brother_of" => $to->id(),
			));

			foreach($ol->arr() as $o)
			{
				$rv .= "koopia vend: " . $o->id() . "/" . $o->name . "/" . $o->lang() . "/" . $o->parent() .  "<br>";
			};
		};

		$ol = new object_list(array(
			"brother_of" => $o->id(),
		));

		// nii .. need vennad on nüüd teiste projektide all.

		// ma pean nüüd võtma sündmuse tõlked ja need KA teiste projektide alla vennastama.
		// parentiks on ikka see projekt ise, aga vennnad on teise keele ID-ga

		// and that pretty much is it
		foreach($ol->arr() as $o)
		{
			$rv .= "vend: " . $o->id() . "/" . $o->name . "/" . $o->lang() . "/" . $o->parent() .  "<br>";

			$conns = $o->connections_from(array(
				"type" => RELTYPE_TRANSLATION,
			));

			$rv .= "vs = " . sizeof($conns) . "<br>";
		};
		obj_set_opt("no_auto_translation", 0);
		return $rv;
	}

	/**
		@attrib name=fixxer
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
		
		// check if this is a copy and then show connections from the original object
		$copy_conns = $o->connections_to(array(
			"type" => RELTYPE_COPY,
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


		// siin loome (mitte ei uuenda) koopiaid - koopia tegemisel
		// 1. teha uus objekt
		// 2. kanda info üle
		// 3. teha tõlgete objektid
		// 4. kanda info üle

		// kõigepealt on vaja välja mõelda mis keeltes meil tõlked on

		// auto_translation on vist selleks et võetaks seosed ikka originaali pealt
		obj_set_opt("no_auto_translation", 1);
		$tr_conns = $o->connections_from(array(
			"type" => RELTYPE_TRANSLATION,
		));

		// create list of translations
		$trans_clone = array();
		foreach($tr_conns as $tr_conn)
		{
			$trans_clone[$tr_conn->prop("to.lang_id")] = $tr_conn->prop("to");
		};

		// get a list of brothers, get a list of ...

		// can't I create a simpler infrastructure for this shit?

		// get a list of brothers ... get a list of translations from each brother

		// now recreate all that structure


		// update existing objects
		if (is_array($times["existing"]))
		{
			$ext = $times["existing"];
			foreach($ext as $obj_id => $date_data)
			{
				// äkki ma saan seda asja kuidagi liita ..
				// siin ma uuendan ainult aegu .. uute tõlgete tegemine on tõlkekomponendi
				// ja mitte minu ülesanne
				$ts = $de->get_timestamp($date_data);

				$obj_copy = new object($obj_id);
				$obj_copy->set_prop("start1",$ts);

				$obj_copy->save();
			};
		}
		
		obj_set_opt("no_auto_translation", 1);

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
					$props = $arr["obj_inst"]->properties();

					// XX: miks ma ei saa omadusi üle tuua? :(
					// loome uue koopia objekti
					$new_obj = new object($o->properties());
					$new_obj->set_prop("start1",$ts);
					$new_obj->save_new();

					foreach($props as $prop => $value)
					{
						if (substr($prop,0,1) == "u")
						{
							$new_obj->set_prop($prop,$value);
						};
					};

					$new_obj->save();

					// connection from original to the copy so that the original
					// can later manage copies
					$o->connect(array(
						"to" => $new_obj->id(),
						"reltype" => RELTYPE_COPY,
					));

					dbg::p1("created new object with id " . $new_obj->id());

					// now create copies of translation objects and connect them

					// for each new copy I have to copy all the translations as well
					obj_set_opt("no_auto_translation", 1);
					foreach($trans_clone as $trans_obj_id)
					{
						// need to clone those objects

						// nii aga tõlke objektid tuleb ümber connectida
						// originaalist siis
						$trans_obj = new object($trans_obj_id);

						// misasja siin tehakse?
						$orig_trans = $trans_obj;


						// create a copy of a single translation
						$trans_obj->save_new();

						dbg::p1("original translation object is " . $orig_trans->id() . " with lang " . $orig_trans->lang());
						dbg::p1("created translation object " . $trans_obj->id() . " with lang " . $trans_obj->lang());

						$tid = $trans_obj->id();

						dbg::p1("connect from " . $orig_trans->id() . "to $tid");

						// lingid master->slave tõlgete vahel

						// aga mis juhtub kui ma lähen muudan slave tõlget?
						$orig_trans->connect(array(
							"to"=> $tid,
							"reltype" => RELTYPE_COPY,
						));
	
						// new_obj on äsja loodud kloon originaalist
						$new_obj->connect(array(
							"to" => $tid,
							"reltype" => RELTYPE_TRANSLATION,
						));

						dbg::p1("connect from  " . $trans_obj->id() . " to " . $orig_trans->id());

						// ja see on kloon sellest teisest

						// ja muud infot ma ei muuda - seda lihtsalt pole tarvis ju


						// võib-olla tuleks implementeerida kustutamine
						// nii või teisiti, edasine action toimub juba tõlke komponendis
						$trans_obj->connect(array(
							"to" => $new_obj->id(),
							"reltype" => RELTYPE_ORIGINAL,
						));

					};
					obj_set_opt("no_auto_translation", 0);
					// I need to clone translations as well
					// 1. load all translation connections from the original
					// 2. create new objects from those with new start date
					// 3. connect them to the copied original ..
				};
			};

		};

		$valx = $de->get_timestamp($times["active"]);
		$arr["obj_inst"]->set_prop("start1",$valx);

		//arr($new_obj->properties());

		//$tmp_obj = new object();

		// get timestamp?
		// tsükkel üle times array - kusjuures 1 sisaldab hetkel kehtiva sündmuse kuupäeva
		
		// loo uued sündmused, nii palju kui neil infot on.
		// ehk - loe olemasolev objekt sisse
		// loo uue objekti instants - säti propertyd
		// säti uus algusaeg
		// connecti olemasoleva külge
		// salvesta

		// and I'm done

		//print "<h1>times end</h1>";


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
