<?php


	// triggerite mask
	define("T_ENTRY",1);
	define("T_INLIST",2);
	define("T_MAILSENT",4);
	define("T_MAILSENTAT",8);
	define("T_MAILSUBJ",16);
	define("T_USEDVARS",32);
	
	// äktsionite mask
	define("A_ADDLIST",1);
	define("A_DELLIST",2);
	define("A_DELETE",3);
	define("A_DONTSEND",4);

// ruulid
class ml_rule extends aw_template
{

	////
	//! Konstruktor
	function ml_rule()
	{
		$this->init("automatweb/mlist");
		$this->dbconf=get_instance("config");
		$this->formid=$this->dbconf->get_simple_config("ml_form");
		$this->searchformid=$this->dbconf->get_simple_config("ml_search_form");
		lc_load("definition");
	}

	////
	//! Näitab ruuli muutmise lehte
	function orb_change($arr)
	{
		is_array($arr)? extract($arr) : $parent=$arr;

		$this->mk_path($parent,"Muuda ruuli");
		$this->read_template("rule_change.tpl");

		if ($id)
		{
			$name=$this->db_fetch_field("SELECT name FROM objects WHERE oid = '$id'","name");
			$this->db_query("SELECT * FROM ml_rules WHERE rid='$id'");
			$r=$this->db_next();
		} 
		else
		{
			$r=array();
		};
		
		if (!isset($this->ml))
		{
			$this->ml = get_instance("ml_list");
		};
		load_vcl("date_edit");
		$date_edit = new date_edit(time());
		$date_edit->configure(array(
			"day" => "",
			"month" => "",
			"year" => "",
			"hour" => "",
			"minute" => "",
			"classid" => "small_button",
		));

		if (!isset($this->fr))
		{
			$this->fr=get_instance("formgen/form");
		};
		$farr=array(
			"id" => $this->searchformid,//searchformid
			"tpl" => "show_onlyels.tpl",
			"formtag_name" => "foo",
			"prefix" => "__formdta"
		);
		
		if (($r["trig"] & T_ENTRY) && $r["trig_entry"])
		{
			$farr["entry_id"]=$r["trig_entry"];
		};

		$fparse=$this->fr->gen_preview($farr);
		//echo("<textarea>$fparse</textarea>");
		$fparse=preg_replace("/<input(.+?)type='submit'(.+?)>/i","",$fparse);
		$fparse=preg_replace("/<input(.+?)name='id'(.+?)>/i","",$fparse);
		//echo("<textarea>$fparse</textarea>");
		
		
		$alllists=$this->ml->get_lists_and_groups(array("spacer" => "&nbsp;","fullnames"=> 1));
		$allvars=$this->ml->get_all_varnames();
		$trig_usedvars=unserialize($r["trig_usedvars"]);
		$t_usedvars=array_flip(is_array($trig_usedvars)?$trig_usedvars:array());


		if (!$r["action"])
		{
			$r["action"]=1;
		};
		// kas on otsitud??
		if ($search)
		{
			$matches=$this->match_rules(array($id));
			if (is_array($matches[$id]))
			{
				foreach($matches[$id] as $mid)
				{
					//echo($mid."<br>");
					$this->vars(array(
						"id"=>$mid,
						"name"=>$this->db_fetch_field("SELECT name FROM objects WHERE oid ='$mid'","name"),
					));
					$rida.=$this->parse("rida");
				};
				$taida=$this->parse("taida");
			};
		};

		$this->vars(array(
			"taida" => $taida,
			"rida" => $rida,
			"name" => $name,
			"t_mailsentat" => $date_edit->gen_edit_form("t_mailsentat",$r["trig_mailsentat"]),
			"t_mailsentat2" => $date_edit->gen_edit_form("t_mailsentat2",$r["trig_mailsentat2"]),
			"t_inlist" => $this->picker($r["trig_inlist"],$alllists),
			"t_mailsubj" => $r["trig_mailsubj"],
			"t_mailsent" => $r["trig_mailsent"],
			"addlist" => $this->picker(($r["action"] == A_ADDLIST)? $r["actionid"]:0,$alllists),
			"dellist" => $this->picker(($r["action"] == A_DELLIST)? $r["actionid"]:0,$alllists),
			"dontsend" => ($r["action"] == A_DONTSEND) ? $r["actionid"] : 0,
			"t_usedvars" => $this->multiple_option_list($t_usedvars,$allvars),
			"formparse" => $fparse,
			"tm_inlist" => checked($r["trig"] & T_INLIST),
			"tm_mailsent" => checked($r["trig"] & T_MAILSENT),
			"tm_mailsubj" => checked($r["trig"] & T_MAILSUBJ),
			"tm_mailsentat" => checked($r["trig"] & T_MAILSENTAT),
			"tm_usedvars" => checked($r["trig"] & T_USEDVARS),
			"tm_entry" => checked($r["trig"] & T_ENTRY),
			"trig_not_0" => checked(!$r["trig_not"]),
			"trig_not_1" => checked($r["trig_not"]),
			"dynamic" => checked($r["dynamic"]),
			"a_dontsend" => checked($r["action"] == A_DONTSEND),
			"a_addlist" => checked($r["action"] == A_ADDLIST),
			"a_dellist" => checked($r["action"] == A_DELLIST),
			"a_delete" => checked($r["action"] == A_DELETE),
			"l_vali" => $this->mk_my_orb("select_mail",array()),
			"reforb" => $this->mk_reforb("submit_change",array(
				"parent" => $parent, 
				"id" => $id,
				"subaction" => " ",
				"oldentry_id" => $r["trig_entry"])),
			));
		
		return $this->parse();
	}

