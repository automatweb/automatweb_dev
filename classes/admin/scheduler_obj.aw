<?php
// $Header: /home/cvs/automatweb_dev/classes/admin/scheduler_obj.aw,v 1.2 2004/10/28 09:47:35 kristo Exp $
// scheduler.aw - Scheduler

// okey, objektid mida käima tõmmatakse, defineeritakse seostega. Metainfos salvestatud
// asjad lennaku kus kurat. St88rl arvas, et nende konvertimisega mingit jama ei teki

// korduse kellajad, well, nende jaoks tuleb analoogselt kalendri sündmusega teine
// seose tüüp. CL_RECURRENCE peale

// login_uid ja login_pass kahh

/*
@classinfo relationmgr=yes
@default table=objects
@default field=meta
@default method=serialize
@default group=general

@property login_uid type=textbox 
@caption Kasutaja

@property login_password type=password
@caption Parool


@default group=recurrence

@property recurrence type=releditor reltype=RELTYPE_RECURRENCE use_form=emb mode=manager
@caption Kordused

@groupinfo recurrence caption=Kordused submit=no

@reltype TARGET_OBJ value=1 clid=CL_XML_IMPORT,CL_LIVELINK_IMPORT
@caption Sihtobjekt

@reltype RECURRENCE value=2 clid=CL_RECURRENCE
@caption Kordus

@reltype AW_LOGIN value=3 clid=CL_AW_LOGIN
@caption AW login

*/
class scheduler_obj extends class_base
{
	function scheduler_obj()
	{
		$this->init(array(
			"clid" => CL_SCHEDULER
		));
	}

	function set_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "recurrence":
				$this->re_schedule = true;
				break;
		};
		return $retval;
	}

	function callback_post_save($arr)
	{
		// use only the first for now
		$conns = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_RECURRENCE,
		));

		if (sizeof($conns) > 0)
		{
			$first = reset($conns);
			$rep_id = $first->prop("to");
		};

		// again, only the first for now
		$targets = $arr["obj_inst"]->connections_from(array(
			"type" => RELTYPE_TARGET_OBJ,
		));

		if (sizeof($targets) > 0)
		{
			$target = reset($targets);
			$target_id = $target->prop("to");
			$target_obj = $target->to();
		};

		// try and schedule the event
		if ($rep_id && $target_id)
		{
			$sch = get_instance("scheduler");
			$event_url = $this->mk_my_orb("invoke",array("id" => $target_id),$target_obj->class_id());
			$sch->add(array(
				"event" => $event_url,
				"rep_id" => $rep_id,
				"uid" => $arr["obj_inst"]->prop("login_uid"),
				"password" => $arr["obj_inst"]->prop("login_password"),
			));
		};


	}

	

	// ah yes. Each time I'm saving the schedulering object, I need to create a new
	// record in the scheduler table. And that pretty much is it too
	
	//$this->add(array("event" => $link,"rep_id" => $id, "uid" => $obj["meta"]["login_uid"],"password" => $obj["meta"]["login_password"]));
}
?>
