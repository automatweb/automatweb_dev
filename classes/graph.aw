<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/graph.aw,v 2.16 2005/04/05 08:54:04 kristo Exp $
// graph.aw - graafikute haldamine

define("TYPE_PIE",0);
define("TYPE_LINE",1);
define("TYPE_BAR",2);
class graph extends aw_template
{
	//mis tyypi pilte me siin 6ieti loome
	var $outputimagetype;

	function graph()
	{
		$this->init("");
		$this->lc_load("graph","lc_graph");
		lc_load("definition");
		$this->outputimagetype = $this->cfg["image_type"];
	}
	
	////
	// !parsib aliast
	function parse_alias($args = array())
	{
		extract($args);
		return "<img src='".$this->mk_my_orb("show", array("id" => $alias["target"]), "graph",false,true)."'>";
	}

	
	//Uue graafiku lisamise pildi ettemanamine
	/**  
		
		@attrib name=new params=name default="0"
		
		@param parent required acl="add"
		@param alias_doc optional
		
		@returns
		
		
		@comment

	**/
	function add($arr)
	{
		extract($arr);
		$this->read_template("graphs/graph_new.tpl");
		$this->vars(array("reforb" => $this->mk_reforb("submit",array("parent" => $parent,"id" => 0, "alias_doc" => $alias_doc))));
		return $this->parse();
	}

	/** Uue graafikuga tegelemine  
		
		@attrib name=submit params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function base_add($ar)
	{
		extract($ar);
		$o = obj();
		$o->set_parent($parent);
		$o->set_class_id(CL_GRAPH);
		$o->set_name($name);
		$o->set_comment($comment);
		$o->set_status(STAT_ACTIVE);
		$id = $o->save();
		switch ($type) 
		{
			case TYPE_PIE:
				//default setup ja data
				$setup=array(
					"title" => t("Tiitel"),
					"title_col" => "000000",
					"bgcolor" => "aabbaa",
					"width" => "300",
					"height" => "200",
					"radius" => "50",
					"showlabels" => "on",
					"percentage" => "on"
				);
				$data=array(
					"labels" => "Mai,Juuni,Juuli,August,September",
					"data" => "5,13,18,7,3"
				);
				$sdata = $this->quote(serialize($data));
				$ssetup = $this->quote(serialize($setup));
				$q = "INSERT INTO graphs (id,setup,type,data,datasrc) VALUES ('$id','$ssetup','$type','$sdata','userdata')";
				$this->db_query($q);
				return $this->mk_orb("change",array("id"=>$id));
				break;

			case TYPE_BAR:
				return $this->mk_orb("gotbarline",array("id"=>$id,"type" => $type, "name" => $name));
				break;

			case TYPE_LINE:
				return $this->mk_orb("gotbarline",array("id"=>$id,"type" => $type, "name" => $name));
				break;
		}
	}
	
	function pie_conf($ar) 
	{
		extract($ar);
		$q="SELECT o.name,g.setup,g.data from graphs g,objects o WHERE oid=$id AND id=$id";
		$this->db_query($q);	
		$row=$this->db_next();
		$setup=unserialize($row["setup"]);
		$data=unserialize($row["data"]);
		($setup["showlabels"]=="on")?$showlabels ="CHECKED":$showlabels ="";
		($setup["percentage"]=="on")?$percentage ="CHECKED":$percentage ="";
		$this->read_template("graphs/pie.tpl");
		$this->vars(array(
			"name" => $row["name"],
			"title" => $setup["title"],
			"title_col" => $setup["title_col"],
			"bgcolor" => $setup["bgcolor"],
			"width" => $setup["width"],
			"height" => $setup["height"],
			"radius" => $setup["radius"],
			"showlabels" => $showlabels,
			"percentage" => $percentage,
			"data" => $data["data"],
			"labels" => $data["labels"],
			"id" => $id,
			"meta" => $this->mk_orb("meta",array("id"=>$id)),
			"prev" => $this->mk_orb("preview",array("id" => $id))
		));
		$this->vars(array("reforb" => $this->mk_reforb("savepie",array("id" => $id))));
		return $this->parse();
	}
	
	/**  
		
		@attrib name=savepie params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function save_pie($ar) 
	{
		extract($ar);
		$temp=array_keys($setup);
		if (!in_array("showlabels",$temp)) 
		{
			$setup["showlabel"]="off";
		} 
		else 
		if (!in_array("percentage",$temp)) 
		{
			$setup["percentage"]="off";
		}
		$sdata=$this->quote(serialize($data));
		$ssetup=$this->quote(serialize($setup));
		$q="UPDATE graphs SET data='$sdata',setup = '$ssetup' where id=$id";
		$this->db_query($q);
		return $this->mk_orb("change",array("id"=>$id));
	}
	
	/**  
		
		@attrib name=change params=name default="0"
		
		@param id required acl="edit;view"
		
		@returns
		
		
		@comment

	**/
	function configure($ar)
	{
		//extract($ar);
		$id=$ar["id"];
		$q="SELECT type from graphs WHERE id=$id";
		$this->db_query($q);
		$row=$this->db_next();
		switch ($row["type"]) 
		{
			case TYPE_PIE:			
				return $this->pie_conf($ar);
				break;
			case TYPE_BAR:
				return $this->line_bar_conf($ar);
				break;
			case TYPE_LINE:
				return $this->line_bar_conf($ar);
				break;
			default:
				break;
		}
	}