	////
	//! Händleb täida nuppu ja executib ruuli $id memberite $mids peal
	function do_execute($arr)
	{
		extract($arr);
		$this->execute_rules(array($id => $mids));
	}

	////
	//! Näitab meili valimise popup akent
	function orb_select_mail($arr)
	{
		extract($arr);
		$this->read_template("select_mail.tpl");

		$this->db_query("SELECT * FROM messages WHERE messages.type='3'");//3 on konstant MSG_LIST failist messenger.aw
		while ($r = $this->db_next() )
		{
			$r["message"]=substr($r["message"],0,150)."...";
			$this->vars($r);
			$rida.=$this->parse("rida");
		};

		$this->vars(array(
			"el"=> $el,
			"rida" => $rida
		));

		return $this->parse();
	}

	////
	//! Händleb ruuli muutmise sumbitti
	function orb_submit_change($arr)
	{
		//echo("<pre>arr=");print_r($arr);echo("</pre>");//dbg
		extract($arr);

		if (!$id)
		{
			//echo("teen uue <br>");//dbg
			$id=$this->new_object(array(
				"class_id" => CL_ML_RULE,
				"name" => $name, 
				"parent" => $parent
				));
			$this->db_query("INSERT INTO ml_rules (rid) VALUES ('$id')");
			//echo("id=$id<br>");//dbg
		};

		$this->db_query("UPDATE objects SET name='$name' WHERE oid = '$id'");

		$trig=0;
		$tm_inlist?$trig|=T_INLIST:"";
		$tm_mailsent?$trig|=T_MAILSENT:"";
		$tm_mailsubj?$trig|=T_MAILSUBJ:"";
		$tm_mailsentat?$trig|=T_MAILSENTAT:"";
		$tm_usedvars?$trig|=T_USEDVARS:"";
		$tm_entry?$trig|=T_ENTRY:"";

		// salvesta kasutaja mätsimise form
		unset($t_entry);
		if ($tm_entry)
		{
			//echo("need to save entry data");//dbg
			if (!isset($this->fr))
			{
				$this->fr=get_instance("formgen/form");
			};
		
			$this->fr->process_entry(array(
				"values" => $arr,		//Kas form_element.aw ikka võtab siit inffi?? :)
				"entry_id" => $oldentry_id,
				"prefix" => "__formdta",
				"parent" => $id,
				"id" => $this->searchformid,// peax olema searchformid
			));
			$t_entry = $this->fr->entry_id?$this->fr->entry_id:$oldentry_id;
			$t_entry = " trig_entry='$t_entry', ";
			//echo("process_entry done t_entry=$t_entry");
		};
		
		$t_mailsentat=mktime($t_mailsentat["hour"],$t_mailsentat["minute"],0,$t_mailsentat["month"],$t_mailsentat["day"],$t_mailsentat["year"]);
		$t_mailsentat2=mktime($t_mailsentat2["hour"],$t_mailsentat2["minute"],0,$t_mailsentat2["month"],$t_mailsentat2["day"],$t_mailsentat2["year"]);
		//echo("<pre>t_usedvars=");print_r($t_usedvars);echo("</pre>");//dbg
		
		$t_usedvars=serialize(is_array($t_usedvars)?$t_usedvars:array());
		$dynamic=$dynamic?1:0;

		switch($actionx)
		{
			case "addlist":
				$action=A_ADDLIST;
				$actionid=$addlist;
				break;

			case "dellist":
				$action=A_DELLIST;
				$actionid=$dellist;
				break;

			case "dontsend":
				$action=A_DONTSEND;
				$actionid=$dontsend;
				break;
			
			case "delete":
				$action=A_DELETE;
				$actionid=0;
				break;

			default:
				die("kuidas on võimalik, et ükski actioni radiobuttonitest polnud valitud??");
		};
		
		$q="UPDATE ml_rules SET dynamic='$dynamic', trig='$trig', trig_inlist='$t_inlist', 
			trig_mailsent='$t_mailsent', trig_mailsentat='$t_mailsentat', trig_mailsentat2='$t_mailsentat2',
			trig_mailsubj='$t_mailsubj', trig_usedvars='$t_usedvars', trig_not = '$trig_not', $t_entry action='$action',
			actionid='$actionid' WHERE rid='$id'";
		//echo("query==<b>$q</b><br>");//dbg
		$this->db_query($q);

		// Votnii, siin nüüd vaadatakse, et kas vajutati otsi või täida nuppe
		//echo("lõpp");//dbg
		if ($subaction=="search")
		{
			return $this->mk_my_orb("change",array("id" => $id,"parent" => $parent, "search" => 1,"_"=>"_#searcha"));
		} 
		else
		if ($subaction=="execute")
		{
			$this->do_execute($arr);
			return $this->mk_my_orb("change",array("id" => $id,"parent" => $parent));
		} 
		else
		{
			// kui on dynaamiline, siis execute
			if ($dynamic)
			{
				$selrules=array($id);
				$selrules["return_infoz"]=1;
				$matches=$this->match_rules($selrules);
				$ruledta=$matches["rarr"];
				unset($matches["rarr"]);
				$this->execute_rules($matches,$ruledta);
			};

			$this->_log("mlist","muutis ruuli $name");
			return $this->mk_my_orb("change",array("id" => $id,"parent" => $parent));
		};
	}

