<?php
// $Header: /home/cvs/automatweb_dev/classes/personalihaldus/Attic/cv.aw,v 1.2 2004/03/17 22:20:17 sven Exp $
// cv.aw - CV 
/*
@classinfo syslog_type=ST_CV relationmgr=yes

@default table=objects
@default group=general
@default field=meta

@tableinfo staff_cv master_table=objects master_index=oid index=oid

@property navtoolbar type=toolbar no_caption=1 store=no group=arvutioskus,keeleoskused,haridustee,tookogemus,othercvs


//////////////////////////////TÜ KARJÄÄRITEENISTUS\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@property tu_education type=releditor reltype=RELTYPE_EDUCATION props=eriala,algusaasta,loppaasta,teaduskond,oppekava,oppeaste,oppevorm,lisainfo_edu group=karjaariteenistus

/////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\


@property educationtabel type=table no_caption=1 store=no group=haridustee 

@property keeleoskused type=table store=no group=keeleoskused no_caption=1
@property lang_skill_label type=text group=keeleoskused subtitle=1 value=Lisa&nbsp;uus&nbsp;keeleoskus store=no

@property computer_skills type=table store=no group=arvutioskus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Arvutioskuste&nbsp;lisamine store=no

@property othercvs type=table store=no group=othercvs no_caption=1



@property jobs type=table store=no group=tookogemus no_caption=1
@property comp_skill_label type=text group=arvutioskus subtitle=1 value=Lisa&nbsp;töökogemus store=no

@property haridus_label type=text group=haridustee subtitle=1 value=Kooli&nbsp;lisamine store=no
@property education type=releditor reltype=RELTYPE_EDUCATION props=lisainfo_edu,loppaasta,algusaasta,eriala,kool group=haridustee
@caption Haridus

@property language_skills type=releditor reltype=RELTYPE_LANG props=keel,tase  group=keeleoskused
@caption Keeleoskus

@property kogemused type=releditor reltype=RELTYPE_KOGEMUS props=asutus,algus,kuni,ametikoht,tasks group=tookogemus
@caption Kogemused

@property arvutioskus type=releditor reltype=RELTYPE_ARVUTIOSKUS props=oskus,tase group=arvutioskus
@caption Arvutioskus

@property juhiload type=chooser multiple=1 method=serialize group=driving_licenses
@caption Juhiload


//////////////////// TAB TÖÖSOOV \\\\\\\\\\\\\\\\\\\\\\\\\

@property valdkond type=classificator group=toosoov method=serialize multiple=1 orient=vertical
@caption Tegevusala

@property liik type=classificator multiple=1 group=toosoov method=serialize
@caption T&ouml;&ouml; liik

@property asukoht type=relpicker multiple=1 automatic=1 reltype=RELTYPE_LINN group=toosoov method=serialize orient=vertical
@caption Linnad

@property koormus type=classificator group=toosoov method=serialize multiple=1 orient=vertical
@caption T&ouml;&ouml; koormus 

@property job_addinfo type=textarea group=toosoov field=addinfo table=staff_cv
@caption Lisainfo soovitava t&ouml;&ouml; kohta

@property soovitajad type=textarea group=toosoov field=recommenders table=staff_cv
@caption Soovitajad

@property sain_tood type=checkbox group=toosoov field=gotjob table=staff_cv
@caption Sain t&ouml;&ouml;d teie kaudu


//////////////////////////////TABID\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@groupinfo skills caption="Oskused"
@groupinfo arvutioskus caption="Arvutioskus" parent=skills
@groupinfo keeleoskused caption="Keeleoskus" parent=skills
@groupinfo driving_licenses caption="Juhiload" parent=skills


@groupinfo hariduskaik_main caption="Haridusk&auml;ik"
@groupinfo haridustee caption="Õpingud" parent=hariduskaik_main

@groupinfo tookogemus caption="T&ouml;&ouml;kogemused"
@groupinfo toosoov caption="Soovitud töö"

@groupinfo karjaariteenistus caption="Õpingud TÜ-s" parent=hariduskaik_main


@groupinfo othercvs caption="Teised CV-d"

////////////////////////////SEOSED\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
@reltype EDUCATION value=1 clid=CL_EDUCATION 
@caption Haridus

@reltype KOGEMUS value=4 clid=CL_PERSONALIHALDUS_TOOKOGEMUS
@caption Kogemus

@reltype LANG value=2 clid=CL_PERSONALIHALDUS_LANG
@caption Keeleoskus

@reltype ARVUTIOSKUS value=3 clid=CL_PERSONALIHALDUS_ARVUTIOSKUS
@caption Arvutioskus

@reltype LINN value=5 clid=CL_CRM_CITY
@caption Linn

@reltype TEGEVUSVALDKOND value=6 clid=CL_META
@caption Tegevusala

*/

