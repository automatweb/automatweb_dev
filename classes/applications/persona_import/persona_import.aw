<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/persona_import/persona_import.aw,v 1.3 2005/03/18 13:42:49 ahti Exp $
// persona_import.aw - Persona import 
/*

@classinfo syslog_type=ST_PERSONA_IMPORT relationmgr=yes

@default table=objects
@default group=general
@default field=meta
@default method=serialize

@property last_import type=text
@caption Viimane import toimus

@property invk type=text store=no
@caption Import

@default group=settings

@property ftp_settings type=releditor reltype=RELTYPE_DATA_SERVER rel_id=first props=server,username,password
@caption FTP seaded

@property xml_folder type=textbox
@caption XML faili asukoht serveris

@property xml_filename type=textbox
@caption XML faili nimi

@property xml_personnel_file type=textbox
@caption Töötajate XML fail

@property xml_structure_file type=textbox
@caption Struktuuriüksuste XML fail

@property xml_image_folder type=textbox
@caption Pildifailide kataloog (kui on eraldi)

@property aw_image_folder type=relpicker reltype=RELTYPE_FOLDER
@caption Pildiobjektide kataloog

@property crm_db_id type=relpicker reltype=RELTYPE_CRM_DB
@caption Kasutatav kliendibaas

@property recur_edit type=releditor reltype=RELTYPE_RECURRENCE use_form=emb group=autoimport rel_id=first
@caption Automaatse impordi seadistamine

@groupinfo settings caption="Seaded"
@groupinfo autoimport caption="Automaatne import"

@reltype CRM_DB value=1 clid=CL_CRM_DB
@caption kliendibaas

@reltype DATA_SERVER value=2 clid=CL_FTP_LOGIN
@caption FTP kasutaja

@reltype FOLDER value=3 clid=CL_MENU
@caption Kaust

@reltype LOGFILE value=4 clid=CL_FILE
@caption Logifail

@reltype RECURRENCE value=5 clid=CL_RECURRENCE
@caption Kordus

*/

class persona_import extends class_base
{
	function persona_import()
	{
		$this->init(array(
			"clid" => CL_PERSONA_IMPORT
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "last_import":
				$prop["value"] = locale::get_lc_date($prop["value"],6) . date(" H:i",$prop["value"]);
				break;

			case "invk":
				$prop["value"] = html::href(array(
					"url" => $this->mk_my_orb("invoke_import",array("id" => $arr["obj_inst"]->id())),
					"caption" => t("Käivita import"),
				));
				break;

		};
		return $retval;
	}

	/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{

		}
		return $retval;
	}	
	*/

	function get_config($arr)
	{
		$obj = new object($arr["id"]);
		$ftp_conns = $obj->connections_from(array(
			"type" => "RELTYPE_DATA_SERVER",
		));

		if (sizeof($ftp_conns) == 0)
		{
			die(t("You forgot to enter server data"));
		};
		$conn = reset($ftp_conns);
		$ftp_serv = new object($conn->prop("to"));

		$rv["ftp"] = array(
			"host" => $ftp_serv->prop("server"),
			"user" => $ftp_serv->prop("username"),
			"pass" => $ftp_serv->prop("password"),
		);

		if (is_oid($obj->prop("aw_image_folder")))
		{
			$rv["image_folder"] = $obj->prop("aw_image_folder");
		};
		return $rv;


	}

