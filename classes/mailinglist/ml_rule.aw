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
		lc_load("definition");
	}

	////
	//! Näitab ruuli muutmise lehte
	function orb_change($arr)
	{
		extract($arr);
		$this->read_template("rule_change.tpl");

		if ($id)
		{
			$ob = $this->get_object($id);
			$name = $ob["name"];
			$r = $this->db_fetch_row("SELECT * FROM ml_rules WHERE rid='$id'");
			$parent = $ob["parent"];
		} 
		else
		{
			$r=array();
		};
		$this->mk_path($parent,"Muuda ruuli");
		
		$this->ml = get_instance("mailinglist/ml_list");
		$this->fr = get_instance("formgen/form");
		$this->conf = get_instance("mailinglist/ml_list_conf");

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

		$farr=array(
			"id" => $this->conf->get_user_search_form($ob["meta"]["conf"]),//searchformid
			"tpl" => "show_onlyels.tpl",
			"formtag_name" => "foo",
			"prefix" => "__formdta"
		);
		
		if (($r["trig"] & T_ENTRY) && $r["trig_entry"])
		{
			$farr["entry_id"] = $r["trig_entry"];
		};

		$fparse=$this->fr->gen_preview($farr);
		$fparse=preg_replace("/<input(.+?)type='submit'(.+?)>/i","",$fparse);
		$fparse=preg_replace("/<input(.+?)name='id'(.+?)>/i","",$fparse);
		
		$alllists=$this->ml->get_lists_and_groups(array("spacer" => "&nbsp;","fullnames"=> 1));
		// we gots to give it the conf object to load the vars from 
		$allvars=$this->ml->get_all_varnames(false, $ob["meta"]["conf"]);
		$trig_usedvars = unserialize($r["trig_usedvars"]);
		$t_usedvars = array_flip(is_array($trig_usedvars)?$trig_usedvars:array());


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

		$tb = get_instance("toolbar");
		$tb->add_button(array(
			"name" => "save",
			"tooltip" => "Salvesta",
			"url" => "javascript:document.foo.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif"
		));
		$tb->add_button(array(
			"name" => "search",
			"tooltip" => "Otsi",
			"url" => "javascript:DoTheThing('search')",
			"imgover" => "search_over.gif",
			"img" => "search.gif"
		));

		$this->vars(array(
			"toolbar" => $tb->get_toolbar(),
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
			"conf" => $this->picker($ob["meta"]["conf"], $this->list_objects(array("class" => CL_ML_LIST_CONF))),
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

		classload("messenger");
		$this->db_query("SELECT * FROM messages WHERE messages.type='".MSG_LIST."'");
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
			$id = $this->new_object(array(
				"class_id" => CL_ML_RULE,
				"name" => $name, 
				"parent" => $parent
			));
			$this->db_query("INSERT INTO ml_rules (rid) VALUES ('$id')");
			//echo("id=$id<br>");//dbg
		};

		$this->upd_object(array(
			"oid" => $id,
			"name" => $name,
			"metadata" => array(
				"conf" => $conf
			)
		));

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
			$this->fr = get_instance("formgen/form");
			$this->conf = get_instance("mailinglist/ml_list_conf");
		
			$this->fr->process_entry(array(
				"values" => $arr,		//Kas form_element.aw ikka võtab siit inffi?? :)
				"entry_id" => $oldentry_id,
				"prefix" => "__formdta",
				"parent" => $id,
				"id" => $this->conf->get_user_search_form($conf),// peax olema searchformid
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

//		echo "match rules! <br>";
		$matches=array();
		if (is_array($members))
		{
			$memstr = join(",",$members);
			if ($memstr != "")
			{
				$oidinclause="objects.oid IN (".$memstr.")";
			}
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
			$r = $this->db_fetch_row("SELECT * FROM ml_rules WHERE rid = '$id'");
			$r_ob = $this->get_object($id);
			if (!$r_ob["meta"]["conf"])
			{
				continue;	// handle old rules. handle them by ignoring them is what I mean.
			}

//			echo "<b> matching rule $id!</b> <br>";
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
//				echo "match T_ENTRY <br>";
				$_tmp = $this->do_entry_trigger(array(
					"rule" => $id
				));

				$tmpstr = join(",",$_tmp);
				if ($tmpstr != "")
				{
//				echo "entries not members = $tmpstr <br>";
					$w[]="objects.oid IN (".$tmpstr.")";
				} 
				else
				{
					$w[]="0";//ei mätsi
				};
			};

			if ($r["trig"] & T_INLIST)
			{
//				echo "match T_INLIST <br>";
				list($lid,$gid)=explode(":",$r["trig_inlist"]);
				if ($gid==0)
				{
					$list_inst = get_instance("mailinglist/ml_list");
					$folders = array_keys($list_inst->get_all_folders_for_list($lid));
					$foldersstr = join(",", $folders);
					if ($foldersstr != "")
					{
						$w[]="objects.parent IN ($foldersstr)";
					}
				} 
				else
				{
					$w[]="objects.parent = '$gid'";
				};
			};

			if ($r["trig"] & T_MAILSENT)
			{
//				echo "match T_MAILSENT <br>";
				$t["ml_sent_mails"]=1;
				$w[]="ml_sent_mails.mail = '".$r["trig_mailsent"]."'";
			};

			if ($r["trig"] & T_MAILSENTAT)
			{
//				echo "match T_MAILSENTAT <br>";
				$t["ml_sent_mails"]=1;
				$w[]="ml_sent_mails.tm >= '".$r["trig_mailsentat"]."' AND ml_sent_mails.tm <= '".$r["trig_mailsentat2"]."'";
			};

			if ($r["trig"] & T_MAILSUBJ)
			{
//				echo "match T_MAILSUBJ <br>";
				$t["ml_sent_mails"]=1;
				// protsendid võib siit ära võtta kui kasutajad nad ruuli edimise juures sisste trükix
				$w[]="ml_sent_mails.subject LIKE '%".$r["trig_mailsubj"]."%'";
			};

			if ($r["trig"] & T_USEDVARS)
			{
//				echo "match T_USEDVARS <br>";
				$t["ml_sent_mails"]=1;
				if (!isset($this->ml))
				{
					$this->ml=get_instance("mailinglist/ml_list");
				};
				$vn=$this->ml->get_all_varnames(false, $r_ob["meta"]["conf"]);
				$vars=unserialize($r["trig_usedvars"]);
				foreach($vars as $vid)
				{
					$w[]="ml_sent_mails.vars LIKE '%,".$vn[$vid].",%'";
				};
			};

			$tables=array("objects");
			if (!sizeof($wi))
			{
				continue;
			}
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
//			echo("<b>rule=$id</b><br>tables=<pre>");print_r($tables);echo("</pre>where=<pre>");print_r($w);echo("</pre><br>");//dbg

			

			// pane see junn kokku
			$wis=sizeof($wi)>0 ? "(".join(" AND ",$wi).")":"";
			//$wns=sizeof($wn)>0 ? (($wis? "AND":"")." NOT (".join(" AND ",$wn).")" ):"";

			
			$q="SELECT DISTINCT(objects.oid) AS oid FROM ".join(",",$tables)." WHERE $wis ";
//			echo("q=$q<br>");//dbg
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
//		echo("matchez=<pre>");print_r($matches);echo("</pre>");//dbg

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
//				echo("<b>executing  * rule $rid on $mid:</b>".$r["action"]."<br>");//dbg
				switch ($r["action"])
				{
					case A_ADDLIST:
						if (!isset($this->ml))
						{
							$this->ml=get_instance("mailinglist/ml_list");
						};
						list($lid,$lgroup)=explode(":",$r["actionid"]);
						$lid=(int)$lid;
						$lgroup=(int)$lgroup;// et määramata lgroup muutux nullix
//						echo "exec rule , addlist mid = $mid , lid = $lid <br>";
						$this->ml->add_member_to_list(array("mid" => $mid,"lid" => $lid,"grp" => $lgroup, "__norules" => 1));
						//echo("A_ADDLIST $mid $lid $lgroup<br>");//dbg
						break;

					case A_DELLIST:
						
						if (!isset($this->ml))
						{
							$this->ml=get_instance("mailinglist/ml_list");
						};
						list($lid,$lgroup)=explode(":",$r["actionid"]);
						$lid=(int)$lid;
						$this->ml->remove_member_from_list(array("mid" => $mid,"lid" => $lid,"__norules" => 1));
//						echo("A_DELLIST $mid $lid<br>");//dbg
						break;

					case A_DELETE:
/*						if (!isset($this->mlmember))
						{
							$this->mlmember=get_instance("mailinglist/ml_member");
						};
						$this->mlmember->orb_delete(array("id" => $mid,"_inner_call" => 1));*/
						$this->delete_object($mid);
//						echo("A_DELETE $mid<br>");//dbg
						break;

					case A_DONTSEND:
//						echo("A_DONTSEND $mid ".$r["actionid"]."<br><pre>");//dbg
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

	function exec_dynamic_rules()
	{
		$rarr = array();
		$this->db_query("SELECT * FROM ml_rules LEFT JOIN objects ON objects.oid = ml_rules.rid WHERE objects.status != 0 AND dynamic=1");
		while ($row = $this->db_next())
		{
			$rarr[] = $row["rid"];
		}
//		echo "exec dyn rules eq ".join(",", $rarr)." <Br>";
		$rule_inst = get_instance("mailinglist/ml_rule");
		$mt = $rule_inst->match_rules($rarr);
		$rule_inst->execute_rules($mt);
	}

	function do_entry_trigger($arr)
	{
		extract($arr);
		$r = $this->db_fetch_row("SELECT * FROM ml_rules WHERE rid = '$rule'");
		$r_ob = $this->get_object($rule);
		$this->fr = get_instance("formgen/form");
		$this->conf = get_instance("mailinglist/ml_list_conf");
		$this->fr->load($this->conf->get_user_search_form($r_ob["meta"]["conf"]));
		$matchedids=$this->fr->search($r["trig_entry"]);

		// ok, th eproblem is that if the search form searched from a chain, then
		// the entry id's in matchedids are for the forst form in the chain. damnit.
		// so we have to create the members so that all the form entries in the chain entry
		// are put as member form entries. 
		// 
		// so we check if the results are indeed for a chain
		// and then do things a bit differently. 
		// 
		if (count($matched_chain_ids = $this->fr->get_last_search_chain_entry_ids()))
		{
			return $this->do_entry_trigger_for_chain(array(
				"rule" => $rule,
				"r" => $r,
				"r_ob" => $r_ob,
				"chain_ids" => $matched_chain_ids
			));
		}

		$this->save_handle();

//		echo "matched entries for entry $r[trig_entry] = ".join(",", $matchedids)." search form eq ",$this->conf->get_user_search_form($r_ob["meta"]["conf"]),"<br>";
		// now we got the matched entry id's, but we gots to convert them to list member id's. 
		// except of course if they are not members yet, we will have to create them. damnit!
		if (is_array($matchedids) && sizeof($matchedids))
		{
			$todo = $this->make_keys($matchedids);
			$_tmp = array();
			$_usedeids = array();
			$this->db_query("SELECT member_id, entry_id FROM ml_member2form_entry LEFT JOIN objects ON objects.oid = ml_member2form_entry.member_id WHERE entry_id IN(".join(",",$matchedids).") AND objects.status != 0");
			while ($_row = $this->db_next())
			{
//				echo "found member for id $_row[entry_id] $_row[member_id] <Br>";
				if (!$_usedeids[$_row["entry_id"]])
				{
					$_usedeids[$_row["entry_id"]] = 1;
					$_tmp[] = $_row["member_id"];
					unset($todo[$_row["entry_id"]]);
				}
			}
			if ($r["action"] == A_ADDLIST)
			{
//				echo "action addlist <br>";
				$_usedeids = array();
				list($a_lid,$a_lgroup)=explode(":",$r["actionid"]);
				$ml_list_inst = get_instance("mailinglist/ml_list");
				$m_parent = $ml_list_inst->get_default_user_folder($a_lid);
				$_list_forms = $ml_list_inst->get_forms_for_list($a_lid);
				foreach($todo as $fentry_id)
				{
					if (!$_usedeids[$fentry_id])
					{
//						echo "proc $fentry_id <br>";
						$_usedeids[$fentry_id] = 1;

						$_formid = $this->fr->get_form_for_entry($fentry_id);
						if ($_list_forms[$_formid])
						{
							// here we gots to create the member for the list, because later we can only create members from other members
							// ie - create brother objects. here we gots to create the real member objects
							$mem_inst = get_instance("mailinglist/ml_member");
							$n_m_id = $mem_inst->create_member(array(
								"parent" => $m_parent,
								"entries" => array(
									$_formid => $fentry_id
								),
								"conf" => $ml_list_inst->get_conf_id($a_lid)
							));
							$_tmp[] = $n_m_id;
//							echo "creating new now member for entry $fentry_id parent $m_parent mid = $n_m_id <br>";
						}
						else
						{
//							echo "form not in list forms ".join(",",$_list_forms)." <br>";
						}
					}
				}
			}
		}
		$this->restore_handle();
		$ml_list_inst = get_instance("mailinglist/ml_list");
		$ml_list_inst->flush_member_cache($a_lid);
		return $_tmp;
	}

	function do_entry_trigger_for_chain($arr)
	{
		extract($arr);
		$this->save_handle();
		$ret = array();

//		echo "matched entries are chain entries ! <br>\n";
		list($a_lid, $a_lgid) = explode(":", $r["actionid"]);
//		echo "lid = $a_lid , lgid = $a_lgid <br>\n";
//		flush();
		$ml_list_inst = get_instance("mailinglist/ml_list");
		$m_parent = $ml_list_inst->get_default_user_folder($a_lid);
		$_list_forms = $ml_list_inst->get_forms_for_list($a_lid);

		// find the form that contains the email element
		// get if from the conf object
		$cf_inst = get_instance("mailinglist/ml_list_conf");
		$email_form = $cf_inst->get_form_for_email_element($r_ob["meta"]["conf"]);
//		echo "email form eq $email_form  <br>\n";
//		flush();

		// if the result is a chain, then go over all chain entries 
		// and for each chain entry, get all form entries for the form that contains the e-mail element
		// now, for each entry check if the member already exists
		// if not, create it
		$chain_inst = get_instance("formgen/form_chain");
		$form_inst = get_instance("formgen/form");
		
		foreach($chain_ids as $cheid)
		{
			$chain_id = $this->db_fetch_field("SELECT chain_id FROM form_chain_entries WHERE id = '$cheid'", "chain_id");
			$forms_in_chain = $form_inst->get_forms_for_chain($chain_id);
//			echo "chain entry id $cheid is from chain $chain_id , forms in chain = ".join(",", $forms_in_chain)." <br>\n";
//			flush();

			$forms_in_list = array();
			foreach($forms_in_chain as $ficid)
			{
				if ($_list_forms[$ficid] && $ficid != $email_form)
				{
					$forms_in_list[$ficid] = $ficid;
				}
			}
//			echo "forms in list = ".join("," , $forms_in_list)." <br>\n";
//			flush();

			// we also have to filter the dudes by list membership, because then the rule won't match if the dude is in any list!
			// filter them by the target list
/*			$members = $ml_list_inst->get_members($a_lid);
			$membersstr = join(",", array_keys($members));
			if ($membersstr != "")
			{
				$membersstr = "AND member_id IN
			}
			else
			{
				$membersstr = "AND 0";
			}*/

			$entries = $this->make_keys($form_inst->get_form_entries_for_chain_entry($cheid, $email_form));
			$entriesstr = join(",",$entries);
			if ($entriesstr != "")
			{
//				echo "checking against entries eq ".join(",",$entries)." <br>";
				$q = "SELECT member_id, entry_id FROM ml_member2form_entry LEFT JOIN objects ON objects.oid = ml_member2form_entry.member_id WHERE entry_id IN(".join(",",$entries).") AND objects.status != 0";
				$this->db_query($q);
//				echo "q = $q <br>\n";
//				flush();
				while ($row = $this->db_next())
				{
					unset($entries[$row["entry_id"]]);
//					echo "unset entries $row[entry_id] <br>\n";
					$ret[] = $row["member_id"];
//					echo "found member for entry $row[entry_id] member = $row[member_id] <br>\n";
//					flush();
				}
			}

//			echo "entries eq ".join(",",$this->map2("%s => %s", $entries))." <br>";
			// now all the entries that are in $entries have no corresponding members, we gotta create them
			$usedeids = array();
			foreach($entries as $eid)
			{
				if (!$usedeids[$eid])
				{
					$usedeids[$eid] = 1;
					
					$memberentries = array($email_form => $eid);
					foreach($forms_in_list as $flid)
					{
						list(,$_eid) = each($form_inst->get_form_entries_for_chain_entry($cheid, $flid));
						$memberentries[$flid] = $_eid;
					}

					$mem_inst = get_instance("mailinglist/ml_member");
					$n_m_id = $mem_inst->create_member(array(
						"parent" => $m_parent,
						"entries" => $memberentries,
						"conf" => $ml_list_inst->get_conf_id($a_lid)
					));
					$ret[] = $n_m_id;
//					echo "created new member ($n_m_id) under $m_parent entries = ".join(",", $this->map2("%s = %s ", $memberentries))." <br>\n";
//					flush();
				}
			}
		}

		$ml_list_inst->flush_member_cache($a_lid);
		$this->restore_handle();
		return $ret;
	}
}
?>