	function pie_show($id) 
	{
		$q="SELECT o.name,g.setup,g.data from graphs g,objects o WHERE oid=$id AND id=$id";
		$this->db_query($q);	
		$row=$this->db_next();
		$setup=unserialize($row["setup"]);
		$data=unserialize($row["data"]);
		($setup["percentage"]=="on")?$percentage=1:$percentage=0;
		($setup["showlabels"]=="on")?$showlabels=1:$showlabels=0;
		classload("tt_pie");
		$p = new PieGraph(2,5,1);
		$p->GraphBase($setup["width"],$setup["height"],$setup["bgcolor"]);
		$p->parsedata($data);
		$p->create($setup["radius"],$percentage,$showlabels);
		$p->title($setup["title"],$setup["title_col"]);
		header("Content-type: image/png");
		$str = 'Content-type: image/$this->outputimagetype';
		eval("header(\"$str\");");
		$create= "image".$this->outputimagetype;
		eval("$create(\$p->image);");
		die;				
	}

	/**  
		
		@attrib name=show params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function show($ar)
	{
		//extract($ar);
		$id=$ar["id"];
		$q="SELECT type from graphs WHERE id=$id";
		$this->db_query($q);
		$row=$this->db_next();
		switch ($row["type"]) 
		{
			case "999":
				//see 999 on miski backward compatble v2rk. kui se dakasutada mujla kui heavensi v6ib selle osa siit 2ra eemaldada
				//echo "PERSE"; die;
				$p=$this->show_line_bar($id);
				$str = 'Content-type: image/$this->outputimagetype';
				eval("header(\"$str\");");
				$create= "image".$this->outputimagetype;
				eval("$create(\$p);");
				imagedestroy($p);
				die;				
				break;
			case TYPE_PIE:			
				die($this->pie_show($id));
				break;
			case TYPE_BAR:
				$p=$this->show_line_bar($id);
				$str = 'Content-type: image/$this->outputimagetype';
				eval("header(\"$str\");");
				$create= "image".$this->outputimagetype;
				eval("$create(\$p);");
				imagedestroy($p);
				die;				
				break;
			case TYPE_LINE:
				$p=$this->show_line_bar($id);
				$str = 'Content-type: image/$this->outputimagetype';
				eval("header(\"$str\");");
				$create= "image".$this->outputimagetype;
				eval("$create(\$p);");
				imagedestroy($p);
				die;				
				break;
			default:
				break;
		}
	}

	/**  
		
		@attrib name=gotbarline params=name default="0"
		
		@param id required
		@param type required
		@param name required
		
		@returns
		
		
		@comment

	**/
	function line_bar_add($ar)
	{
		extract($ar);
		$q="INSERT INTO graphs (id,type) VALUES ('$id','$type')";		
		$this->db_query($q);			
		if ($type==TYPE_BAR) 
		{
			$this->read_template("graphs/bar_line.tpl");
			$this->vars(array("name"=>$name));
			$bar = $this->parse("BARG");
			$this->vars(array("reforb" => $this->mk_reforb("savedatasrc",array("id" => $id))));
			$this->vars(array("BARG" => $bar,"name" => $name));
			return $this->parse();
		} 
		else 
		{
			$this->read_template("graphs/bar_line.tpl");
			$line=$this->parse("LINEG");
			$this->vars(array("reforb" => $this->mk_reforb("savedatasrc",array("id" => $id))));
			$this->vars(array("LINEG" => $line,"name" => $name));
			return $this->parse();
		}
	}