	/**
		@attrib name=invoke_import nologin="1"
		@param id required type=int
	**/
	function invoke_import($arr)
	{
		$obj = new object($arr["id"]);

		$config = $this->get_config($arr);

		//arr($config);
		//exit;

		if (sizeof($config["ftp"]) == 0)
		{
			die("You forgot to enter server data");
		};

		$crm_db_id = $obj->prop("crm_db_id");
		if (!is_oid($crm_db_id))
		{
			die("Nii ei saa ju rallit sõita!");
		};

		$crm_db = new object($crm_db_id);

		$folder_person = $crm_db->prop("folder_person");
		arr($crm_db->properties());

		if (!is_oid($folder_person))
		{
			die("Isikute kataloog valimata");
		};

		$folder_address = $crm_db->prop("dir_address");
		if (!is_oid($folder_address))
		{
			die("Aadresside kataloog valimata");
		};

		$dir_default = $crm_db->prop("dir_default");
		if (!is_oid($dir_default))
		{
			die("Default kataloog valimata");
		};

		// figure out variable manager
		$mt_conns = $crm_db->connections_from(array(
			"type" => "RELTYPE_METAMGR",
		));
		if (sizeof($mt_conns) == 0)
		{
			die("Kliendibaasil puudub muutujate haldur");
		};
		$first = reset($mt_conns);
		$metamgr = new object($first->prop("to"));

		$meta1list = new object_list(array(
			"parent" => $metamgr->id(),
			"class_id" => CL_META,
		));

		$meta1 = array_flip($meta1list->names());

		print "creating variable categories, if any ...";
		flush();

		if (!$meta1["Puhkuste liigid"])
		{
			$m1 = new object();
			$m1->set_parent($metamgr->id());
			$m1->set_class_id(CL_META);
			$m1->set_status(STAT_ACTIVE);
			$m1->set_name("Puhkuste liigid");
			$m1->save();
			$meta_cat["puhkused"] = $m1->id();
		}
		else
		{
			$meta_cat["puhkused"] = $meta1["Puhkuste liigid"];
		};
		
		if (!$meta1["Peatumiste liigid"])
		{
			$m1 = new object();
			$m1->set_parent($metamgr->id());
			$m1->set_class_id(CL_META);
			$m1->set_status(STAT_ACTIVE);
			$m1->set_name("Peatumiste liigid");
			$m1->save();
			$meta_cat["peatumised"] = $m1->id();
		}
		else
		{
			$meta_cat["peatumised"] = $meta1["Peatumiste liigid"];
		};

		print "Getting source data<br>";
		flush();

		$c = get_instance(CL_FTP_LOGIN);
		$c->connect($config["ftp"]);

		$fqfn = $obj->prop("xml_folder") . "/" . $obj->prop("xml_filename");
		$fdat = $c->get_file($fqfn);
		$c->disconnect();
		
		print "<h1>" . $fqfn . "</h1>";

		if (strlen($fdat) == 0)
		{
			die("Not enough data to process<bR>");
		};


		print "got data<br>";
	
		flush();

		$p = xml_parser_create();
		xml_parse_into_struct($p, $fdat, $vals, $index);
		xml_parser_free($p);

		print "parse finished, processing starts<br>";
		flush();

		$workers = array();

		$process_workers = $process_stops = false;

		$obj->set_prop("last_import",time());
		$obj->save();

		$interesting_containers = array("TOOTAJAD","PEATUMISED","PUHKUSED","YKSUSED");

		$w_open = false;
		$tmp = array();
		$target = false;
		$data = array();

		$processing = array();

		foreach($vals as $val)
		{
			if (in_array($val["tag"],$interesting_containers))
			{
				if ("open" == $val["type"])
				{
					$processing[$val["tag"]] = true;
					$target = $val["tag"];
				}
				elseif ("close" == $val["type"])
				{
					$processing[$val["tag"]] = false;
					$target = false;
				};
			};


			if ($target && "RIDA" == $val["tag"])
			{
				if ("open" == $val["type"])
				{
					$w_open = true;
				}
				elseif ("close" == $val["type"])
				{
					$data[$target][] = $tmp;
					$tmp = array();
					$w_open = false;
				};
			};
			
			if ($target && $w_open && "complete" == $val["type"])
			{
				$tmp[$val["tag"]] = $val["value"];
			};

		};

		$person_list = new object_list(array(
			"parent" => $folder_person,
			"class_id" => CL_CRM_PERSON,
		));
	
		// list of addresses
		$addr_list = new object_list(array(
			"class_id" => CL_CRM_ADDRESS,
		));

		$addr = $addr_list->names();
		// ----------------------------

		// list of existing e-mail addresses
		$email_list = new object_list(array(
			"parent" => $dir_default,
			"class_id" => CL_ML_MEMBER,
		));

		$emails = array();

		foreach($email_list->arr() as $member)
		{
			$emails[] = $member->prop("mail");
		};

		// list of phone numbers
		$phone_list = new object_list(array(
			"class_id" => CL_CRM_PHONE,
			"parent" => $dir_default,
		));

		$phones = array_flip($phone_list->names());
		// -------------------------------

		$person_match = array();
		foreach($person_list->arr() as $person_obj)
		{
			$ext_id = $person_obj->prop("ext_id");
			if ($ext_id)
			{
				$person_match[$ext_id] = $person_obj->id();
			};
		};

		$phone_type = array(
			"TELEFON" => "work",
			"MOBIILTELEFON" => "mobile",
			"LYHINUMBER" => "short",
		);

		$simple_attribs = array(
			"HARIDUSTASE" => array(
				"reltype" => 23, // RELTYPE_EDUCATION
				"prop" => "education",
				"clid" => CL_CRM_PERSON_EDUCATION,
			),
			"AADRESS" => array(
				"reltype" => 1, // RELTYPE_ADDRESS
				"prop" => "address",
				"clid" => CL_CRM_ADDRESS,
			),
			"AMETIKOHT_NIMETUS" => array(
				"reltype" => 7, // RELTYPE_PROFESSION
				"prop" => "rank",
				"clid" => CL_CRM_PROFESSION,
			),
		);

		$simple_data = array();

		foreach($simple_attribs as $key => $sdata)
		{
			$olist = new object_list(array(
				"class_id" => $sdata["clid"],
				"parent" => $dir_default,
			));
			$simple_data[$key] = array_flip($olist->names());

		};

		arr($simple_data);

		print "creating yksused objects<br>";
		/*
			 <yksused>
				  <rida>
				   <yksus_id>1</yksus_id>
				   <ylemyksus_id></ylemyksus_id>
				   <nimetus>Keskkonnaministeerium</nimetus>
				   <pohimaarus_viit></pohimaarus_viit>
				   <aadress>Toompuiestee 24, 15172 Tallinn</aadress>
				  </rida>


		*/

		$seco = new object_list(array(
			"class_id" => CL_CRM_SECTION,
		));

		$sections = array();
		foreach($seco->arr() as $sec_obj)
		{
			$sections[$sec_obj->prop("ext_id")] = $sec_obj->id();
		};

		//arr($data["YKSUSED"]);
		//arr($sections);


		$links = array();
		foreach($data["YKSUSED"] as $yksus)
		{
			//arr($yksus);

			$name = $yksus["NIMETUS"];
			$ext_id = $yksus["YKSUS_ID"];
			$ylem = $yksus["YLEMYKSUS_ID"];


			// aga nüüd vaja kontrollida, et kas sellise ext_id-ga üksus on olemas, kui on, siis
			// uut pole vaja teha
			//$links = $sections[$ylem][] = $sections[$ext_id];

			if (!empty($ylem))
			{
				$links[$ylem][] = $ext_id;
			};

			if (empty($sections[$ext_id]))
			{
				print "created new section<br>";
				$yk = new object();
				$yk->set_parent($dir_default);
				$yk->set_class_id(CL_CRM_SECTION);
				$yk->set_prop("ext_id",$ext_id);
				$yk->set_name($name);
				$yk->save();

				$ykid = $yk->id();
				$sections[$ext_id] = $ykid;
			}
			else
			{
				print "updating existing section";
				$yk = new object($sections[$ext_id]);
				$yk->set_name($name);
				$yk->save();
				print "done<br>";
			};
		}

		// nüüd on vaja leida olemasolevad seosed
		$c = new connection();
		$existing = $c->find(array(
			"from.class_id" => CL_CRM_SECTION,
			"to.class_id" => CL_CRM_SECTION,
			"type" => 1 // RELTYPE_SECTION, ehk alamüksus
		));

		foreach($existing as $conn)
		{
			arr($conn);
		};

		foreach($links as $parent_section => $link_section)
		{
			foreach($link_section as $child_section)
			{
				$o1 = new object($sections[$parent_section]);
				$o2 = new object($sections[$child_section]);
				print "connecting ";
				//print $sections[$parent_section];
				print $o1->name();
				print " to ";
				print $o2->name();
				print "<bR>";
				$o1->connect(array(
					"to" => $o2->id(),
					"reltype" => 1, // RELTYPE_SECTION,
				));
				//print $sections[$child_section];
				print "connect done<br>";
				//arr($o1->properties());
				//arr($o2->properties());
			};
		};	

		print "<h1>sections</h1>";
		arr($sections);
		print "<h1>sections done</h1>";

		$persons = array();

		print "creating person objects<br>";
		foreach($data["TOOTAJAD"] as $worker)
		{
			$ext_id = $worker["TOOTAJA_ID"];
			if (empty($person_match[$ext_id]))
			{
				// create new object
				$person_obj = new object();
				$person_obj->set_parent($folder_person);
				$person_obj->set_class_id(CL_CRM_PERSON);
				print "<br>creating new person object<br>";
			}
			else
			{
				$person_obj = new object($person_match[$ext_id]); 
				unset($person_match[$ext_id]);
				print "updating existing person object";
			};	

			print "SA = " . $worker["SYNNIAEG"];
			$bd_parts = explode(".",$worker["SYNNIAEG"]);
			//$bd_parts = unpack("a2day/a2mon/a4year",$worker["SYNNIAEG"]);
			$bday = mktime(0,0,0,$bd_parts[1],$bd_parts[0],$bd_parts[2]);

			print "tm = " . $bday . "<br>";

			$person_obj->set_name($worker["EESNIMI"] . " " . $worker["PEREKONNANIMI"]);
			$person_obj->set_prop("firstname",$worker["EESNIMI"]);
			$person_obj->set_prop("lastname",$worker["PEREKONNANIMI"]);
			$person_obj->set_prop("ext_id",$ext_id);
			$person_obj->set_prop("birthday",$bday);
			$person_obj->set_status(STAT_ACTIVE);

			if (!in_array($worker["AADRESS"],$addr))
			{
				print "creating address object<br>";
				print $worker["ADDRESS"];
				print "<br>";
				// parent?
			};

			// ametikoht_nimetus
			// eriala - aga see käib vist haridusega kokku?

			if (!empty($worker["YKSUS_ID"]) && $sections[$worker["YKSUS_ID"]])
			{
				print "connectiong to section<br>";
				$person_obj->connect(array(
					"to" => $sections[$worker["YKSUS_ID"]],
					"reltype" => 21, //RELTYPE_SECTION,
				));

				$person_obj->set_prop("org_section",$sections[$worker["YKSUS_ID"]]);
				print "sect connect done<br>";
			};


			if (!empty($worker["E_POST"]) && !in_array($worker["E_POST"],$emails))
			{
				print "creating e-mail object<br>";
				print $worker["E_POST"];
				print "<br>!";

				$ml = new object();
				$ml->set_parent($dir_default);
				$ml->set_class_id(CL_ML_MEMBER);
				$ml->set_name($worker["E_POST"]);
				$ml->set_prop("mail",$worker["E_POST"]);
				$ml->save();

				$mid = $ml->id();

				$person_obj->connect(array(
					"to" => $mid,
					"reltype" => 11,
				));

				$person_obj->set_prop("email",$mid);
			};

			foreach($simple_attribs as $skey => $sdata)
			{
				if (empty($worker[$skey]))
				{
					continue;
				};
				
				if ($simple_data[$skey][$worker[$skey]])
				{
					$tmp_o = new object($simple_data[$skey][$worker[$skey]]);
					print "connecting to $skey object<br>";
				}
				else
				{
					$tmp_o = new object();
					$tmp_o->set_parent($dir_default);
					$tmp_o->set_class_id($sdata["clid"]);
					$tmp_o->set_name($worker[$skey]);
					$tmp_o->set_status(STAT_ACTIVE);
					$tmp_o->save();
					print "creating and connecting to $skey object<br>";
				};
				$tmp_id = $tmp_o->id();
				$simple_data[$skey][$worker[$skey]] = $tmp_id;

				$person_obj->connect(array(
					"to" => $tmp_id,
					"reltype" => $sdata["reltype"],
				));

				$person_obj->set_prop($sdata["prop"],$tmp_id);

				//print "name = " . $tmp_o->name();
			};
		
			foreach($phone_type as $pkey => $pval)
			{
				//if (!empty($worker[$pkey]) && !in_array($worker[$pkey],$phones))
				if (!empty($worker[$pkey]) && !$phones[$worker[$pkey]])
				{
					print "creating $pkey telefon object";
					$this->_create_and_connect_phone(array(
						"person_obj" => $person_obj,
						"folder" => $dir_default,
						"type" => $pval,
						"phone" => $worker[$pkey],
					));
				};
				if ($phones[$worker[$pkey]])
				{
					$po = new object($phones[$worker[$pkey]]);
					$po->set_prop("type",$pval);
					$po->save();
				};
				print "all done<bR>";
			};

			$person_obj->save();

			// let us keep track of all existing workers, so I can properly assign vacations and contract_stops
			$persons[$ext_id] = $person_obj->id();

			arr($worker);
			// mul on vaja seda folderi id, mille alla töötaja objekte teha

		};

		// siia jäävad ainult jäägid, need märgime deaktiivseks ja ongi kõik
		foreach($person_match as $ext_id => $obj_id)
		{
			$person_obj = new object($obj_id);
			$person_obj->set_status(STAT_NOTACTIVE);
			$person_obj->save();
		};

		exit;
	
		/*
		print "<h1>peatumised</h1>";
		arr($data["PEATUMISED"]);
		print "<h1>puhkused</h1>";
		arr($data["PUHKUSED"]);
		*/
		print "<h1>all done</h1>";

		print "teeme peatuste ja puhkuste objektid";

		// lisaks on vaja siin tekitada mingid muutujad. Selleks on vaja muutujate haldurit ..
		// ja seal omakorda on mul tarvis mingid oksad eraldada
		$mx = new object_list(array(
			"parent" => $meta_cat["peatumised"],
			"class_id" => CL_META,
		));

		$mxlist = array_flip($mx->names());
		arr($mxlist);

		// ei, aga põhimõtteliselt, kui töötajal juba on puhkus või peatumine, siis teist me ei tee

		// aga nüüd .. ma tahan person klassile lisada meetodid 
		// add_or_update_vacation
		// add_or_update_contract_stop

		foreach($data["PEATUMISED"] as $peatumine)
		{
			print "pt = ";
			arr($peatumine);
			$a = $this->timestamp_from_xml($peatumine["ALGUS"]);
			$b = $this->timestamp_from_xml($peatumine["LOPP"]);
			$t = new object($persons[$peatumine["TOOTAJA_ID"]]);
			$stop = new object();
			$stop->set_class_id(CL_CRM_CONTRACT_STOP);
			$stop->set_parent($dir_default);
			$stop->set_status(STAT_ACTIVE);
			$stop->set_prop("start1",$a);
			$stop->set_prop("end",$b);
			//$stop->set_name($t->name());
			$stop->set_name($peatumine["LIIK"]);
			$stop->save();

		
			$t->connect(array(
				"to" => $stop->id(),
				"reltype" => 42, //RELTYPE_CONTRACT_STOP,
			));

			arr($PEATUMINE);
			if ($mxlist[$peatumine["LIIK"]])
			{
				$xo = new object($mxlist[$peatumine["LIIK"]]);
				print "using existing peatumine" . $xo->id();
			}
			else
			{
				print "creating new<br>";
				$xo = new object();
				$xo->set_parent($meta_cat["peatumised"]);
				$xo->set_class_id(CL_META);
				$xo->set_status(STAT_ACTIVE);
				$xo->set_name($peatumine["LIIK"]);
				$xo->save();
				$mxlist[$peatumine["LIIK"]] = $xo->id();
			};

			$stop->connect(array(
				"to" => $xo->id(),
				"reltype" => 1,
			));

			print "name = " . $t->name() . " ";
			print "from $a to $b<br>";

			// alright, so far, so good .. I have person object, now I need to create
			// vacation object. and somehow I need to determine whether this person
			// already has an entered vacation.

			// for this I need to check start and end I guess .. and worker_id. really 
			// no other way to do it

			// also, whatever should I do with the variable manager?
		};

		$mx = new object_list(array(
			"parent" => $meta_cat["puhkused"],
			"class_id" => CL_META,
		));

		$mxlist = array_flip($mx->names());
		foreach($data["PUHKUSED"] as $puhkus)
		{
			print "pu = ";
			arr($puhkus);
			$a = $this->timestamp_from_xml($puhkus["ALGUS"]);
			$b = $this->timestamp_from_xml($puhkus["LOPP"]);
			$t = new object($persons[$puhkus["TOOTAJA_ID"]]);
			$stop = new object();
			$stop->set_class_id(CL_CRM_VACATION);
			$stop->set_parent($meta_cat["puhkused"]);
			$stop->set_name($puhkus["PUHKUSE_LIIK"]);
			//$stop->set_name($t->name());
			$stop->set_prop("start1",$a);
			$stop->set_prop("end",$b);
			$stop->set_status(STAT_ACTIVE);
			$stop->save();

			$t->connect(array(
				"to" => $stop->id(),
				"reltype" => 41, //RELTYPE_VACATION,
			));
			
			if ($mxlist[$puhkus["PUHKUSE_LIIK"]])
			{
				$xo = new object($mxlist[$puhkus["PUHKUSE_LIIK"]]);
			}
			else
			{
				$xo = new object();
				$xo->set_parent($meta_cat["puhkused"]);
				$xo->set_class_id(CL_META);
				$xo->set_status(STAT_ACTIVE);
				$xo->set_name($puhkus["PUHKUSE_LIIK"]);
				$xo->save();
				$mxlist[$puhkus["PUHKUSE_LIIK"]] = $xo->id();
			};

			$stop->connect(array(
				"to" => $xo->id(),
				"reltype" => 1,
			));
			print "name = " . $t->name() . " ";
			print "from $a to $b<br>";
		};

		print "finished<br>";

		// puhkusi ning ka peatumisi näidatakse eraldi tabelis
		// ainus asi mis neid eristab on töötaja ID. sama peatumise kohta. Therefore I have no way in hell
		// of deleting old vacations .. it simply is not going to happen

		/* puhkus
		<rida>
			<tootaja_id>190</tootaja_id>
			<puhkuse_liik>põhipuhkus</puhkuse_liik>
			<algus>20041222T00:00:00</algus>
			<lopp>20041229T00:00:00</lopp>
			<kestus>6</kestus>
		</rida>
		*/

		/* peatumine
			<rida>
				<tootaja_id>67</tootaja_id> 
				<liik>lapsehoolduspuhkus</liik>
				<algus>20010806T00:00:00</algus>
				<lopp>20060906T00:00:00</lopp>
			</rida>

			// nuu, ma arvan et kasutan siinkohal subclassi ära owneriks
		*/


		print "all done";

		//arr($data);

		/*
			<taitmata_ametikohad>
			<rida>
			   <nimetus>looduskaitse peaspetsialist</nimetus>
			   <kood>2470</kood>
			   <yksus_id>6</yksus_id>
			   <prioriteet></prioriteet>
			  </rida>
			</taitmata_ametikohad>

			<yksused>
			  <rida>
			   <yksus_id>1</yksus_id>
			   <ylemyksus_id></ylemyksus_id>
			   <nimetus>Keskkonnaministeerium</nimetus>
			   <pohimaarus_viit></pohimaarus_viit>
			   <aadress>Toompuiestee 24, 15172 Tallinn</aadress>
			  </rida>

			<puhkused>
			  <rida>
			   <tootaja_id>190</tootaja_id>
			   <puhkuse_liik>põhipuhkus</puhkuse_liik>
			   <algus>20041222T00:00:00</algus>
			   <lopp>20041229T00:00:00</lopp>
			   <kestus>6</kestus>
			  </rida>

			<peatumised>
			  <rida>
			   <tootaja_id>67</tootaja_id>
			   <liik>lapsehoolduspuhkus</liik>
			   <algus>20010806T00:00:00</algus>
			   <lopp>20060906T00:00:00</lopp>
			  </rida>


		


		*/

		// nii .. edasi on vaja siis hakata neid bloody isiku objekte tegema
		// ja puhkuste objekte
		// ja tööpepingute peatamise objekte

		// nii, nüüd vaja siis parser üles ehitada lihtsalt
		print "<pre>";
		print htmlspecialchars($fdat);
		print "</pre>";

		//$c->disconnect();

	//	print "tadaaa";


		//arr($conn_obj->properties());
		arr($obj->properties());


	}

