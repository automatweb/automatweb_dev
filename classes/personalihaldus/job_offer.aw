<?php
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/job_offer.aw,v 1.3 2004/03/18 01:24:48 sven Exp $
// job_offer.aw - job_offer 
/*

@classinfo syslog_type=ST_JOB_OFFER relationmgr=yes

@default table=objects
@default group=general
@default field=meta

@tableinfo personnel_management_job index=oid master_table=objects master_index=oid

@property name type=textbox table=objects field=name group=info_about_job
@caption Ametikoht

@property navtoolbar type=toolbar no_caption=1 store=no group=kandideerinud

@property toosisu type=textarea field=about_job table=personnel_management_job  group=info_about_job
@caption Töö kirjeldus

@property noudmised type=textarea field=requirements table=personnel_management_job  group=info_about_job
@caption N&otilde;udmised kandidaadile

@property asukoht type=relpicker reltype=RELTYPE_LINN automatic=1 editonly=1 method=serialize field=meta table=objects  group=info_about_job
@caption Asukoht

@property deadline type=date_select field=deadline table=personnel_management_job  group=info_about_job
@caption Konkursi tähtaeg

@property tegevusvaldkond type=classificator field=meta method=serialize multiple=1 editonly=1 table=objects store=connect group=info_about_job reltype=RELTYPE_TEGEVUSVALDKOND orient=vertical
@caption Tegevusvaldkond

@property email type=relmanager reltype=RELTYPE_EMAIL props=mail  group=info_about_job field=meta method=serialize
@caption Meiliaadressid

@property phone type=relmanager reltype=RELTYPE_PHONE props=name  group=info_about_job field=meta method=serialize
@caption Telefoninumbrid

property cv_file_rel type=releditor reltype=RELTYPE_CVFILE props=file group=info_about_job_file

@property candits type=table group=kandideerinud
@caption Kandideerijad

@property kandideerin type=select field=meta method=serialize store=no group=minu_kandidatuur
@caption Vali cv kandideerimiseks


@reltype EMAIL value=2 clid=CL_ML_MEMBER
@caption E-post

@reltype PHONE value=3 clid=CL_CRM_PHONE
@caption Telefon

@reltype LINN value=4 clid=CL_CRM_CITY
@caption Linn

@reltype CV value=5 clid=CL_CV
@caption Kandidaat

@reltype TEGEVUSVALDKOND value=6 clid=CL_META
@caption Tegevusvaldkond

@reltype CVFILE value=7 clid=CL_FILE
@caption CV failina


@groupinfo info_about_job caption="Tööpakkumine"
@groupinfo info_about_job_file caption="Tööpakkumine failina" parent=info_about_job_main
@groupinfo minu_kandidatuur caption="Minu kandidatuur"
@groupinfo kandideerinud caption="Kandideerijad"

*/