	////
	// !Tagastab teatud tüüpi ruulide array
	// "type" => "inlist" | "mailsent" | "mailsentat" | "mailsubj" | "usedvars" | "entry"
	// "dta" vastavad andmed
	function select_rules($arr=array())
	{
		extract($arr);
		unset($w);

		switch($type)
		{
			case "inlist":
				list($dtam,$lgid)=explode(":",$dta);
				$w="(trig & ".T_INLIST." > 0) AND trig_inlist='$dta' OR trig_inlist='$dtam:0'";
				break;
			case "mailsent":
				$w="(trig & ".T_MAILSENT." >0) AND trig_mailsent='$dta'";
				break;
			case "mailsentat":
				$w="(trig & ".T_MAILSENTAT." >0) AND trig_mailsentat <= '$dta' AND trig_mailsentat2 >= '$dta'";
				break;
			case "mailsubj":
				$w="(trig & ".T_MAILSUBJ." >0) AND '$dta' LIKE trig_mailsubj";// ei tea kas nii saab teha ?? OH saabki, äge!
				break;
			case "usedvars":
				$w="(trig & ".T_USEDVARS." >0)";//siia küll ei oska midagi välja mõelda. liiga pikk query tulex et miskit edu saada
				// kõik ,, vahel ruulis olevad nimed peavad olema $dta -s olemas
				break;
			case "entry":
				$w="(trig & ".T_ENTRY." >0)";
				break;

			default:
				$this->_log("mlist","error: select_rules type on määramata");
				return;
		}

		$q="SELECT rid FROM ml_rules WHERE $w AND dynamic='1'";
		$this->db_query($q);
		$ret=array();
		while ($r = $this->db_next())
		{
			$ret[]=$r["rid"];
		};
		return $ret;

	}