	/**
		@attrib name=import_images
		@param id required type=int
	**/
	function import_images($arr)
	{
		$obj = new object($arr["id"]);
		
		$config = $this->get_config($arr);

		if (sizeof($config["ftp"]) == 0)
		{
			die("You forgot to enter server data");
		};

		$c = get_instance(CL_FTP_LOGIN);


		$config["ftp"]["host"] = "ftp.envir.ee";

		$c->connect($config["ftp"]);

		$p = xml_parser_create();

		$fqdn = $obj->prop("xml_folder") . "/";
		$files = $c->dir_list($fqdn);

		$persons = new object_list(array(
			"class_id" => CL_CRM_PERSON,
			"status" => STAT_ACTIVE,
		));	
		$px = array();
		foreach($persons->arr() as $person_obj)
		{
			$px[$person_obj->prop("ext_id")] = $person_obj->id();
		};

		$rpx = array_flip($px);

		// isiku puhul on tegemist lihtsalt esimese vastavat tüüpi seosega pildiobjektiga

		$cx = new connection();
		$existing = $cx->find(array(
			"from.class_id" => CL_CRM_PERSON,
			"to.class_id" => CL_IMAGE,
			"type" => 3 // RELTYPE_PICTURE, ehk pilt
		));

		$existing_images = array();
		foreach($existing as $conn)
		{
			//$existing_images[$conn["from"]] = $conn["to"];
			$existing_images[$rpx[$conn["from"]]] = $conn["to"];
		};

		//arr($px);

		$ti = get_instance(CL_IMAGE);
		$base_len = strlen(aw_ini_get("baseurl"));



		foreach($files as $file)
		{
			// XXX: implement proper detection of image files
			if (strpos($file,"intranet_pilt"))
			{
				print "retrieving and parsing $file<br>";
				$fdat = $c->get_file($file);
				$p = xml_parser_create();
				xml_parse_into_struct($p, $fdat, $vals, $index);
				xml_parser_free($p);
				$tootaja_id = false;
				foreach($vals as $tag)
				{
					if ($tootaja_id && $tag["tag"] == "PILT" && $tag["type"] == "complete")
					{
						$pilt_data = base64_decode($tag["value"]);
						print "assigning " . strlen($pilt_data) . " bytes to $tootaja_id<br>";
						print "ix = ";
						//print $existing_images[$tootaja_id];
						$t = new object($px[$tootaja_id]);
						if (is_oid($config["image_folder"]))
						{
							$image_folder = $config["image_folder"];
						}
						else
						{
							$image_folder = $t->id();
						};
						$pilt_name = $t->name() . ".jpg";
						if (empty($existing_images[$tootaja_id]))
						{
							print "creating image<br>";

							$emb = array();
							$emb["group"] = "general";
							$emb["parent"] = $image_folder;
							$emb["return"] = "id";
							$emb["cb_existing_props_only"] = 1;
							$emb["file"] = array(
								"name" => $pilt_name,
								"contents" => $pilt_data,
								"type" => "image/jpg",
							);

							$timg = $ti->submit($emb);

							$t->connect(array(
								"to" => $timg,
								"reltype" => 3,
							));

							$t->set_prop("picture",$timg);
							$t->save();
						}
						else
						{
							// change the parent
							$img_o = new object($existing_images[$tootaja_id]);
							if ($img_o->parent() != $image_folder)
							{
								print "changing parent<br>";
								$img_o->set_parent($image_folder);
								$img_o->save();
							};
							if ($t->prop("picture") != $img_o->id())
							{
								$t->set_prop("picture",$img_o->id());
								print "setting pic property<br>";
								$t->save();
							};
							$url = substr($ti->get_url($img_o->prop("file")),$baselen);
							print "url = $url<br>";

							print "updating parent of ";
							arr($img_o->properties());
							print "<bR>";
							print "imf = " . $config["image_folder"];
							print "<br>";
						}
						

						// n
						print $t->name();
						print "<br>";
						$tootaja_id = false;
					};

					if ($tag["tag"] == "TOOTAJA_ID" && $tag["type"] == "complete")
					{
						$tootaja_id = $tag["value"];
					};
				};
			};
		};

		$c->disconnect();

	}

