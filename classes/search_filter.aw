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

			$this->mk_path($parent,"Muuda filtrit | <a href=\"".$this->mk_my_orb("output",array("id"=>$id))."\">Väljund</a> | <a href=\"".$this->mk_my_orb("search",array("id"=>$id))."\">Otsi</a>");

			
			$this->__load_data();
			$this->__load_filter();
			$this->filter["name"]=$name;
			$this->build_master_array();
			$this->sql_filter->set_data($this->master_array);

			//echo($this->sql_filter->filter_to_sql(array("filter"=>$this->filter)));//dbg
			return $this->sql_filter->do_filter_edit(array(
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
				$content=$this->form->get_form_elements(array("id" => $fid));
				//echo("form title=$ftitle<br><pre>");print_r($content);echo("</pre>");//dbg

				$arr=array();
				$arr["real"]="form_".$fid."_entries";

				foreach($content as $fieldname => $edata)
				{
					$create=1;
					switch ($edata["type"])
					{
						case "button"://Don't let these suckers in!
							$create=0;
							break;

						case "radiobutton":
							$arr["fields"][$edata["name"]]["type"]=0;//string
							$arr["fields"][$edata["name"]]["select"][$edata["ch_value"]]=$edata["ch_value"];
							break;

						case "listbox":
							$arr["fields"][$edata["name"]]["type"]=0;//string
							if (is_array($edata["listbox_items"]))
							foreach($edata["listbox_items"] as $number => $lbval)
							{
								if ($lbval)//miskid tyhgad valikud tekivad
									$arr["fields"][$edata["name"]]["select"][$lbval]=$lbval;
							};
							break;

						case "date":
							$arr["fields"][$edata["name"]]["type"]=2;//date
							break;

						case "textbox":
							$arr["fields"][$edata["name"]]["type"]=0;//string
							break;

						default:
							$create=1;
							$arr["fields"][$edata["name"]]["type"]=0;//string
							break;
					};
					if ($create)
					{
						$arr["fields"][$edata["name"]]["real"]="ev_".$edata["id"];
					};

				};
			
				$this->master_array[$formname]=$arr;
			};//of ($formids as $k => $fid)

			//echo("<pre>");print_r($this->master_array);echo("</pre>");//dbg
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

			$this->mk_path($parent,"<a href=\"".$this->mk_my_orb("change",array("id"=>$id))."\">Muuda filtrit</a> | <a href=\"".$this->mk_my_orb("output",array("id"=>$id))."\">Väljund</a> | Otsi");

			if (!$this->data["output_id"])
			{
				return "Väljundi tabelit pole veel määratud, vajuta 'väljund' lingile!";
			};
			global $search_filter_m;
			session_register("search_filter_m");
			$sfm=unserialize($search_filter_m);
			if /*(!is_array($sfm[$id]))*/ (1)//Kuna "enam-vähem" kõlab minumeelest ysna "jah" moodi siis otsib igakord uuesti
			{
				$this->__load_data();
				$this->__load_filter();
				
				$this->build_master_array();
				$this->sql_filter->set_data($this->master_array);

				
				$sfm[$id]=$this->perform_search();
				$search_filter_m=serialize($sfm);
			};

			//siin tuleb stuffi näidata
			classload("form_table");
			$this->ft=new form_table();
			
			if ($this->data["type"]=="chain")
			{
				// form_table rida 469
				$table_id=$this->data["output_id"];
				$chain_id=$this->data["target_id"];
				$this->ft->start_table($table_id,array("class" => "search_filter", "action" => "search",   "filter_id" => $id,"id"=>$id,"op_id" => $op_id));

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
//					echo "q = $q <br>";
					$this->ft->db_query($q);
					while ($row = $this->ft->db_next())
					{
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
//						echo "eid = ", $row["entry_id"], " ch_eid = ", $row["chain_entry_id"], "<br>";
						$this->ft->row_data($row);
					}
				}

				$this->ft->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
				$tbl = $this->ft->get_css();
				$tbl.="<form action='reforb.aw' method='POST'>\n";
				if ($this->ft->table["submit_top"])
				{
					$tbl.="<input type='submit' value='".$this->ft->table["submit_text"]."'>";
				}
				if ($this->ft->table["user_button_top"])
				{
					$tbl.="&nbsp;<input type='submit' value='".$this->ft->table["user_button_text"]."' onClick=\"window.location='".$this->ft->table["user_button_url"]."';return false;\">";
				}
				$tbl.=$this->ft->t->draw();

				if ($this->ft->table["submit_bottom"])
				{
					$tbl.="<input type='submit' value='".$this->ft->table["submit_text"]."'>";
				}
				if ($this->ft->table["user_button_bottom"])
				{
					$tbl.="&nbsp;<input type='submit' value='".$this->ft->table["user_button_text"]."' onClick=\"window.location='".$this->ft->table["user_button_url"]."';return false;\">";
				}
				$tbl.= $this->ft->mk_reforb("submit_table", array("return" => $this->ft->binhex($this->ft->mk_my_orb("show_entry", array("id" => $this->ft->id, "entry_id" => $entry_id, "op_id" => $output_id)))));
				$tbl.="</form>";
				$parse=$tbl;

			} else
			{
				//tavaline yhest formist otsimine oli oopis
				$table_id=$this->data["output_id"];
				$fid=$this->data["target_id"];
				$this->ft->start_table($table_id,array("class" => "search_filter", "action" => "search",   "filter_id" => $id,"id"=>$id,"op_id" => $op_id));

				$eids = $sfm[$id];
				$q="SELECT * FROM form_".$fid."_entries,objects WHERE objects.status != 0 and objects.oid = '$fid' AND form_".$fid."_entries.id in (".join(",",$eids).")";
				$this->ft->db_query($q);
				while ($row = $this->ft->db_next())
				{
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

				$this->ft->t->sort_by(array("field" => $GLOBALS["sortby"],"sorder" => $GLOBALS["sort_order"]));
				$tbl = $this->ft->get_css();
				$tbl.="<form action='reforb.aw' method='POST'>\n";
				if ($this->ft->table["submit_top"])
				{
					$tbl.="<input type='submit' value='".$this->ft->table["submit_text"]."'>";
				}
				if ($this->ft->table["user_button_top"])
				{
					$tbl.="&nbsp;<input type='submit' value='".$this->ft->table["user_button_text"]."' onClick=\"window.location='".$this->ft->table["user_button_url"]."';return false;\">";
				}
				$tbl.=$this->ft->t->draw();

				if ($this->ft->table["submit_bottom"])
				{
					$tbl.="<input type='submit' value='".$this->ft->table["submit_text"]."'>";
				}
				if ($this->ft->table["user_button_bottom"])
				{
					$tbl.="&nbsp;<input type='submit' value='".$this->ft->table["user_button_text"]."' onClick=\"window.location='".$this->ft->table["user_button_url"]."';return false;\">";
				}
				$tbl.= $this->ft->mk_reforb("submit_table", array("return" => $this->ft->binhex($this->ft->mk_my_orb("show_entry", array("id" => $this->ft->id, "entry_id" => $entry_id, "op_id" => $output_id)))));
				$tbl.="</form>";
				$parse=$tbl;

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

			$this->mk_path($parent,"<a href=\"".$this->mk_my_orb("change",array("id"=>$id))."\">Muuda filtrit</a> | Väljund | <a href=\"".$this->mk_my_orb("search",array("id"=>$id))."\">Otsi</a>");

			return $cparse;
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
			$sqlw=preg_replace("/%virtual.%täistekst = '(.+?)'/","($ftsstring)",$sqlw);
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
				$sql="SELECT DISTINCT(form_chain_entries.id) as id FROM form_chain_entries".join(" ",$leftjoin)." $sqlw AND form_chain_entries.chain_id='".$this->data["target_id"]."'";
//				echo("sql=$sql<br>");//dbg
			} else
			{
				$sql="SELECT id FROM ".join(",",array_keys($used_tables))." $sqlw";
//				echo("sql=$sql<br>");//dbg
			};

			$this->db_query($sql);
			$matches=array();
			while ($r=$this->db_next())
			{
				$matches[]=$r["id"];
			};
//			echo "<pre>", var_dump($matches),"</pre><br>";
			return $matches;
		}

		function __unserialize_fdata($dta)
		{
			// copy & paste from form_base.aw lines 85-105.
			// why the f*ck am i duplicating code?? 
			//i dont need those form_cell & form_element objects created, that's why
			if (substr($dta,0,14) == "<?xml version=")
			{
				classload("xml");
				$x = new xml;
				$arr = $x->xml_unserialize(array("source" => $dta));
			}
			else
			if (substr($dta,0,6) == "\$arr =")
			{
				// php serializer
				classload("php");
				$p = new php_serializer;
				$arr = $p->php_unserialize($dta);
			}
			else
			{
				$arr = unserialize($dta);
			}
			return $arr;
		}

/*		// Filter edit forwarders
		function orb_filter_edit_down($arr)
		{
			return $this->orb_filter_edit_move(array_merge($arr,array("delta"=>"1")));
		}

		function orb_filter_edit_up($arr)
		{
			return $this->orb_filter_edit_move(array_merge($arr,array("delta"=>"-1")));
		}

		//liigutab filtri tingimus yles/alla
		function orb_filter_edit_move($arr)
		{
			extract($arr);
			$this->id=$id;
			$arr["filter"]=$this->__load_filter();

			$this->filter=$this->sql_filter->do_filter_edit_move($arr);

			$this->__save_filter();
			return $this->mk_my_orb("change",array("id" => $id));
		}
*/
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
