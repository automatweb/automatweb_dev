<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/personnel_management/personnel_management_cv_search.aw,v 1.7 2006/03/17 15:06:30 ahti Exp $
// personnel_management_cv_search.aw - CV Otsing 
/*

@classinfo syslog_type=ST_PERSONNEL_MANAGEMENT_CV_SEARCH relationmgr=yes

@default table=objects
@default group=general
@default form=cv_search
@default store=no

@property s_cv_name type=textbox
@caption Nimi

@property s_cv_company type=textbox
@caption Ettevõte

@property s_cv_job type=textbox
@caption Ametinimetus

@layout pay type=vbox

@property s_cv_paywish type=textbox parent=pay
@caption Palk alates

@property s_cv_paywish2 type=textbox parent=pay
@caption Palk kuni

@property s_cv_valdkond type=classificator mode=checkboxes multiple=1 orient=vertical reltype=RELTYPE_TEGEVUSVALDKOND
@caption Tegevusala

@property s_cv_liik type=classificator multiple=1 mode=checkboxes
@caption Töö liik

@property s_cv_asukoht type=objpicker multiple=1 clid=CL_CRM_CITY orient=vertical
@caption Töötamise piirkond

@property s_cv_koormus type=classificator multiple=1 orient=vertical mode=checkboxes
@caption Töökoormus

@property personality type=textarea
@caption Isikuomadused

@property comments type=textarea
@caption Kommentaarid

@property recommenders type=textarea
@caption Soovitajad

@property cv_search_button type=submit value=Otsi
@caption Otsi

@property cv_search_results type=table no_caption=1
@caption Otsingutulemused 

@property no_reforb type=hidden value=1

@forminfo cv_search onload=init_search onsubmit=test method=get

@reltype TEGEVUSVALDKOND value=6 clid=CL_META
@caption Tegevusala

@reltype LINN value=5 clid=CL_CRM_CITY
@caption Linn

*/

class personnel_management_cv_search extends class_base
{
	var $my_profile;
	