	/**  
		
		@attrib name=savedatasrc params=name default="0"
		
		@param id required
		@param datasrc required
		
		@returns
		
		
		@comment

	**/
	function save_datasrc_bar_line($ar) 
	{
		extract($ar);
//			echo $id;echo $datasrc;echo $ycount;die;
		$setup=array(
			"title" => t("Tiitel"),
			"title_col" => "000000",
			"back_col" => "aabbaa",
			"width" => "300",
			"heigth" => "200",
			"frame" => "3",
			"inside" => "40",
			"y_axis_text" => "Arv",
			"y_axis_col" => "FF0000",
			"x_axis_text" => "Aeg",
			"x_axis_col" => "000000",
			"y_grid" => "6",
			"y_grid_col" => "0000FF",
			"show_y_val" => "on",
			"show_grid_val" => "on",
			"g" => "$g",
			"fir_col" => "00FF00",
			"sec_col" => "00FFFF"			
		);
		$ssetup=$this->quote(serialize($setup));
		$q="UPDATE graphs SET datasrc='$datasrc',ycount='$ycount',setup='$ssetup' WHERE id=$id";
		$this->db_query($q);
		return $this->mk_orb("change",array("id"=>$id));
	}

	//Graafiku konfimise jaoks, manab esile konfi page
	function line_bar_conf($ar)
	{	
		extract($ar);
		$q="SELECT objects.name, graphs.* FROM graphs, objects WHERE oid=$id AND id = $id";
		$this->db_query($q);
		$res=$this->db_next();
		extract($res);
		$setup=unserialize($setup);
		($setup["show_y_val"]=="on")?$y="CHECKED":$y="";
		($setup["show_grid_val"]=="on")?$g="CHECKED":$g="";

		$this->read_template("graphs/graph_config.tpl");
		$cdata="";
		if ($res["datasrc"]=="userdata") 
		{
			$this->vars(array("userdata" => $this->mk_orb("userdata",array("id" => $id))));
			$cdata=$this->parse("CHANGE");
		}	

		if (count($setup)>0)
		{
			$this->vars(array(
				"name" => "$name",
				"oid" => "$id",
				"title" => $setup["title"],
				"title_col" => "$setup[title_col]",
				"back_col" => "$setup[back_col]",
				"width" => "$setup[width]",
				"gr_height" => "$setup[heigth]",
				"frame" => "$setup[frame]",
				"inside" => "$setup[inside]",
				"y_axis_text" => "$setup[y_axis_text]",
				"y_axis_col" => "$setup[y_axis_col]",
				"x_axis_text" => "$setup[x_axis_text]",
				"x_axis_col" => "$setup[x_axis_col]",
				"y_grid" => "$setup[y_grid]",
				"y_grid_col" => "$setup[y_grid_col]",
				"y" => "$y",
				"g" => "$g",
				"fir_col" => "$setup[fir_col]",
				"sec_col" => "$setup[sec_col]",
				"prev" => $this->mk_orb("preview",array("id" => $id)),
				"reforb" => $this->mk_reforb("savelinebar",array("id" => $id)),
				"meta" => $this->mk_orb("meta",array("id"=>$id)),
				"CHANGE" => $cdata
			));
		}
		return $this->parse();
	}

	/**  
		
		@attrib name=savelinebar params=name default="0"
		
		
		@returns
		
		
		@comment

	**/
	function line_bar_save($ar)
	{
		extract($ar);
		$temp=array_keys($setup);
		if (!in_array("show_y_val",$temp)) 
		{
			$setup["show_y_val"]="off";
		} else 
		if (!in_array("show_grid_val",$temp)) 
		{
			$setup["show_grid_val"]="off";
		}
		$ssetup=$this->quote(serialize($setup));
		$q="UPDATE graphs SET setup = '$ssetup' where id=$id";
		$this->db_query($q);
		return $this->mk_orb("change",array("id"=>$id));
	}