	////
	//! Annab ruulidega $arr mätsivate memberite id-d
	// tagastab array rid=>array(1,2,3),rid=>array(4,5,6) , $arr on ruulid, mida mätsida
	// $arr[return_infoz] näitab kas tagastada ruulide info 
	// members on array liikmetest mida mätsida (opt)
	function match_rules($arr,$members=0)
	{
		if (!is_array($arr))
		{
			$this->_log("mlist","error: match_rules arr ei ole array");
			return;
		};

		$matches=array();
		if (is_array($members))
		{
			$oidinclause="objects.oid IN (".join(",",$members).")";
		} 
		else
		{
			unset($oidinclause);
		};

		if ($arr["return_infoz"])
		{
			$return_infoz=1;
			$rarr=array();
			unset($arr["return_infoz"]);
		};

		foreach ($arr as $id)
		{
			$this->db_query("SELECT * FROM ml_rules WHERE rid = '$id'");
			$r=$this->db_next();
			//ehita query
			$wi=array();//where osad
			$w=&$wi;

			if ($oidinclause)
			{
				$w[]=$oidinclause;
			};

			$t=array();//from tabelid

			if (($r["trig"] & T_ENTRY) && $r["trig_entry"])
			{
				//echo("doing form::search() entry=".$r["trig_entry"]);//dbg
				if (!isset($this->fr))
				{
					$this->fr=get_instance("formgen/form");
				};
				$this->fr->load($this->searchformid);
				$matchedids=$this->fr->search($r["trig_entry"]);
				// Ohh, lahe, minumeelest oli kuskil kirjas, et form::search tagastab
				// array kujul formid => array(match,match), jne
				// Aga oh imet! ära muudetud

				//echo("<pre>");print_r($matchedids);echo("</pre>");//dbg
				
				if (is_array($matchedids) && sizeof($matchedids))
				{
					$w[]="objects.oid IN (".join(",",$matchedids/*[$this->searchformid]*/).")";
				} 
				else
				{
					$w[]="0";//ei mätsi
				};
			};

			if ($r["trig"] & T_INLIST)
			{
				$t["ml_list2member"]=1;
				list($lid,$gid)=explode(":",$r["trig_inlist"]);
				if ($gid==0)
				{
					$w[]="ml_list2member.lid = '$lid'";
				} else
				{
					$w[]="ml_list2member.lid = '$lid' AND ml_list2member.lgroup = '$gid'";
				};
			};

			if ($r["trig"] & T_MAILSENT)
			{
				$t["ml_sent_mails"]=1;
				$w[]="ml_sent_mails.mail = '".$r["trig_mailsent"]."'";
			};

			if ($r["trig"] & T_MAILSENTAT)
			{
				$t["ml_sent_mails"]=1;
				$w[]="ml_sent_mails.tm >= '".$r["trig_mailsentat"]."' AND ml_sent_mails.tm <= '".$r["trig_mailsentat2"]."'";
			};

			if ($r["trig"] & T_MAILSUBJ)
			{
				$t["ml_sent_mails"]=1;
				// protsendid võib siit ära võtta kui kasutajad nad ruuli edimise juures sisste trükix
				$w[]="ml_sent_mails.subject LIKE '%".$r["trig_mailsubj"]."%'";
			};

			if ($r["trig"] & T_USEDVARS)
			{
				$t["ml_sent_mails"]=1;
				if (!isset($this->ml))
				{
					$this->ml=get_instance("ml_list");
				};
				$vn=$this->ml->get_all_varnames();
				$vars=unserialize($r["trig_usedvars"]);
				foreach($vars as $vid)
				{
					$w[]="ml_sent_mails.vars LIKE '%,".$vn[$vid].",%'";
				};
			};

			$tables=array("objects");
			$wi[]="objects.class_id = '".CL_ML_MEMBER."'";
			$wi[]="objects.status != '0'";

			if ($t["ml_sent_mails"])
			{
				$tables[]="ml_sent_mails";
				$wi[]="objects.oid = ml_sent_mails.member";
			};

			if ($t["ml_list2member"])
			{
				$tables[]="ml_list2member";
				$wi[]="objects.oid = ml_list2member.mid";
			};

			//debug
			//echo("<b>rule=$id</b><br>tables=<pre>");print_r($tables);echo("</pre>where=<pre>");print_r($w);echo("</pre><br>");//dbg

			

			// pane see junn kokku
			$wis=sizeof($wi)>0 ? "(".join(" AND ",$wi).")":"";
			//$wns=sizeof($wn)>0 ? (($wis? "AND":"")." NOT (".join(" AND ",$wn).")" ):"";

			
			$q="SELECT DISTINCT(objects.oid) AS oid FROM ".join(",",$tables)." WHERE $wis ";
			//echo("q=$q<br>");//dbg
			$this->db_query($q);

			while($match = $this->db_next())
			{
				//echo("got ".$match["oid"]."<br>");//dbg
				$matches[$id][]=$match["oid"];
			};
			//echo("<b>$idgot=</b><pre>");print_r($matches);echo("</pre>");//dbg
			
			if ($return_infoz && is_array($matches[$id]))
			{
				$rarr[$id]=$r;
			};
		};//of foreach ($arr as $id)

		if ($return_infoz)
		{
			$matches["rarr"]=$rarr;
		};
		//debug
		//echo("matchez=<pre>");print_r($matches);echo("</pre>");//dbg

		if (!is_array($matches))
		{
			$matches=array();
		};
		return $matches;
	}// of method