	function personnel_management_cv_search()
	{
		$this->init(array(
			"tpldir" => "applications/personnel_management/personnel_management_cv_search",
			"clid" => CL_PERSONNEL_MANAGEMENT_CV_SEARCH
		));
		if (!aw_global_get("no_db_connection"))
		{
			$personalikeskkond = get_instance(CL_PERSONNEL_MANAGEMENT);
			$this->my_profile = $personalikeskkond->my_profile;
		}
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "s_cv_name":
				global $XX3;
				if ($XX3)
				{
					print dbg::process_backtrace(debug_backtrace());
					print "<pre>";
					print_r($arr);
					print "</pre>";
				};
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "cv_search_results":
				$this->do_sres_tbl($arr);
				break;

			case "s_cv_ametinimetus":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "s_cv_palgasoov":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "s_cv_valdkond":
				$ol = new object_list(array(
					"class_id" => CL_META,
					"parent" => 111189,
					"lang_id" => array(),
					"site_id" => array()
				));
				$prop["options"] = $ol->names();
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "s_cv_liik":
				$ol = new object_list(array(
					"class_id" => CL_META,
					"parent" => 111190,
					"lang_id" => array(),
					"site_id" => array()
				));
				$prop["options"] = $ol->names();
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "s_cv_asukoht":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "s_cv_koormus":
				$ol = new object_list(array(
					"class_id" => CL_META,
					"parent" => 111188,
					"lang_id" => array(),
					"site_id" => array()
				));
				$prop["options"] = $ol->names();
				$prop["value"] = $arr["request"][$prop["name"]];
				break;

			case "s_cv_job_addinfo":
				$prop["value"] = $arr["request"][$prop["name"]];
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	

	/**
		@attrib name=test all_args="1"
	**/
	function test($arr)
	{
		$arr["form"] = "cv_search";
		return $this->change($arr);
	}

	function get_cv_list()
	{
		$manager = current($this->my_profile["manager_list"]);
		if(!is_object($manager))
		{
			die();
		}
		
		$tootsijad = $manager->connections_from(array("type" => "RELTYPE_TOOTSIJA"));
		
		foreach ($tootsijad as $otsija)
		{
			$job_seekers[] = $otsija->prop("to");
		}
		
		$tootsijad_obj_list = new object_list(array(
			"class_id" => CL_CRM_PERSON,
        	"oid" => $job_seekers,
       	));
		
       	$tootsijad_obj_list = $tootsijad_obj_list->arr();
       	foreach($tootsijad_obj_list as $otsija)
       	{
       		if($otsija->prop("default_cv"))
       		{
       			$cv_oids[] = $otsija->prop("default_cv");
       		}
       	}
       	return $cv_oids;
	}
	
	
	function do_sres_tbl($arr)
	{
		
		$t =& $arr["prop"]["vcl_inst"];
		$this->_init_sres_tbl($t);
				
		$params = array(
			"class_id" => CL_PERSONNEL_MANAGEMENT_CV,
			"status" => STAT_ACTIVE,
			"language" => array(),
			"site_id" => array(),
			"oid" => $this->get_cv_list(),
		);

		$ps = true;
		
		/*
		$sa = new aw_array($arr["request"]);
	
		foreach($sa->get() as $k => $v)
		{
			if ($v != "" && substr($k, 0, 4) == "s_cv")
			{
				$nn = substr($k, 5);
				if ($nn == "name")
				{
					$params[$nn] = "%".$v."%";
				}
				else
				if ($nn == "ametinimetus")
				{
					$params[$nn] = "%".$v."%";
				}
				else
				if ($nn == "job_addinfo")
				{
					$params[$nn] = "%".$v."%";
				}
				$ps = true;
			}
		}
		
		print_r($ps);
		*/
		if ($ps)
		{
			$ol = new object_list($params);

			for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{
				
				// manual search, just because I'm a fucking asshole and the important cv datas are in metadata anyway - terryf.
				if ($arr["request"]["s_cv_palgasoov"])
				{
					if ($o->prop("palgasoov") < $arr["request"]["s_cv_palgasoov"])
					{
						continue;
					}
				}
				if (is_array($arr["request"]["s_cv_valdkond"]) && count($arr["request"]["s_cv_valdkond"]) > 0)
				{
					$has_any = false;
					$dat = array();
					foreach($o->connections_from(array("type" => "RELTYPE_TEGEVUSVALDKOND")) as $c)
					{
						$dat[$c->prop("to")] = $c->prop("to");
					}
					foreach($arr["request"]["s_cv_valdkond"] as $tmpid)
					{
						if ($dat[$tmpid])
						{
							$has_any = true;
						}
					}
					if (!$has_any)
					{
						continue;
					}
				}
				if (is_array($arr["request"]["s_cv_liik"]) && count($arr["request"]["s_cv_liik"]) > 0)
				{
					$has_any = false;
					$dat = array();
					foreach($o->connections_from(array("type" => "RELTYPE_LIIK")) as $c)
					{
						$dat[$c->prop("to")] = $c->prop("to");
					}
					foreach($arr["request"]["s_cv_liik"] as $tmpid)
					{
						if ($dat[$tmpid])
						{
							$has_any = true;
						}
					}
					if (!$has_any)
					{
						continue;
					}
				}
				if (is_array($arr["request"]["s_cv_asukoht"]) && count($arr["request"]["s_cv_asukoht"]) > 0)
				{
					$has_any = false;
					$dat = array();
					foreach($o->connections_from(array("type" => "RELTYPE_LINN")) as $c)
					{
						$dat[$c->prop("to")] = $c->prop("to");
					}
					foreach($arr["request"]["s_cv_asukoht"] as $tmpid)
					{
						if ($dat[$tmpid])
						{
							$has_any = true;
						}
					}
					if (!$has_any)
					{
						continue;
					}
				}
			
				if (is_array($arr["request"]["s_cv_koormus"]) && count($arr["request"]["s_cv_koormus"]) > 0)
				{
					$has_any = false;
					$dat = $o->prop("koormus");
					foreach($arr["request"]["s_cv_koormus"] as $tmpid)
					{
						if ($dat[$tmpid])
						{
							$has_any = true;
						}
					}
					if (!$has_any)
					{
						continue;
					}
				}
			
				$t->define_data(array(
					"name" => html::href(array(
						"url" => $this->mk_my_orb("change", array("id" => $o->id()), CL_PERSONNEL_MANAGEMENT_CV, true, true),
						"caption" => $o->name()
					))
				));
			}
		}
	}

	function _init_sres_tbl(&$t)
	{
		$t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi")
		));
	}
}
?>