	//Annab by default n2idatava graafikute listi
	/**  
		
		@attrib name=list params=name default="0"
		
		@param parent required
		
		@returns
		
		
		@comment

	**/
	function glist($ar)
	{	
		($ar["parent"]=="0")?$asdf="":$asdf=" AND parent=$parent";
		$ci=CL_GRAPH;
		$q="SELECT o.oid,o.name,o.comment,o.createdby from objects o where class_id='$ci'$asdf";
		$this->db_query($q);
		$this->read_template("graphs/graph_show.tpl");
		$s_row=0;$sl="";$cdd="";
		while ($row=$this->db_next())
		{
			$this->vars(array(
				"name" => $row["name"], 
				"comment" => $row["comment"],
				"id" => $row["oid"],
				"change" => $this->mk_orb("change",array("id" => $row["oid"])),
				"meta" => $this->mk_orb("meta",array("id" => $row["oid"])),
				"preview" => $this->mk_orb("preview",array("id" => $row["oid"])),
				"add" => $this->mk_orb("new",array("parent" => $parent))
			));
			$sub_html .= $this->parse("LINE");
			$this->vars(array("row" => $s_row));
			$sl.=$this->parse("SELLINE");
			$s_row++;
			$cdd.= $this->parse("DEL_LINE");
		}
		$this->vars(array("LINE" => $sub_html));
		return $this->parse();
	}
		