	////
	//! täidab ruulid. arr on array rid => array(mid,mid,mid),rid=>array(mid,mid) 
	// $rarr on ruulide info kui on olemas juba
	function execute_rules($arr,$rarr=0)
	{
		if (!is_array($arr))
		{
			$this->_log("mlist","error: execute_rules arr ei ole array");
			return;
		};

		if (is_array($rarr))
		{
			$got_infoz=1;
			//echo("got_infoz=1<br>");//dbg
		} 
		else
		{
			$rarr=array();
		};

		foreach($arr as $rid => $midarr)
		{
			if (!$got_infoz || !is_array($rarr[$rid]))
			{
				$this->db_query("SELECT * FROM ml_rules WHERE rid = '$rid'");
				$rarr[$rid] = $this->db_next();
			};

			$r=$rarr[$rid];

			foreach($midarr as $mid)
			{
				//echo("<b>executing  * rule $rid on $mid:</b>".$r["action"]."<br>");//dbg
				switch ($r["action"])
				{
					case A_ADDLIST:
						if (!isset($this->ml))
						{
							$this->ml=get_instance("ml_list");
						};
						list($lid,$lgroup)=explode(":",$r["actionid"]);
						$lid=(int)$lid;
						$lgroup=(int)$lgroup;// et määramata lgroup muutux nullix
						$this->ml->add_member_to_list(array("mid" => $mid,"lid" => $lid,"grp" => $lgroup, "__norules" => 1));
						//echo("A_ADDLIST $mid $lid $lgroup<br>");//dbg
						break;

					case A_DELLIST:
						
						if (!isset($this->ml))
						{
							$this->ml=get_instance("ml_list");
						};
						list($lid,$lgroup)=explode(":",$r["actionid"]);
						$lid=(int)$lid;
						$this->ml->remove_member_from_list(array("mid" => $mid,"lid" => $lid,"__norules" => 1));
						//echo("A_DELLIST $mid $lid<br>");//dbg
						break;

					case A_DELETE:
						if (!isset($this->mlmember))
						{
							classload("ml_member");
							$this->mlmember=get_instance("ml_member");
						};
						$this->mlmember->orb_delete(array("id" => $mid,"_inner_call" => 1));
						//echo("A_DELETE $mid<br>");//dbg
						break;

					case A_DONTSEND:
						//echo("A_DONTSEND $mid ".$r["actionid"]."<br><pre>");//dbg
						$avoidmessages=$this->get_object_metadata(array("oid" => $mid,"key" => "avoidmessages"));
						//print_r($avoidmessages);//dbg
						if (!is_Array($avoidmessages))
						{
							$avoidmessages=array();
						};
						$avoidmessages[$r["actionid"]]=1;
						//echo("after");print_r($avoidmessages);echo("<pre>");//dbg
						$this->set_object_metadata(array("oid" => $mid,"key" => "avoidmessages", "value" => $avoidmessages));
						
						break;


					default:
						$this->_log("mlist","error: WTF?? execute_rules on mid=$mid rule rid=$rid has no action!");
						break;
				};// of switch
			};// of foreach($midarr as $mid)
		};//of foreach($arr as $rid => $midarr)

	}// of method

