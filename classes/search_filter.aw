<?php


	global $orb_defs;
	$orb_defs["search_filter"] ="xml";
	classload("form","form_base","sql_filter");

	class search_filter extends aw_template
	{
		function search_filter()
		{
			$this->tpl_init("automatweb/filter");
			$this->db_init();
			lc_load("definition");
			$this->sql_filter=new sql_filter();
			
		}

		function orb_new($arr)
		{
			is_array($arr)? extract($arr) : $parent=$arr;

			$this->mk_path($parent,"Lisa Filter");
			$this->read_template("new_filter.tpl");

			$this->fb=new form_base();
			$formlist=$this->fb->get_list(FTYPE_ENTRY,false,true);

			$chainlist=array();
			$this->get_objects_by_class(array("class"=>CL_FORM_CHAIN));
			while ($r = $this->db_next())
			{
				$chainlist[$r["oid"]]=$r["name"];
			};
			$this->vars(array(
				"formlist"=>$this->picker("",$formlist),
				"chainlist"=>$this->picker("",$chainlist),
				"reforb" => $this->mk_reforb("submit_new",array("parent" => $parent))
				));
			
			return $this->parse();
		}

		function orb_submit_new($arr)
		{
			is_array($arr)? extract($arr) : $parent=$arr;

			$id=$this->new_object(array(
				"class_id" => CL_SEARCH_FILTER,
				"name" => $name,
				"parent" => $parent,
				"comment" => $comment,
				));

			$this->data=array(
				"type" => $type,
				"target_id" => $type=="form"?$target_id_f:$target_id_c,
				"output_id" => 0,
				"stat_id" => 0,
				"stat_show" => 0,
				"stat_data" => array(),
				);

			$this->set_object_metadata(array("oid" => $id,"key" => "data","value"=> $this->data));
			return $this->mk_my_orb("change",array("id" => $id,"parent" => $parent));
		}


		function orb_submit_change($arr)
		{
			extract($arr);
			$this->id=$id;
			$arr["filter"]=$this->__load_filter();

			$this->filter=$this->sql_filter->do_submit_filter_edit($arr);

			$this->__save_filter();
			return $this->mk_my_orb("change",array("id" => $id));
		}

		function orb_change($arr)
		{
			is_array($arr)? extract($arr) : $parent=$arr;
			$this->id=$id;
			$this->db_query("SELECT name,parent FROM objects WHERE oid='$this->id'");
			$r=$this->db_next();
			$parent=$r["parent"];
			$name=$r["name"];

			$this->mk_path($parent,"Filter");

			
			$this->__load_data();
			$this->__load_filter();
			$this->filter["name"]=$name;
			$this->build_master_array();
			$this->sql_filter->set_data($this->master_array);

			//echo($this->sql_filter->filter_to_sql(array("filter"=>$this->filter)));//dbg
			return $this->make_upper_menu($arr,"change").$this->sql_filter->do_filter_edit(array(
			"filter"=>$this->filter,
			"is_change_part"=>$is_change_part,
			"change_part" => $change_part,
			"reforb"=>$this->mk_reforb("submit_change",array("id" => $id))
			));
			
		}

		function __load_data()
		{
			$this->data=$this->get_object_metadata(array("oid" => $this->id,"key" => "data"));
		}

		function __save_data()
		{
			$this->set_object_metadata(array("oid" => $this->id,"key" => "data", "value" => $this->data));
		}

		function __load_filter()
		{
			$this->filter=$this->get_object_metadata(array("oid" => $this->id,"key" => "filter"));
			if (!is_array($this->filter))
			{
				$this->filter=array();
			};
			return $this->filter;
		}

		function __save_filter()
		{
			$this->set_object_metadata(array("oid" => $this->id,"key" => "filter","value" => $this->filter));
		}

		function build_master_array()
		{
			$this->master_array=array(
				""=>array(
					"real"=>"%virtual",
					"fields"=>array(
						"täistekst"=>array(
							"real"=>"%täistekst",
							"type"=>0,
						)
					)
				));
			
			$formids=array();
			if ($this->data["type"]=="form")
			{
				$formids=explode(",",$this->data["target_id"]);
				if (!is_array($formids))
				{
					$formids=array();
				};
			} else
			if ($this->data["type"]=="chain")
			{
				$this->db_query("SELECT form_id FROM form2chain WHERE chain_id='".(int)$this->data["target_id"]."'");
				while ($r= $this->db_next())
				{
					$formids[]=$r["form_id"];
				};
			};
			
			$this->form=new form();
			
			//Okay, let's build the array
			foreach ($formids as $k => $fid)
			{
				$fid=(int)$fid;
				
				$this->db_query("SELECT objects.name FROM objects WHERE objects.oid='$fid'");
				$r=$this->db_next();
				
				$formname=str_replace(" ","_",$r["name"]);
				/*$content=$this->content[$fid]=$this->__unserialize_fdata($r["content"]);
				echo("<pre>build_master_array::content=");print_r($content);echo("</pre>");//dbg*/
				$content=$this->form->get_form_elements(array("id" => $fid,"key" => "id"));
				//echo("form title=$ftitle<br><pre>");print_r($content);echo("</pre>");//dbg

				$arr=array();
				$arr["real"]="form_".$fid."_entries";
				//echo("content=><pre>");print_r($content);echo("</pre><br>");//dbg
				foreach($content as $f_id => $edata)
				{
					$fieldname=$edata["name"];
					$create=1;
					//echo("$fieldname=><pre>");print_r($edata);echo("</pre><br>");//dbg
					switch ($edata["type"])
					{
						case "button"://Don't let these suckers in!
							$create=0;
							break;

						case "radiobutton":
							//paluks siia arraysse info ka rb. väärtuse kohta??
							
							$arr["fields"][$edata["name"]]["type"]=0;//string
							$arr["fields"][$edata["name"]]["select"][$edata["text"]]=$edata["text"];
							break;

						case "listbox":
							$arr["fields"][$edata["name"]]["type"]=0;//string
							if (is_array($edata["lb_items"]))
							foreach($edata["lb_items"] as $number => $lbval)
							{
								if ($lbval)//miskid tyhjad valikud tekivad
									$arr["fields"][$edata["name"]]["select"][$lbval]=$lbval;
							};
							break;

						case "date":
							$arr["fields"][$edata["name"]]["type"]=2;//date
							break;

						default:
							$create=1;
							$arr["fields"][$edata["name"]]["type"]=0;//string
							break;
					};
					if ($create)
					{
						$arr["fields"][$edata["name"]]["real"]="ev_$f_id";
					};

				};
			
				$this->master_array[$formname]=$arr;
			};//of ($formids as $k => $fid)

			//echo("<pre>");print_r($this->master_array);echo("</pre>");//dbg
		}

		function make_upper_menu($arr,$action)
		{
			extract($arr);
			$a="<table border=0 cellpadding=2 cellspacing=1 bgcolor=#CCCCCC><tr>";
			$b=($action=="change")?0:1;
			$a.="<td bgcolor=#EEEEEE>".($b?"<a href='".$this->mk_my_orb("change",array("id"=>$id))."'>":"")."Tingimused".($b?"</a>":"")."</td>";

			$b=($action=="output")?0:1;
			$a.="<td bgcolor=#EEEEEE>".($b?"<a href='".$this->mk_my_orb("output",array("id"=>$id))."'>":"")."Väljund".($b?"</a>":"")."</td>";

			$b=($action=="statdata")?0:1;
			$a.="<td bgcolor=#EEEEEE>".($b?"<a href='".$this->mk_my_orb("statdata",array("id"=>$id))."'>":"")."Statandmed".($b?"</a>":"")."</td>";

			$b=($action=="stat")?0:1;
			$a.="<td bgcolor=#EEEEEE>".($b?"<a href='".$this->mk_my_orb("stat",array("id"=>$id))."'>":"")."Stattabel".($b?"</a>":"")."</td>";

			$b=($action=="search")?0:1;
			$a.="<td bgcolor=#EEEEEE>".($b?"<a href='".$this->mk_my_orb("search",array("id"=>$id))."'>":"")."Otsi".($b?"</a>":"")."</td>";
			$a.="</tr></table>";
			return $a;
		}

		// see on selline func mis kudagi petab 2ra kliendi
		function orb_totally_fake_fulltext_search($arr)
		{
			extract($arr);
			$html="<form action='orb.aw' method='get'>
				<input type='text' name='true_fulltext' value='' class='small_button'>
				<input type='submit' value='Otsi' class='small_button'>
				<input type='hidden' name='class' value='search_filter'>
				<input type='hidden' name='id' value='$id'>
				<input type='hidden' name='action' value='do_tf_fulltext_search'>
				</form>
				";
			return $html;
		}

		function orb_do_totally_fake_fulltext_search($arr)
		{
			extract($arr);
			//okei, siin nüüd muudame ära ainsa filtri osa teksti ja otsime stuffi
			$this->id=$id;
			$this->__load_filter();
			//print_r($this->filter);//dbg
			$this->filter["p0"]["val"]=$true_fulltext;
			$this->__save_filter();

			$arr["no_menu"]=1;
			$arr["j2ta_see_form_sinna_yles"]=1;
			return $this->orb_search($arr);

		}

		function orb_stat_new_submit($arr)
		{
			extract($arr);
			//echo("blah<pre>");print_r($arr);echo("</pre>");//dbg
			$this->id=$filter_id;
			$this->db_query("SELECT name,parent FROM objects WHERE oid='$this->id'");
			$r=$this->db_next();
			$this->__load_data();

			classload("table");
			$tbl=new table();
			
			$arr["is_filter"]=1;
			$arr["filter"]=$this->id;
			$tbl->submit_add($arr);
			$this->data["stat_id"]=$tbl->id;
			//echo("id=".$tbl->id);
			$this->data["stat_show"]=1;
			$this->data["stat_pix"]=10;

			$this->__save_data();

			return $this->mk_my_orb("stat",array("id" => $filter_id));
		}

		function orb_statdata($arr)
		{
			extract($arr);
			$this->id=$id;
			$this->db_query("SELECT name,parent FROM objects WHERE oid='$this->id'");
			$r=$this->db_next();
			$parent=$r["parent"];
			$name=$r["name"];

			$this->read_template("statdata.tpl");
			
			$this->__load_data();
			$this->build_master_array();

			$fieldarr=array();
			if (is_array($this->master_array))
			foreach($this->master_array as $tfakename => $tdata)// for each table do
			{
				if ($tfakename && is_array($tdata) && is_array($tdata["fields"])) // for each field in table do
				foreach($tdata["fields"] as $ffakename => $fdata)
				{
					$fieldarr[$tdata["real"].".".$fdata["real"]]="$tfakename.$ffakename";
				};
			};
			

			$statd="";
			$fields=$this->picker("",$fieldarr);

			if (is_array($this->data["statdata"]))
			foreach($this->data["statdata"] as $alias => $sd)
			{
				$this->vars(array(
					"alias" => "#$alias",
					"nr" => $alias,
					"display" => $sd["display"],
					));
				$statd.=$this->parse("statd");
			};

			$this->vars(array(
				"chkstat_show"=> $this->data["stat_show"]?"checked":"",
				"stat_pix" => $this->data["stat_pix"],
				"statd" => $statd,
				"fields" => $fields,
				"reforb" => $this->mk_reforb("submit_statdata",array("id"=>$id)),
				));

			$this->mk_path($parent,"Filter");
			return $this->make_upper_menu($arr,"statdata").$this->parse();
		}

		function orb_submit_statdata($arr)
		{
			extract($arr);
			$this->id=$id;
			$this->__load_data();
			$this->data["stat_pix"]=$stat_pix;
			$this->data["stat_show"]=$stat_show;
			if ($subaction=="addpart")
			{
				$arr2["func"]=$func;
				
				list($rtable,$rfield)=explode(".",$field);
				$this->build_master_array();

				//tra, vastupidi on :)
				if (is_array($this->master_array))
				foreach($this->master_array as $tfakename => $tdata)// for each table do
				{
					if ($tdata["real"]==$rtable && is_array($tdata["fields"])) // for each field in table do
					{
						foreach($tdata["fields"] as $ffakename => $fdata)
						if ($fdata["real"]==$rfield)
						{
							$ffield=$ffakename;
							break;
						};
						$ftable=$tfakename;
					};
				};
				$arr2["field"]=$rfield;
				$arr2["table"]=$rtable;
				$arr2["display"]="$func($ftable.$ffield)";
				//print_r($arr2);//dbg
				$this->data["statdata"][]=$arr2;
				
			} else
			if ($subaction=="delpart")
			{
				if (is_array($sel))
				foreach($sel as $nr)
				{
					unset($this->data["statdata"][$nr]);
				};
			};

			$this->__save_data();
			return $this->mk_my_orb("statdata",array("id"=>$id));
		}

		function orb_stat($arr)
		{
			extract($arr);
			$this->id=$id;
			$this->db_query("SELECT name,parent FROM objects WHERE oid='$this->id'");
			$r=$this->db_next();
			$parent=$r["parent"];
			$name=$r["name"];
			
			$this->__load_data();

			classload("table");
			$tbl=new table();
			$rec=$this->get_object($this->data["stat_id"]);
			if ($this->data["stat_id"] && $rec["status"]!=0)
			{
				$parse="<div><IFRAME SRC='".$this->mk_my_orb("change",array(
					"id"=>$this->data["stat_id"],
					"is_filter"=>1,
					"filter"=>$id,
					),"table")."' Style='width:100%;height:800;margin-left:-5;margin-top:0;' frameborder=0 id='ifr'></iframe></div>";
			} else
			{
				// nõu komments
				$parse="Tee uus stattabel:".$tbl->add(array("parent" => $parent,"name" => "stat_for_$name"));

				$parse=preg_replace("/name='class' value='(.+?)'/","name='class' value='search_filter'",$parse);
				$parse=preg_replace("/name='action' value='(.+?)'/","name='action' value='stat_new_submit'",$parse);
				$parse=preg_replace("/<input type='hidden' name='reforb'/","<input type='hidden' name='filter_id' value='$id'><input type='hidden' name='reforb'",$parse);
			};

			$this->mk_path($parent,"Filter");
			return $this->make_upper_menu($arr,"stat").$parse;
		}


		function orb_search($arr)
		{
			extract($arr);
			if (isset($filter_id))
			{
				$id=$filter_id;
			};
			$this->id=$id;
			$this->db_query("SELECT name,parent FROM objects WHERE oid='$this->id'");
			$r=$this->db_next();
			$parent=$r["parent"];
			$name=$r["name"];
			$this->__load_data();
			
			if (!$no_menu)
			$this->mk_path($parent,"Filter");

			if (!$this->data["output_id"])
			{
				return $this->make_upper_menu($arr,"search")."Väljundi tabelit pole veel määratud, vajuta 'väljund' lingile!";
			};
			global $search_filter_m;
			session_register("search_filter_m");
			$sfm=unserialize($search_filter_m);
			if /*(!is_array($sfm[$id]))*/ (1)//Kuna "enam-vähem" kõlab minumeelest ysna "jah" moodi siis otsib igakord uuesti
			{
				if (!$dont_load_filter)
				$this->__load_filter();
				
				$this->build_master_array();
				$this->sql_filter->set_data($this->master_array);

				
				$sfm[$id]=$this->perform_search();
				$search_filter_m=serialize($sfm);
			};

			//siin tuleb stuffi näidata
			classload("form_table");

			$this->ft=new form_table();
			$table_id=$this->data["output_id"];
			if (!$this_page_array)
			{
				$this_page_array=array("class" => "search_filter", "action" => "search",   "filter_id" => $id,"id"=>$id,"op_id" => $op_id);
			};
			$this->ft->start_table($table_id,$this_page_array);

			$stats=array();//statistika avaldiste väärtused
			$num_rec_found=0;
			if ($this->data["type"]=="chain")
			{
				// form_table rida 469
				
				$chain_id=$this->data["target_id"];
				

				$this->ft->load_chain($chain_id);

				$eids = $sfm[$id];

				$tbls = "";
				$joins = "";
				reset($this->ft->chain["forms"]);
				list($fid,) = each($this->ft->chain["forms"]);
				while(list($ch_fid,) = each($this->ft->chain["forms"]))
				{
					if ($ch_fid != $fid)
					{
						$tbls.=",form_".$ch_fid."_entries.*";
						$joins.=" LEFT JOIN form_".$ch_fid."_entries ON form_".$ch_fid."_entries.chain_id = form_".$fid."_entries.chain_id ";
					}
				}
				
				$eids = join(",", $eids);
				if ($eids != "")
				{
					$q = "SELECT distinct(form_".$fid."_entries.id) as entry_id, form_".$fid."_entries.chain_id as chain_entry_id, form_".$fid."_entries.* $tbls FROM form_".$fid."_entries LEFT JOIN objects ON objects.oid = form_".$fid."_entries.id $joins WHERE objects.status != 0 AND form_".$fid."_entries.chain_id in ($eids)";
					//echo "q = $q <br>";
					$this->ft->db_query($q);
					while ($row = $this->ft->db_next())
					{
						$num_rec_found++;
						if ($this->data["stat_show"] && $this->data["stat_id"] && is_array($this->data["statdata"]))
						{
							foreach($this->data["statdata"] as $alias2 => $statd2)
							{
								
								$v2=$row[$statd2["field"]];
								//echo($statd2["field"]." = ".$v2."<br>");//dbg
								switch($statd2["func"])
								{
									case "sum":
										$stats[$alias2]["val"]+=$v2;
										break;
									case "min":
										if ($stats[$alias2]["val"]=="")
											$stats[$alias2]["val"]=$v2;
										if ($v2<$stats[$alias2]["val"])
											$stats[$alias2]["val"]=$v2;
										break;
									case "max":
										if ($v2>$stats[$alias2]["val"])
											$stats[$alias2]["val"]=$v2;
										break;
									case "avg":
										$stats[$alias2]["sum"]+=$v2;
										$stats[$alias2]["num"]++;
										break;
								};
							};
						};
						//echo("<pre>");print_r($row);echo("</pre>");//dbg
						$row["ev_change"] = "<a href='".$this->ft->mk_my_orb("show", array("id" => $chain_id,"entry_id" => $row["chain_entry_id"]), "form_chain")."'>Muuda</a>";
						$row["ev_view"] = "<a href='".$this->ft->mk_my_orb("show_entry", array("id" => $fid,"entry_id" => $row["entry_id"], "op_id" => $op_id,"section" => $section),"form")."'>Vaata</a>";		
						$row["ev_delete"] = "<a href='".$this->ft->mk_my_orb(
							"delete_entry", 
								array(
									"id" => $fid,
									"entry_id" => $row["entry_id"], 
									"after" => $this->ft->binhex($this->ft->mk_my_orb("show_user_entries", array("chain_id" => $chain_id, "table_id" => $table_id, "op_id" => $op_id,"section" => $section)))
								),
							"form")."'>Kustuta</a>";
						//echo "eid = ", $row["entry_id"], " ch_eid = ", $row["chain_entry_id"], "<br>";
						$this->ft->row_data($row);
					}
				}

			} else
			{
				//tavaline yhest formist otsimine oli hoopis
				
				$fid=$this->data["target_id"];
				
				$eids = $sfm[$id];
				$eids=join(",",$eids);
				if ($eids != "")
				{
					$q="SELECT * FROM form_".$fid."_entries,objects WHERE objects.status != 0 and objects.oid = '$fid' AND form_".$fid."_entries.id in ($eids)";
					$this->ft->db_query($q);
					while ($row = $this->ft->db_next())
					{
						$num_rec_found++;
						if ($this->data["stat_show"] && $this->data["stat_id"] && is_array($this->data["statdata"]))
						{
							foreach($this->data["statdata"] as $alias2 => $statd2)
							{
								
								$v2=$row[$statd2["field"]];
								//echo($statd2["field"]." = ".$v2." ++".$statd2["func"]."<br>");//dbg

								switch($statd2["func"])
								{
									case "sum":
										$stats[$alias2]["val"]+=$v2;
										break;
									case "min":
										if ($stats[$alias2]["val"]=="")
											$stats[$alias2]["val"]=$v2;
										if ($v2<$stats[$alias2]["val"])
											$stats[$alias2]["val"]=$v2;
										break;
									case "max":
										if ($v2>$stats[$alias2]["val"])
											$stats[$alias2]["val"]=$v2;
										break;
									case "avg":
										$stats[$alias2]["sum"]+=$v2;
										$stats[$alias2]["num"]++;
										break;
								};
							};
						};
						$row["ev_delete"] = "<a href='".$this->ft->mk_my_orb(
							"delete_entry", 
								array(
									"id" => $fid,
									"entry_id" => $row["entry_id"], 
									"after" => $this->ft->binhex($this->ft->mk_my_orb("show_user_entries", array("chain_id" => $chain_id, "table_id" => $table_id, "op_id" => $op_id,"section" => $section)))
								),
							"form")."'>Kustuta</a>";
						$this->ft->row_data($row);
					}
				};
			};




			$this->ft->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));

			if ($GLOBALS["get_csv_file"])
			{
				header('Content-type: Application/Octet-stream"');
				header('Content-disposition: root_access; filename="csv_output_'.$id.'.csv"');
				print $this->ft->t->get_csv_file();
				die();
			};
			$parse="";
			if ($j2ta_see_form_sinna_yles)
			{
				$parse.="<form action='orb.aw' method='get'>
				<input type='text' name='true_fulltext' value='' class='small_button'>
				<input type='submit' value='Otsi' class='small_button'>
				<input type='hidden' name='class' value='search_filter'>
				<input type='hidden' name='id' value='$id'>
				<input type='hidden' name='action' value='do_tf_fulltext_search'>
				</form>
				";
			};
			
			$parse.="Otsingu tulemusena leiti ".(int)$num_rec_found." kirjet";
			//siin teeb lingi csv outputile
			if ($this_page)
			{
				$parse.="&nbsp;&nbsp;<a href='".$this_page."&get_csv_file=1' target=_blank>CSV</a><br>";
			} else
			{
				$parse.="&nbsp;&nbsp;<a href='".$this->mk_my_orb("search",array("id"=>$id,"get_csv_file"=>1))."' target=_blank>CSV</a><br>";
			};
			
			$parse.= $this->ft->get_css();
			$parse.="<form action='reforb.aw' method='POST'>\n";
			if ($this->ft->table["submit_top"])
			{
				$parse.="<input type='submit' value='".$this->ft->table["submit_text"]."'>";
			}
			if ($this->ft->table["user_button_top"])
			{
				$parse.="&nbsp;<input type='submit' value='".$this->ft->table["user_button_text"]."' onClick=\"window.location='".$this->ft->table["user_button_url"]."';return false;\">";
			}
			$blah=$this->ft->t->draw();
			$parse.=str_replace("<table>","",$blah);

			if ($this->ft->table["submit_bottom"])
			{
				$parse.="<input type='submit' value='".$this->ft->table["submit_text"]."'>";
			};
			if ($this->ft->table["user_button_bottom"])
			{
				$parse.="&nbsp;<input type='submit' value='".$this->ft->table["user_button_text"]."' onClick=\"window.location='".$this->ft->table["user_button_url"]."';return false;\">";
			};

			$parse.=$this->ft->mk_reforb("submit_table", array("return" => $this->ft->binhex($this->ft->mk_my_orb("show_entry", array("id" => $this->ft->id, "entry_id" => $entry_id, "op_id" => $output_id)))));
			
			if ($this->data["stat_show"] && $this->data["stat_id"])
			{
				classload("table");
				$tbl=new table();
							// tee veel avg funktsioonid korda
				$tbl->fl_external=array();
				if (is_array($this->data["statdata"]))
				foreach($this->data["statdata"] as $alias2 => $statd2)
				{
					switch($statd2["func"])
					{
						case "avg":

							$stats[$alias2]["val"]=$stats[$alias2]["num"]?$stats[$alias2]["sum"]/$stats[$alias2]["num"]:0;
							break;
					};
					//echo($alias2." = ".$stats[$alias2]["val"]."<br>");//dbg
					$tbl->fl_external[$alias2]=$stats[$alias2]["val"];
				};

				if ($this->data["stat_pix"])
				{
					$parse.="<table border=0 cellpadding=0 cellspacing=0 height='".$this->data["stat_pix"]."' Style='height:".$this->data["stat_pix"]."px'><tr><td></td></tr></table>";
				};
				$parse.=$tbl->show(array("id" => $this->data["stat_id"],"is_filter" => 1));
			};
			$parse.="</form>";
			if (!$no_menu)
			{
				$parse=$this->make_upper_menu($arr,"search").$parse;
			};
			return $parse;
		}

		function orb_submit_select_forms($arr)
		{
			extract($arr);
			$this->id=$id;
			$this->__load_data();
			//print_r($selected_forms);//dbg
			$this->data["selected_forms"]=$this->binhex(serialize(array_flip($selected_forms)));
			$this->__save_data();
			return $this->mk_my_orb("output",array("id" => $id));
		}

		function orb_output($arr)
		{
			extract($arr);
			$this->id=$id;
			$this->db_query("SELECT name,parent FROM objects WHERE oid='$this->id'");
			$r=$this->db_next();
			$parent=$r["parent"];
			$name=$r["name"];

			classload("form_table");
			$this->ft=new form_table();

			

			$this->__load_data();
			$this->__load_filter();
			
			$this->build_master_array();
			$this->sql_filter->set_data($this->master_array);

			// Kui pole veel formi tabelit tehtud siis tee valmis
			if (!$this->data["output_id"])
			{
				//echo("type=".$this->data["type"]);//dbg
				if ($this->data["selected_forms"] || $this->data["type"]!="chain")
				{
				
					//echo("oki, hakkan uut tegema");//dbg
					$this->data["selected_forms"]=unserialize($this->hexbin($this->data["selected_forms"]));
					//echo("self=<pre>");print_r($this->data["selected_forms"]);echo("</pre>");//dbg
					$num_cols=0;
					$form_ids=array();
					$names=array();
					$columns=array();
					$sortable=array();
					if (is_array($this->master_array))
					foreach($this->master_array as $faketname => $tdata)
					{
						//echo("f=$faketname<br>");//dbg
						if ($faketname &&  (isset($this->data["selected_forms"][$faketname]) || $this->data["type"]!="chain"))
						{
							//echo("on olemas<br>");
							
							list($a_,$form_id,$b_)=explode("_",$tdata["real"]);
							$form_ids[]=$form_id;
							//echo("faketname=$faketname form_id=$form_id<br>");//dbg
							if (is_array($tdata["fields"]))
							foreach($tdata["fields"] as $fakefname => $fdata)
							{
								//echo("field=$fakefname<br>");//dbg
								list($a_,$fieldid)=explode("_",$fdata["real"]);

								$names[$num_cols][1]=$fakefname;
								$sortable[$num_cols]=1;
								$columns[$num_cols][]=$fieldid;
								$num_cols++;
							};
						};
					};

					$arr=array(
						"name" => "output_for_$name",
						"parent" => $parent,
						"comment" => "$id_$name",
						"num_cols" => $num_cols,
						"forms" => $form_ids);
					//echo("arr=<pre>");print_r($arr);echo("</pre>");//dbg
					$this->ft->submit($arr);
					$this->data["output_id"]=$this->ft->id;
					$this->__save_data();

					//echo("second phase columns=<pre>");print_r($columns);echo("</pre>");//dbg
					
					$arr=array_merge($arr,array(
						"id" => $this->data["output_id"],
						"columns" => $columns,
						"names" => $names,
						"sortable" => $sortable,
						));
					//echo("arr=<pre>");print_r($arr);echo("</pre>");//dbg
					$this->ft->submit($arr);
					//echo("f table id=".$this->data["output_id"]);//dbg
				} else
				{
					//Vot siin tuleb nyyd valida formid, mida kasutada outputis
					$this->read_template("select_forms.tpl");
					$this->mk_path($parent,"<a href=\"".$this->mk_my_orb("change",array("id"=>$id))."\">Muuda filtrit</a> | Väljund | <a href=\"".$this->mk_my_orb("search",array("id"=>$id))."\">Otsi</a>");
					
					$form_arr=array();
					foreach($this->master_array as $faketname => $fdata)
					{
						if ($faketname)
							$form_arr[$faketname]=$faketname;
					};
					$this->vars(array(
						"form_list"=> $this->multiple_option_list("",$form_arr),
						"reforb" => $this->mk_reforb("submit_select_forms",array("id"=>$id)),
						));
					return $this->parse();
				};
			}

			$cparse=$this->ft->change(array("id" => $this->data["output_id"]));
			$cparse=preg_replace("/name='class' value='(.+?)'/","name='class' value='search_filter'",$cparse);
			$cparse=preg_replace("/name='action' value='(.+?)'/","name='action' value='output_submit'",$cparse);
			$cparse=preg_replace("/<input type='hidden' name='reforb'/","<input type='hidden' name='filter_id' value='$id'><input type='hidden' name='reforb'",$cparse);

			$this->mk_path($parent,"Filter");

			return $this->make_upper_menu($arr,"output").$cparse;
		}

		function orb_output_submit($arr)
		{
			extract($arr);
			classload("form_table");
			$this->ft=new form_table();
			$this->ft->submit($arr);
			
			return $this->mk_my_orb("output",array("id" => $filter_id));
		}

		// selle jaoks peab olema tehtud build_master_array ja load_filter
		function perform_search()
		{
			$this->matches=array();
			//Okei, kõigepealt küsi sql filtri käest sql päringu where osa
			$sqlw=$this->sql_filter->filter_to_sql(array("filter" => $this->filter));

			//Nii, nüüd tuleb see täistekstotsing ringi vahetada
			$fulltextsearch=array();
			$used_tables=array();
			//echo("master_Array=<pre>");print_r($this->master_array);echo("</pre>");//dbg
			if (is_array($this->master_array))
			foreach ($this->master_array as $fakefname => $fdata)
			{
				if ($fakefname)
				{
					$realfname=$fdata["real"];
					//echo("fakefname=$fakefname realfname=$realfname<br>");//dbg
					$used_tables[$realfname]=1;

					if (is_array($fdata["fields"]))
					foreach ($fdata["fields"] as $fakeename => $edata)
					{
						$fulltextsearch[]=$realfname.".".$edata["real"]." LIKE '%\\1%'";
					};
				};
			};

			$ftsstring=join(" or ",$fulltextsearch);
			//print_r($fulltextsearch);//dbg
			//echo("ftsstring=$ftsstring<br>");//dbg
			//echo("sqlw=$sqlw<br>");//dbg
			$sqlw=preg_replace("/%virtual.%täistekst = '(.*?)'/","($ftsstring)",$sqlw);
			//echo("sqlw=$sqlw<br>");//dbg

			if ($this->data["type"]=="chain")
			{
			// Oki, seda saab siiski teha yhe queryga:)
			// nimelt tuleb chaini puhul kõik üksikud form_baah_entries joinida nii et 
			// select id from form_chain_entries where chain_id='$target_id' and form_baah_entries.
			// chain_id=form_chain_entries.id and baah.. and form_111_entries.chain_id=form_chain_entries.id
			// ja siis pärast näitamisel valida lihtsalt ids form_chain_entries tablast ja seal on juba kirjas
			// mis entry iga formi kohta käib. blaaah indeed.

				$leftjoin=Array();
				foreach(array_keys($used_tables) as $tbl)
				{
					$leftjoin[]=" LEFT JOIN $tbl ON $tbl.chain_id=form_chain_entries.id ";
				};
				//$sql="SELECT form_chain_entries.id FROM form_chain_entries, ".join(",",array_keys($used_tables))." $sqlw AND form_chain_entries.chain_id='".$this->data["target_id"]."' AND ".join(" AND ",$jointofce);
				$sqlw=$sqlw?$sqlw." AND ":" WHERE ";
				$sql="SELECT DISTINCT(form_chain_entries.id) as id FROM form_chain_entries".join(" ",$leftjoin)." $sqlw form_chain_entries.chain_id='".$this->data["target_id"]."'";
				//echo("sql=$sql<br>");//dbg
			} else
			{
				$sql="SELECT id FROM ".join(",",array_keys($used_tables))." $sqlw";
				//echo("sql=$sql<br>");//dbg
			};

			$this->db_query($sql);
			$matches=array();
			while ($r=$this->db_next())
			{
				$matches[]=$r["id"];
			};
			//echo "<pre>", var_dump($matches),"</pre><br>";
			return $matches;
		}

	


		function orb_filter_edit_change_part($arr)
		{
			extract($arr);
			if (!is_array($sel))
			{
				return $this->mk_my_orb("change",array("id" => $id));
			};
			$chgnum=$sel[0];
			return $this->mk_my_orb("change",array("change_part"=> $chgnum,"is_change_part"=>1, "id" => $id));
		}

		function orb_filter_edit_add($arr)
		{
			extract($arr);
			
			$this->id=$id;
			
			$arr["filter"]=$this->__load_filter();

			$this->filter=$this->sql_filter->do_filter_edit_add($arr);

			$this->__save_filter();
			
			return $this->mk_my_orb("change",array("id" => $id));
		}

		// filtrile yhe tingimuse kustutamine
		function orb_filter_edit_del($arr)
		{
			extract($arr);
			if (!is_array($sel) || !sizeof($sel))
			{
				return $this->mk_my_orb("change",array("id" => $id));
			};
			$this->id=$id;
			$arr["filter"]=$this->__load_filter();
			
		
			$this->filter=$this->sql_filter->do_filter_edit_del($arr);

			$this->__save_filter();
			
			return $this->mk_my_orb("change",array("id" => $id));
		}




	};
?>