	//Kustutab graafiku(d)
	/**  
		
		@attrib name=delete params=name default="0"
		
		@param id required
		@param parent required
		
		@returns
		
		
		@comment

	**/
	function delete_graph($ar)
	{
		extract($ar);
		$tmp = obj($id);
		$tmp->delete();
		header("Location:orb.".$this->cfg["ext"]."?class=menuedit&action=obj_list&parent=$parent");
	}
	//Näitab preview pilti, sinna sisse imetakse templatest ka graph->show() meetodiga pilt ise
	/**  
		
		@attrib name=preview params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function preview($id)
	{
		//for .. aah. just a hack
		if (is_array($id)) $id=$id["id"];
		$this->read_template("graphs/graph_prev.tpl");
		$this->vars(array(
			"id" => $id,
			"ext" => $ext,
			"fine" =>  md5(uniqid(rand()))
		));
		return $this->parse();
	}

	//Kõigetegija bari ja line jaoks, peamiselt teeb valmis pildi ja returnib image pointeri.
	function show_line_bar($id)
	{
		$gm=get_instance("graph_modules");
		if ($id)
		{
			$q="SELECT setup,datasrc,type FROM graphs where id=$id";
			$this->db_query($q);
			$row=$this->db_next();
			$setup=unserialize($row["setup"]);
			$datasrc=$row["datasrc"];
			switch($datasrc)
			{
				case "stats_syslog":
					$data=$gm->get_syslog();
					$gr_type="syslog";
					break;
				case "stats_all":
					$data=$gm->get_10_stats();
					$gr_type="stats_all";
					break;
				case "stats_rows":
						$data=$gm->get_stats();
						$gr_type="rows";
					break;
				case "stats_bytes":
						$data=$gm->get_stats();
/*								while (list($ke,$va)=each($data[ybytes]))
							{
								echo "$ke";echo " = $va<br />";
							}	
							exit;
*/						$gr_type="bytes";									
					break;
				case "stats_words":
					$data=$gm->get_stats();
					$gr_type="words";
					break;
				case "userdata":
					$data=$gm->get_user_data($id);
					$gr_type="samdelaju";
					break;
				default:
					break;
			}

			/*
			* See on t2htis koht tegelikult, inetu, kyll, aga t2htis sest siin vaaadakse millen tuleb graafik meil on, kas * * tulp v6i joon.
			*
			*/
			switch ($row["type"]) 
			{
				case TYPE_BAR:
					classload("tt_bar");
					$type="bar";
					$Im = new BarGraph(1,$setup["inside"],$setup["frame"]);
					break;
				case TYPE_LINE:
					classload("tt_line");
					$type="line";
					$Im = new LineGraph(1,$setup["inside"],$setup["frame"]);
					break;
				default:
					break;
			}
			/*
			* Joonistame Base valmis, kus on borderid ja asjad k6ik paigas
			*/
			$Im->GraphBase($setup["width"],$setup["heigth"],$setup["back_col"]);
	
			/*
			*Parsime data vastavalt graafiku datasourcule, krt, tegelt peaks mingi standardi tegema et igayhe jaoks eraldi *"#¤% jebunni poleks
			*/
			switch($gr_type) 
			{
				case "syslog":
					$Im->parseData($data["xdata"],$data["ydata"]);
					break;
				case "bytes":
					$Im->parseData($data["xdata"],$data["ybytes"]);
					break;
				case "rows":
					$Im->parseData($data["xdata"],$data["yrows"]);
					break;
				case "words":
					$Im->parseData($data["xdata"],$data["ywords"]);
					break;
				case "stats_all":
					$Im->parseData($data["xdata"],$data["ydata"]);
					break;
				case "samdelaju":
					$Im->parseData($data["xdata"],$data["ydata"]);
					break;
				default:
					break;
			}

			//Draw Title
			$Im->title($setup["title"],$setup["title_col"]);
			//Draw text on x axis
			$Im->xaxis($data["xdata"],$setup["x_axis_text"],$setup["x_axis_col"]);
			//Draw Grid
			($setup["show_grid_val"]=="on")?$drawg=TRUE:$drawg=FALSE;
			$Im->grid($setup["y_grid"],$drawg,$setup["y_grid_col"]);

			($setup["show_y_val"]=="on")?$drawy=TRUE:$drawy=FALSE;
			$Im->yaxis($drawy,$setup["y_axis_text"],$setup["y_axis_col"],$setup["y_grid_col"]);

			//Draw data lines
			switch($gr_type) 
			{
				case "syslog":
					if ($type=="bar")
					{
						$Im->makeBar($data["ydata"],"00ff00");
					}
					else
					{
						$Im->makeLine($data["ydata"], $setup["fir_col"]);			
					}
					break;
				case "bytes":
					$Im->makeLine($data["ybytes"], $setup["fir_col"]);
					break;
				case "rows":
					$Im->makeLine($data["yrows"], $setup["fir_col"]);
					break;
				case "words":
					if ($type=="bar")
					{
						$Im->makeBar($data["ywords"],$data["ycol"]);
					}
					else
					{
						$Im->makeLine($data["ywords"], $setup["fir_col"]);
					}
					break;
				case "stats_all":
					if ($type=="bar")
					{
						$Im->makeBar($data["ydata"],$data["ycol"]);
					}
					else
					{
						for($i=0;$i<count($data["ydata"]);$i++)
						{
							$Im->makeLine($data["ydata"]["ydata_".$i], $data["ycol"]["ycol_".$i]);
						}
					}
					break;
				case "samdelaju":
					if ($type=="bar")
					{
						$Im->makeBar($data["ydata"],$data["ycol"]);
					} 
					else
					{
						for($i=0;$i<count($data["ydata"]);$i++)
						{
							$Im->makeLine($data["ydata"]["ydata_".$i], $data["ycol"]["ycol_".$i]);
						}
					}
				default:
					break;
			
			}
			$image=$Im->getImage();
			return $image;
		}
	}
	//Kasutaja data sisestamiseks vajaliku template parsimine jne
	/**  
		
		@attrib name=userdata params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function insert_data($ar)
	{
		$id=$ar["id"];
		$q="SELECT o.name,g.datasrc,g.data,g.setup,g.ycount FROM graphs g, objects o WHERE o.oid=$id AND g.id=$id";
		$this->db_query($q);
		$row=$this->db_next();
		$datasrc=$row["datasrc"];
		$data=unserialize($row["data"]);
		$setup=unserialize($row["setup"]);
		if ($datasrc=="userdata")
		{	
			$this->read_template("graphs/graph_data.tpl");
			$y=$row["ycount"];
			$rw="";
			$f_nr=0;
			for($i=0;$i<$y;$i++)
			{
				$cur="y".$i;
				$curc="yc".$i;						
				$this->vars(array(
					"y_nr" => $i,
					"f_nr" => ($f_nr=$f_nr+2),
					"ydata" => "$data[$cur]",
					"ycolor" => "$data[$curc]"
				));
				$rw .= $this->parse("LINE");
			}
			$this->vars(array(
				"LINE" => $rw,
				"oid" => $id,
				"id" => $id,
				"name" => $row["name"],
				"xdata" => "$data[x]",
				"conf" => $this->mk_orb("change",array("id" => $id)),
				"preview" => $this->mk_orb("preview", array("id" => $id)),
				"upload" => $this->mk_reforb("upload",array("id" => $id))
				));
			$this->vars(array("reforb" => $this->mk_reforb("savedata",array("id" => $id))));
			return $this->parse();
		} 
		else 
		{
			$this->raise_error(ERR_GRAPH_IMP,LC_GRAPH_IMP_INS,TRUE);
		}
	}

	//Salvestab kasutaja andmed
	/**  
		
		@attrib name=savedata params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function save_userdata($ar)
	{
		extract($ar);
		$tmp = obj($id);
		$tmp->save();
		$dt=$this->quote(serialize($arr));
		$q="UPDATE graphs SET data = '$dt' WHERE id=$id";
		$this->db_query($q);
		return $this->mk_orb("userdata",array("id"=>$id));
	}
	
	/**  
		
		@attrib name=meta params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function show_meta($ar)
	{
		extract($ar);
		$q="SELECT o.name,o.comment,g.datasrc,g.ycount,g.type FROM graphs g, objects o WHERE o.oid=$id AND g.id=$id";
		$this->db_query($q);
		$row=$this->db_next();
		$ar_type=array("Pie","Line","Bar");
		$type=$row["type"];
		$ar_datasrc=array(
			"userdata" => LC_GRAPH_USERS_DATA,
			"stats_rows" => LC_GRAPH_STAT_BY_ROW,
			"stats_bytes" => LC_GRAPH_STAT_BY_BITE,
			"stats_words" => LC_GRAPH_STAT_BY_WORD
		);
		$datasrc=$row["datasrc"];
		$this->read_template("graphs/graph_meta.tpl");
		$this->vars(array(
			"name" => $row["name"],
			"comment" => $row["comment"],
			"type" => $ar_type[$type],
			"andmed" => $ar_datasrc[$datasrc],
			"prev" => $this->mk_orb("preview",array("id" => $id)),
			"reforb" => $this->mk_reforb("savemeta",array("id" => $id)),
			"conf" => $this->mk_orb("change",array("id" => $id)),
		));
		return $this->parse();
	}
	
	/**  
		
		@attrib name=savemeta params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function save_meta($ar)
	{
		extract($ar);
		$tmp = obj($id);
		$tmp->set_name($name);
		$tmp->set_comment($comment);
		$tmp->save();
		return $this->mk_orb("meta",array("id"=>$id));
	}

	function upload_csv($ar) 
	{
		extract($ar);
		$this->read_template("graphs/file.tpl");
		return $this->parse();
	}
	
	/**  
		
		@attrib name=upload params=name default="0"
		
		@param id required
		
		@returns
		
		
		@comment

	**/
	function handle_upload($ar)
	{
		extract($ar);
		global $userfile;
		if (is_uploaded_file($userfile)) 
		{
			if ($HTTP_POST_FILES['userfile']['size']>8000000)	
			{
				echo "Liiga suur fail.";							
			} 
			else 
			{
				//move_uploaded_file($userfile, "/Temp/files/".$HTTP_POST_FILES['userfile']['name']);
				$fp=fopen($userfile,"r");
				$i=0;
				while ($data = fgetcsv ($fp, 1000, ",")) 
				{
					$blaah=join(",",$data);
					$ii=$i-1;
					$cur="y".$ii;
					$curc="yc".$ii;
					if ($i==0) 
					{
						$temp["x"]=$blaah;
					} 
					else 
					{
						$temp[$cur]=$blaah;
						$temp[$curc]="000000";					
					}
					$ycount=$ii+1;
					$i++;
				}
/*							echo $temp[x];echo "<br />";
					for($i=0;$i<$ycount;$i++) {
						$cur="y".$i;
						$curc="yc".$i;						
						print("$temp[$cur] $temp[$curc]<br />");
					}
*/				fclose ($fp);
//								echo "Ajee.. done... edukas appload";
					}
			} 
			else 
			{
				echo "Mingi jura... '$userfile'.";
			}
			$dt=$this->quote(serialize($temp));
			$q="UPDATE graphs SET data = '$dt',ycount = '$ycount' WHERE id=$id";
			$this->db_query($q);
			return $this->mk_orb("userdata",array("id"=>$id));
//					die;
	}

	//miski debug meetod, jumal teab milleks ta parasjagu hea on, a las ta olla, äki läheb vaja veel ;)
	function tryitout($id)
	{
		$gm=get_instance("graph_modules");
		$data=$gm->get_stats();
		while(list(,$v) = each($data["yrows"])) 
		{
			if (is_array($v))
			{
				while(list($ke,$val) = each($v)) 
				{
					echo "$ke = $val<br />";						
					if (is_array($val))
					{					
						while(list($kee,$va) = each($val)) 
						{
							//echo "$kee = $va<br />";						
						}
					}
				}
			} 
			else 
			{
				echo "mh1 = $v<br />";						
			}
		}
		exit;
	}
};
?>