	function timestamp_from_xml($xml_stamp)
	{
		// 20060906T00:00:00
		$p = unpack("a4year/a2mon/a2day/c1e/a2hour/a2min/a2sec",$xml_stamp);
		return mktime($p["hour"],$p["min"],$p["sec"],$p["mon"],$p["day"],$p["year"]);
	}

	function _create_and_connect_phone($arr)
	{
		$ml = new object();
		$ml->set_parent($arr["folder"]);
		$ml->set_class_id(CL_CRM_PHONE);
		$ml->set_name($arr["phone"]);
		$ml->set_prop("type",$arr["type"]);
		$ml->save();

		$mid = $ml->id();

		$arr["person_obj"]->connect(array(
			"to" => $mid,
			"reltype" => 13,
		));

		if (!is_oid($arr["person_obj"]->prop("phone")))
		{
			$arr["person_obj"]->set_prop("phone",$mid);
		};

	}

	function _create_and_connect_attrib($arr)
	{



	}

	function callback_post_save($arr)
	{
		$o = $arr["obj_inst"];
		$conns = $o->connections_from(array(
			"type" => "RELTYPE_RECURRENCE",
		));
		// iga asja kohta on vaja teada seda, et millal ta välja kutsutakse
		$sch = get_instance("scheduler");
		foreach($conns as $conn)
		{
			$rep_id = $conn->prop("to");
			$event_url = $this->mk_my_orb("invoke_import",array("id" => $o->id()));
			$sch->add(array(
			 	"event" => $event_url,
				"rep_id" => $rep_id,
			));
		};
	}

}
?>