class job_offer extends class_base
{
	function job_offer()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "personalihaldus/job_offer",
			"clid" => CL_JOB_OFFER
		));
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them

	
	function get_property($arr)
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "candits":
				
				$table=&$arr["prop"]["vcl_inst"];

				$table->define_field(array(
					"name" => "cv",
					"caption" => "CV",
					"sortable" => 1,
				));
				
				$table->define_field(array(
					"name" => "perenimi",
					"caption" => "Perekonnanimi",
					"sortable" => 1,
				));
				
				
				$table->define_field(array(
					"name" => "eesnimi",
					"caption" => "Eesnimi",
					"sortable" => 1,
				));
				
				$table->define_field(array(
					"name" => "date",
					"caption" => "Kuupäev",
					"sortable" => 1,
				));
				
				
				$table->define_chooser(array(
					"name" => "sel",
					"field" => "from",
				));
				
				
				
				
				foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_CV)) as $cv)
				{
				
					$connection_id = $cv->id();
					$connection_created= $cv->prop("created");
					
					$cv = obj($cv->prop("to"));
					$conn = new connection();
				
					$conn = $conn->find(array(
						"from.class_id" => CL_CRM_PERSON,
						"to" => $cv->id(),	
					));
				
					$conn = array_shift($conn);
					$person =& obj($conn["from"]);
						
					$table->define_data(array(
						"cv" => html::href(array(
												"caption" => $cv->name(),
												"url" => $this->mk_my_orb("change", array("id" => $cv->id()) ,"cv"), 
											)),
						"perenimi" 		=> html::href(array(
												"caption" => $person->prop("lastname"),
												"url" =>$this->mk_my_orb("change", array("id" => $person->id()), "crm_person"), 
											)),
											
						"eesnimi" => html::href(array(
												"caption" => $person->prop("firstname"),
												"url" =>  $this->mk_my_orb("change", array("id" => $person->id()) ,"crm_person"),
											)),
						"date" => get_lc_date($connection_created)
					));
				}
			break;
			
			case "navtoolbar":
				
				$tb = &$data["toolbar"];
				
				$tb->add_button(array(
					"name" => "delete",
					"img" => "delete.gif",
					"tooltip" => "Kustuta valitud seosed",
					"action" => "delete_rels",
				));
			break;
			
			case "kandideerin":
			
				$data["options"] = $this->my_candidature_get();
				
				$person_obj = $this->get_my_person_obj();
				$mycvs = $this->get_cvs_of_person($person_obj);
							
				foreach ($mycvs as $mycv)
				{
					if($arr["obj_inst"]->connections_from(array("to" => $mycv->id())))
					{
						$data["selected"] = $mycv->id();		
					}
				}
				
			break;
		};
		return $retval;
	}
	
	function my_candidature_get()
	{
		$user_id = users::get_oid_for_uid(aw_global_get("uid"));
		$user_obj = & obj($user_id);
		$person_obj = $user_obj->connections_from(array("type" => RELTYPE_PERSON));
		
		if($person_obj)
		{
			$person_obj = array_pop($person_obj);
			$person_obj = &obj($person_obj->prop("to"));
			
			$mycvs=$person_obj->connections_from(array("type" => 19)); 
			
			$employee_group = aw_ini_get("employee.group");
			
			$gidlist = aw_global_get("gidlist");
			
			//Kui kasutaja kuulub tööotsijate gruppi
			foreach($gidlist as $gid)
			{
				if($employee_group==users::get_oid_for_gid($gid))
				{
					
					$retval[] = "--Vali CV--";
					foreach ($mycvs as $mycv)
					{
						$mycv = &obj($mycv->prop("to"));
						$retval[$mycv->id()] = $mycv->name();
						
					}
					
					return $retval;				
				}
			}

		}		
	}
	
	/* This function returns current logged user person object */
	function get_my_person_obj()
	{
		$user_id = users::get_oid_for_uid(aw_global_get("uid"));
		$user_obj = & obj($user_id);
		$person_obj = $user_obj->connections_from(array("type" => RELTYPE_PERSON));
		
		if($person_obj)
		{
			$person_obj = array_pop($person_obj);
			return $person_obj->to();
		}
	}
	
	/* This function returns array of cv objects of person passed in argument */
	
	function get_cvs_of_person(&$person)
	{
		$cv_conns = $person->connections_from(array("type" => 19)); 
		foreach ($cv_conns as $cv_conn)
		{
			$return_cvs[] = $cv_conn->to();
		}
		return $return_cvs;
	}
	
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		

		
		switch($data["name"])
        {
			case "kandideerin" :
				//Kontrollime kas kasutjal on ehk juba mõni CV selle tööpakkumisega seostatud
				$person_obj = $this->get_my_person_obj();
		
				if($person_obj)
				{
					$mycvs=$this->get_cvs_of_person($person_obj); 
				}
				
				$conn = new connection();
				
				foreach ($mycvs as $cv)
				{
					$mycv_temp = $conn->find(array(
        				"from" => $arr["obj_inst"]->id(),
        				"type" => RELTYPE_CV,
        				"to" => $cv->id(),
					));
					if($mycv_temp)
					{
						$mycv_this[] = $mycv_temp; 
					}
				}
				
				if($mycv_this)
				{
					$mycv_this = array_pop(array_pop($mycv_this));
					$conn_this = new connection($mycv_this["id"]); 
					$conn_this->delete();
				}
				
				if($arr["prop"]["value"])
				{
					$newconn = new connection();
					$newconn->change(array("from" => $arr["obj_inst"]->id(), "to" => $arr["prop"]["value"], "reltype" => RELTYPE_CV));
				}
				
			break;
		}
		return $retval;
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
}
?>