class cv extends class_base
{

	function cv()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "personalihaldus/cv",
			"clid" => CL_CV
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
			
			case "juhiload":
				 $data["options"] = array(
                 	"A" => "A",
                 	"B" => "B",
                 	"C" => "C",
                 	"D" => "D",
                 	"E" => "E",
                 );
			break;
			
			case "othercvs":
				
				$table = & $arr["prop"]["vcl_inst"];
				
				$table->define_field(array(
					"name" => "othercvs",
					"caption" => "CV",
					"sortable" => 1
				));
				
				$table->define_field(array(
					"name" => "created",
					"caption" => "Lisatud",
					"sortable" => 1
				));
				
				$table->define_field(array(
					"name" => "modified",
					"caption" => "Muudetud",
					"sortable" => 1
				));
				
				$table->define_chooser(array(
					"name" => "sel",
					"field" => "from",
				));
				
				$conn = new connection();
				
				$conn = $conn->find(array(
					"from.class_id" => CL_CRM_PERSON,
					"to" => $arr["obj_inst"]->id(),	
				));
				
				$conn = array_shift($conn);
				
				$person =& obj($conn["from"]);
				
				if($conn)
				{
					foreach ($person->connections_from(array("type" => RELTYPE_CV)) as $cv)
					{
						$cv_connectionid = $cv->id();

						$cv = & obj($cv->prop("to"));
					
						$table->define_data(array(
							"othercvs" => html::href(array(
										"caption" => $cv->name(),
										"url" => $this->mk_my_orb("change", array("id" => $cv->id()), "cv"),
								)),
							"created" => get_lc_date($cv->created()),
							"modified" => get_lc_date($cv->modified()),
							"from" => $cv_connectionid 
						));
					}
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
			
			
			case "sisseastumine":
				for($i=date("Y"); $i>date("Y") - 80; $i--){
					$data["options"][$i]=$i;
				}
			break;
		
			case "educationtabel":
				
				$table =& $arr["prop"]["vcl_inst"];
				
				$table->define_field(array(
					"name" => "kool",
					"caption" => "Kool",
					"sortable" => 1
				));
				
				$table->define_field(array(
					"name" => "periood",
					"caption" => "Periood",
					"sortable" => 1
				));
				
				$table->define_field(array(
					"name" => "eriala",
					"caption" => "Eriala",
					"sortable" => 1
				));
				
				$table->define_chooser(array(
					"name" => "sel",
					"field" => "from",
				));
				
				foreach($arr["obj_inst"]->connections_from(array("type" => RELTYPE_EDUCATION)) as $haridus)
				{
					$connection_id = $haridus->id();
					
					$haridus = obj($haridus->prop("to"));
					
					$eriala=obj($haridus->prop("eriala"));
					
					$table->define_data(array(
						"kool" => html::href(array(
										"caption" => $haridus->prop("kool"),
										"url" => $this->mk_my_orb("change", array("id" => $haridus->id()), "education"),
								)),
						"eriala" => $eriala->name(),
						"periood" => $haridus->prop("algusaasta") ."-". $haridus->prop("loppaasta"),
						"from" => $connection_id,
					));
					
				}
				
				$table->set_default_sortby("periood");
				$table->sort_by();
			break;
			
			case "lopetamine":
			
				for($i=date("Y"); $i>date("Y") - 80; $i--)
				{
					$data["options"][$i]=$i;
				}
				
			break;
			
			case "keeleoskused":
			
				$table =& $arr["prop"]["vcl_inst"];
				
				$table->define_field(array(
					"name" => "keel",
					"caption" => "Keel",
					"sortable" => 1
				));
				
				$table->define_field(array(
					"name" => "tase",
					"caption" => "Tase",
					"sortable" => 1
				));
				
				$table->define_chooser(array(
					"name" => "sel",
					"field" => "from",
				));
				
				foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_LANG)) as $keeleoskus)
				{
					$connection_id=$keeleoskus->id();
					
					$keeleoskus = obj($keeleoskus->prop("to"));
					
					$keel = obj($keeleoskus->prop("keel"));
					$tase = obj($keeleoskus->prop("tase"));
					
				
					$table->define_data(array(
						"keel" => html::href(array(
									"caption" => $keel->name(),
									"url" => $this->mk_my_orb("change", array("id" => $keeleoskus->id()), "personalihaldus_lang"),
								)),
						"tase" => $tase->name(),
						"from" => $connection_id,
					));	
				
				}
								
			break;
			
			case "jobs":
				$table =& $arr["prop"]["vcl_inst"];
				
				$table->define_field(array(
					"name" => "asutus",
					"caption" => "Asutus",
					"sortable" => 1
				));
				
				$table->define_field(array(
					"name" => "ametikoht",
					"caption" => "Ametikoht",
					"sortable" => 1
				));

				$table->define_field(array(
					"name" => "alates",
					"caption" => "Alates",
					"sortable" => 1
				));			
				
				$table->define_field(array(
					"name" => "kuni",
					"caption" => "Kuni",
					"sortable" => 1
				));			
				
				$table->define_chooser(array(
					"name" => "sel",
					"field" => "from",
				));
				
				
				foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_KOGEMUS)) as $kogemus)
				{
					$connection_id=$kogemus->id();
					
					$kogemus = obj($kogemus->prop("to"));
					$table->define_data(array(
						"asutus" => html::href(array(
									"caption" => $kogemus->prop("asutus"),
									"url" => $this->mk_my_orb("change", array("id" => $kogemus->id()), "personalihaldus_tookogemus"),
								)),
						"ametikoht" => $kogemus->prop("ametikoht"),
						"alates" => get_lc_date($kogemus->prop("algus")),
						"kuni" => get_lc_date($kogemus->prop("kuni")),
						"from" => $connection_id,
					));	
				}
			break;
			
			case "computer_skills":
				
				$table =& $arr["prop"]["vcl_inst"];
				
				$table->define_field(array(
					"name" => "oskus",
					"caption" => "Oskus",
				));
				
				$table->define_field(array(
					"name" => "tase",
					"caption" => "Tase",
				));
				
				$table->define_chooser(array(
					"name" => "sel",
					"field" => "from",
				));
				
				foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_ARVUTIOSKUS)) as $c_skill)
				{
					$connection_id=$c_skill->id();
					
					$c_skill = obj($c_skill->prop("to"));
					
					$oskus = obj($c_skill->prop("oskus"));
					$tase = obj($c_skill->prop("tase"));
				
					$table->define_data(array(
						"oskus" => html::href(array(
									"caption" => $oskus->name(),
									"url" => $this->mk_my_orb("change", array("id" => $c_skill->id()), "personalihaldus_arvutioskus"),
								)),
						"tase" => $tase->name(),
						"from" => $connection_id,
					));	
				}
				
			break;
			
		};
		return $retval;
	}
	
	/**
		@attrib name=delete_rels
	**/
	function delete_rels($arr)
	{
		foreach ($arr["sel"] as $conn)
		{
			$conn=new connection($conn);
			$conn->delete();	
		}
		
		//On mõni parem viis tagasi minekuks?
		echo " <SCRIPT LANGUAGE=\"JavaScript\">history.go(-1)</SCRIPT>";
	}
	
	function sectors_create_rels(&$arr)
	{	
		$sector_conns = new connection();

		foreach ($arr["obj_inst"]->connections_from(array("type" => RELTYPE_TEGEVUSVALDKOND)) as $valdkond)
		{
			$valdkond->delete();	
		}
	
		foreach ($arr["form_data"]["valdkond"] as $valdkond)
		{
			if($valdkond)
			{
				$new_sector_conn = new connection();
				$new_sector_conn->change(array(
					"from" => $arr["obj_inst"]->id(),
					"to" => $valdkond,
					"reltype" => RELTYPE_TEGEVUSVALDKOND,
				));
			}
		}
	}
	
	function set_property($arr = array())
	{
		$data = &$arr["prop"];
		$retval = PROP_OK;
		switch($data["name"])
        {
			case "valdkond":
				$this->sectors_create_rels($arr);
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
	
	/*
	
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
	*/
}
?>