	////
	//! Seda tuleb kutsuda liikmete listidevahelisel liigutamisel
	function check_inlist($lidgid,$members)
	{
		$selrules=$this->select_rules(array("type" => "inlist", "dta" => $lidgid));
		$selrules["return_infoz"]=1;
		$matches=$this->match_rules($selrules,$members);
		$ruledta=$matches["rarr"];
		unset($matches["rarr"]);
		$this->execute_rules($matches,$ruledta);
	}

	////
	//! Seda tuleb kutsuda liikmete andmete muutmisel
	function check_entry($members)
	{
		$selrules=$this->select_rules(array("type" => "entry"));
		$selrules["return_infoz"]=1;
		$matches=$this->match_rules($selrules,$members);
		$ruledta=$matches["rarr"];
		unset($matches["rarr"]);
		$this->execute_rules($matches,$ruledta);
	}

	////
	//! Seda tuleb kutuda peale IGA meili saatmist, sest pärast pole enam kasutada
	// optimiseerimisex vaja olevat infii nagu mail id, member, subject jne..
	function check_mailsent($m,$members)
	{
		// "type" => "inlist" | "mailsent" | "mailsentat" | "mailsubj" | "usedvars"
		$selrules=array();
		$srules=$this->select_rules(array("type" => "mailsent", "dta" => $m["mid"]));
		foreach($srules as $rid)
		{
			$selrules[$rid]=1;
		};
		//echo("mailsent<pre>");print_r($srules);echo("<pre><br>");//dbg
		
		$srules=$this->select_rules(array("type" => "mailsentat", "dta" => time())); // väike lag
		foreach($srules as $rid)
		{
			$selrules[$rid]=1;
		};
		//echo("mailsentat<pre>");print_r($srules);echo("<pre><br>");//dbg

		$srules=$this->select_rules(array("type" => "mailsubj", "dta" => $m["subject"]));
		foreach($srules as $rid)
		{
			$selrules[$rid]=1;
		};
		//echo("mailsubj<pre>");print_r($srules);echo("<pre><br>");//dbg

		$srules=$this->select_rules(array("type" => "usedvars", "dta" => $m["vars"]));
		foreach($srules as $rid)
		{
			$selrules[$rid]=1;
		};
		//echo("usedvars<pre>");print_r($srules);echo("<pre><br>");//dbg

		$selrules=array_keys($selrules);//pööra ümber
		//echo("SELRULES=<pre>");print_r($selrules);echo("<pre><br>");//dbg


		$selrules["return_infoz"]=1;
		
		$matches=$this->match_rules($selrules,$members);
		$ruledta=$matches["rarr"];
		unset($matches["rarr"]);

		//echo("matches:<pre>");print_r($matches);echo("<pre><br>");//dbg
		//echo("check out return_infoz: rarr=<pre>");print_r($ruledta);echo("<pre><br>");//dbg
		$this->execute_rules($matches,$ruledta);
	}
}

?>
