<?php
class html_import extends aw_template
{
	////////////////////////////////////
	// the next functions are REQUIRED for all classes that can be added from menu editor interface
	////////////////////////////////////

	function html_import()
	{
		// change this to the folder under the templates folder, where this classes templates will be 
		$this->init("html_import");
	}

	function getfile($file)
	{
		$document=@implode("",@file($file));

		$search = array ("'\t'", // strip tabs
		                 "'\n'", // strip linebrakes
		                 "'\r'",                 
		);
		$replace = array ("",
		                  "",
		                  "",
		);
		$text = preg_replace ($search, $replace, $document);
	
		return $text;
	}

	////
	// ! get everything between body tags
	function getbody($html,$val="body")
	{
		preg_match("'<body[^>]*?>(.*)?<\/body>'si", $html, $matches);
		return $matches[1];
	}

	function gettable($html,$val="",$begin,$end)
	{
		preg_match("'</tstart $begin>(<table[^>]*?>(.*)<\/table>)<\/tend $end>'si", $html, $matches);
		return $matches[1];
	}

	////
	// ! list fields of existing sql table
	// table - tabele name will be prefixed by "html_import_"
/*
	function list_db_fields($table,$all=false)
	{
		$result = mysql_query("SELECT * FROM html_import_$table limit 1");
		$fields = mysql_num_fields($result);
		for ($i=0; $i < $fields; $i++)
		{
			$type  = mysql_field_type($result, $i);
			$name  = mysql_field_name($result, $i);
			$len   = mysql_field_len($result, $i);
			$flags = mysql_field_flags($result, $i);
			$str.=$type." ".$name." ".$len." ".$flags."\n";
		}
		return $str;
	}

*/


	function drop_table($table)
	{
		return mysql_query("DROP table html_import_$table");
	}

	function show_create_table($table)
	{
		$res=@mysql_query("show create table html_import_$table");
		return @mysql_result($res,0,1);
	}


	////
	// ! crate sql table
	// tablename - NB! "hmlt_import_" will be prefixed
	// ruul	- no description right now sry
	// sql_ruul - -..-
	// add_id - set true if ID column is required
	// create - set true if you wanna make the actual tabel instead of just gettin' the query
	function mk_my_table($tablename,$ruul,$sql_ruul,$add_id,$create=false)
	{
		if (is_array($ruul))
		foreach($ruul as $key => $val)
		{
			if ($ruul[$key]["mk_field"])
			{
				switch($sql_ruul[$key]["type"])
				{
					case "int": 
						$tp="int(".($sql_ruul[$key]["size"]?$sql_ruul[$key]["size"]:"11").")";
					break;
					case "varchar": 
						$tp="varchar(".($sql_ruul[$key]["size"]?$sql_ruul[$key]["size"]:"50").")";
					break;
					default: 
						$tp="text";
				}
				$tp.=$sql_ruul[$key]["unique"]?" unique":"";
				$cols[]=$val["mk_field"]." ".$tp;
			}

		}
		$cols=$this->field_list($cols);
		if ($add_id)
		{
			$cols="\nid int primary key auto_increment,".$cols;
		}
		$q="create table html_import_".$tablename." ($cols \n)";
		if ($create)
		{
			$this->db_query($q);
		}
//echo $q;
		return str_replace(",",",\n",$q);
	}


	function field_list($arr)
	{
		if (is_array($arr))
		foreach($arr as $key =>$val)
		{
			if ($val) 
				$fi[$key]=$val;
		}
		else return 0;
		return @implode(",",$fi);
	}

	
	////
	// ! will perform the import process
	function tiri($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		classload("linklist");				// selle linklistis oleva meetodi peaks oopis coressse panema
		$path=$ob["meta"]["source_path"];
		$files=linklist::get_templates($path);
		if (!$files) 
			{die("could not get files in $path");}
		//tabeli jrk nr
		$starts=$ob["meta"]["starts"];
		$what=$ob["meta"]["ruul"];

		foreach($what as $key => $val)
		{
			$cells[]=$val["mk_field"];
		}
		$fields=$this->field_list($cells);
		echo "<html><body><pre>";

		if (6<7){
			foreach($files as $key=>$val)
			{
				$this->pinu=array();//need peavad globaalselt iga kord nulli minema
				$this->tblnr=0;
				$file="$path/$val";
				$source=$this->getfile($file);
				if (is_int(strpos($source,$ob["meta"]["match"])))
				{
					if($ob["meta"]["single"])
					{
						unset($tbl);
						foreach($what as $key=>$val)
						{
							if($val["mk_field"])
							{
								$begin=str_replace("/", "\/", $val["begin"]);
								$end=str_replace("/", "\/", $val["end"]);
								preg_match("/($begin)(.*)($end)/sU", $source, $mm);
								//striptags?
								$tbl[1][]=$mm[2];
							}
						}

					}				
					else
					{
						$source=preg_replace("/(<([\/]?)table[^>]*>)/ei", '\$this->caunt($link,"\\2","$starts")', $source);
						$source=$this->gettable($source,"",$starts,$starts);
						$tbl=$this->table_to_array($source,$cells);
					}
//print_r($tbl);

					switch ($ob["meta"]["output"])
					{
						case "mk_my_table":
							$total+=$this->db_insert("html_import_".$ob["meta"]["mk_my_table"],$fields,$tbl,true,true);
						break;

						case "mk_my_query":
							$total+=$this->db_insert("html_import_".$ob["meta"]["mk_my_table"],$fields,$tbl,true);
						break;
						
					}
//					echo "imported data from $file<br>";
				}
				else
				{
//					echo "could not import data from $file !! string comparision failed!!<br>";
				}
			}
		}
		echo "total: ".(int)$total."\n\r";
		if ($ob["meta"]["output"]=="mk_my_table")
			echo "kui errorit ei tekkinud, siis andmed läksid vist baasi";
		if ($ob["meta"]["output"]=="mk_my_query")
			echo "";
		echo "</pre></body></html>";
		die("\n\rfinished");
	}



	function ruul_test($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		$examples=explode("\n",$ob["meta"]["example"]);		
		
		$source=$this->getfile($examples[$f]);
		$ruuls=$ob["meta"]["ruul"];
		if ($ruuls)
		foreach($ruuls as $key=>$val)
		{
			$begin=str_replace("/", "\/", $val["begin"]);
			$end=str_replace("/", "\/", $val["end"]);
			if($begin && $end)
			{	
				$source=preg_replace("/($begin)(.*)($end)/sU", '\\1<span title="'.$val["desc"].'" style="background-color:#ffaaaa">\\2</span>\\3', $source);
			}
		}
		echo $source;
		die();
	}


//teeme pinu et siis kui mingi tabel on tabeli sees, algus ja lõpu id oleks õige
	function caunt($link,$end,$aktiivne="")
	{
		if ($end)
		{
			$m=array_pop($this->pinu) ;
			$html="</table></tend $m>";
			return $html;
		}
		else
		{	
			$this->tblnr++;
			$t=$this->tblnr;
			$this->pinu[]=$t;
			$link.="&starts=$t";
			$color=$aktiivne?"#ff9999":$this->color[$t%6];
			$html="<a href=$link>[andmed vaid sellest tabelist?]</a><br></tstart $t><table border=1 id=$t bgcolor=$color>";
			return $html;
		}
	}

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	// id - the id of the object to change
	// return_url - optional, if set, "back" link should point to it
	function sqlconf($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda HTML import");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda html_import");
		}
		$this->read_template("sqlconf.tpl");
		$tyyp=array("varchar"=>"varchar","text"=>"text","int"=>"int");
//		$link=$this->mk_my_orb("sqlconf", array("id" => $id, "return_url" => urlencode($return_url)));

		$ruul=$ob["meta"]["ruul"];
//print_r(	
		$sql_ruul=$ob["meta"]["sql_ruul"];
				foreach($ruul as $key=>$val)
				{
					if (($ruul[$key]["end"]||$ruul[$key]["begin"])&&$ruul[$key]["mk_field"])
					{	$this->vars(array(
						"ruul"=>$key,
						"mis"=>"sql_ruul",
						"mk_field"=>$ruul[$key]["mk_field"],
						"desc"=>$ruul[$key]["desc"],
						"unique"=>checked($sql_ruul[$key]["unique"]),
						"type"=>$this->picker($sql_ruul[$key]["type"],$tyyp),
						"strip_html"=>checked($sql_ruul[$key]["strip_html"]),
						"size"=>(int)$sql_ruul[$key]["size"],
//						""=>$sql_ruul[$key][""],
					));
					$ruulid.=$this->parse("ruul");
					}
				}
		$html=$this->getfile($ob["meta"]["example"]);

		if (is_int(@strpos($html,$ob["meta"]["match"]))) //&& create lause õige && veerge on defineeritud
		{
			$import_link="<a href=".$this->mk_my_orb("tiri",array("id"=>$id))." target=_blank>impordi</a>";
		}
		else
		{
			$import_link="unikaalne string puudu või vale";
		}
		if ($drop_table)
		{
		echo "dropping";
			$this->drop_table($ob["meta"]["mk_my_table"]);
		}


		$this->vars(array(
			"gogo"=> $import_link,
			"create_table"=>"<a href=\"".$this->mk_my_orb("sqlconf", array("id" => $id, "create_table" => "yes", "return_url" => urlencode($return_url)))."\">CREATE</a>",
			"drop_table"=>"<a href=\"".$this->mk_my_orb("sqlconf", array("id" => $id, "drop_table" => "yes", "return_url" => urlencode($return_url)))."\">DROP</a>",
			"add_id"=>checked($ob["meta"]["add_id"]),
			"mk_table"=> $this->mk_my_table($ob["meta"]["mk_my_table"],$ruul,$sql_ruul,$ob["meta"]["add_id"],$create_table),
			"got_table"=>$this->show_create_table($ob["meta"]["mk_my_table"]),
			"ruul"=>$ruulid,
			"toolbar" => $this->my_toolbar(array("id"=>$id)),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "do"=>"sqlconf", "return_url" => urlencode($return_url)))
		));
		return $this->parse();
	}
	
	

	////
	// !this gets called when the user clicks on change object 
	// parameters:
	// id - the id of the object to change
	// return_url - optional, if set, "back" link should point to it
	function change($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda HTML import");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda html_import");
		}
		$this->read_template("change.tpl");

		$link=$this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));


		if (is_int(@strpos($html,$ob["meta"]["match"])))
		{
			$import_link="<a href=".$this->mk_my_orb("tiri",array("id"=>$id))." target=_blank>impordi</a>";
		}
		else
		{
			$import_link="unikaalne string confis puudu või vale";
		}

		$this->vars(array(
			"example"=>$ob["meta"]["example"],
			"singleon"=>checked($ob["meta"]["single"]),
			"singleoff"=>checked(!$ob["meta"]["single"]),
			"is_my_table"=> checked($ob["meta"]["output"]=="mk_my_table"),
			"is_my_csv"=> checked($ob["meta"]["output"]=="mk_my_csv"),
			"is_my_query"=> checked($ob["meta"]["output"]=="mk_my_query"),
			"is_my_xml"=> checked($ob["meta"]["output"]=="mk_my_xml"),
			"mk_my_table"=> $ob["meta"]["mk_my_table"],
			"mk_my_csv"=> $ob["meta"]["mk_my_csv"],
			"mk_my_query"=> $ob["meta"]["mk_my_query"],
			"mk_my_xml"=> $ob["meta"]["mk_my_xml"],
			"output"=>$output,
			"name"=>$ob["name"],
			"source_path"=>$ob["meta"]["source_path"],//$this->picker(0,array("/home/axel/public_html/html"=>"/home/axel/public_html/html")),
			"comment"=>$ob["comment"],
			"toolbar" => $this->my_toolbar(array("id"=>$id,"sid"=>$ob["meta"]["sid"])),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "do"=>"change", "return_url" => urlencode($return_url)))
		));
		return $this->parse();
	}
	
	
	////
	// !this gets called when the user clicks on change object 
	// parameters:
	// id - the id of the object to change
	// return_url - optional, if set, "back" link should point to it
	function conf($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Muuda HTML import");
		}
		else
		{
			$this->mk_path($ob["parent"], "Muuda html_import");
		}
		$this->read_template("conf.tpl");

		$link=$this->mk_my_orb("conf", array("id" => $id, "return_url" => urlencode($return_url)));

		$examples=explode("\n",$ob["meta"]["example"]);
		foreach ($examples as $key => $val)
		{
			$exmpl.="testi :<a href=".$this->mk_my_orb("ruul_test",array("id"=>$id, "f"=>$key))." target=_blank>$val</a><br />";
		}

		if ($reset)
		{
			$starts="";
		}	
		else
		{
			$starts=$starts?$starts:$ob["meta"]["starts"];
		}
		$this->set_object_metadata(array("oid" => $id,"key" => "starts","value" => $starts,));

		$what=$ob["meta"]["ruul"];		
		$html=$this->getfile($examples[0]);

		if ($ob["meta"]["example"])
		{
			if ($ob["meta"]["single"])
			{
//$what=$ob["meta"]["ruul"];
				$rule=$what;
				$ruul=array();
				$i=0;$j=1;
				while(($rule["ruul_".++$i]))		
				{
					if (($rule["ruul_".$i]["begin"]!="")||($rule["ruul_".$i]["end"]!=""))
					{
						$rule["ruul_".$j]=$rule["ruul_".$i];
						$ruul["ruul_".$j]="ruul_".$j;
						$j++;
					}
				}
				$ruul["ruul_".$j]="ruul_".$j;
				$rule["ruul_".$j]=array();
	
				foreach($ruul as $key=>$val)
				{
					$this->vars(array(
						"ruul"=>$key,
						"mis"=>"ruul",
						"mk_field"=>$rule[$key]["mk_field"],
						"desc"=>$rule[$key]["desc"],
						"begin"=>$rule[$key]["begin"],
						"end"=>$rule[$key]["end"],
					));
					$ruulid.=$this->parse("ruul");
				}
				$ruulid=$this->parse("ruulbar").$ruulid;
//				$source=$this->ruultest($html,$ob["meta"]["ruul"]);
			}
			if (!$ob["meta"]["single"])
			{
				$source=preg_replace("/(<([\/]?)table[^>]*>)/ei", '\$this->caunt($link,"\\2","$starts")', $this->getbody($html));
				if($starts)//&&$ends)
				{
//					$source=preg_replace("/(<([\/]?)table[^>]*>)/ei", '\$this->caunt($link,"\\2","$starts")', $this->getbody($html));
					$source=$this->gettable($source,"",$starts,$starts);
					$tmp=spliti("<tr", $source);		//leiame veergude arvu
					$cnt=preg_match_all("/(<td)/i", $tmp[1], $null); 
	//				print_r($source);
//					$tbl=$this->table_to_array($source,);
					$this->ruul=$what;
					$source=preg_replace("/(<td[^\!>]*>)/ie", '\$this->sel_field("\\1")', $source,$cnt);
				}
			}
		}
/*
		$this->vars(array(
			"ruul"=>"eraldajad",
			"mis"=>"separator",
			"begin"=>$ob["meta"]["separator"]["eraldajad"]["begin"],
			"end"=>$ob["meta"]["separator"]["eraldajad"]["end"],
			"fields"=>"",
		));
		$separators=$this->parse("ruul");*/


//$source=preg_replace("/(<input [^>]*)(name\=)([^>]*>)/i", '\1 nome=\3', $source);//et formi elmendid ei hakkak AW-d segama
$source=preg_replace("/<input.*>/Ui", '', $source);
$source=preg_replace("/<script.*<\/script>/Ui", '\1 nome=\3', $source);
$source=preg_replace("/<form [^>]*>/i", '', $source);

		$this->vars(array(
			"match"=>$ob["meta"]["match"],
			"ruul"=>$ruulid,
			"reset"=> "<a href='$link&reset=1'>reset</a>",
			"source"=>"<textarea cols=95 rows=15>".$html."</textarea><br /><br />".$source,
			"ruul_test"=>$exmpl,
			"toolbar" => $this->my_toolbar(array("id"=>$id,"sid"=>$ob["meta"]["sid"])),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "starts"=>$starts, "do"=>"conf", "return_url" => urlencode($return_url)))
		));
		return $this->parse();
	}


	function sel_field($more="")
	{
		static $fn,$tbl;if(!$fn)$fn=0;
		
		$this->vars(array(
			"mis"=>"ruul",
			"ruul"=>$fn,
			"mk_field"=>$this->ruul[$fn]["mk_field"],
			"desc"=>$this->ruul[$fn]["desc"],
		));
		$fn++;
		return	$more.$this->parse("fields");
	
	}

	function table_to_array($table,$gets)
	{
		$html=preg_replace("/<\/td>/i", "#%#", $table);
		$html=preg_replace("/<td[^>]*?>/i", "", $html);
		$html=preg_replace("/<\/tr>/i", "#&#", $html);
		$html=preg_replace("/<tr[^>]*?>/i", "", $html);
		$html=strip_tags($html);
		$rows=explode("#&#",trim($html));
		foreach($rows as $val)
		{
			$row=trim($val);
			if ($row)
			{
				$cells=explode("#%#",$row);
				$dat=array();
				foreach($cells as $key=>$cell)
				{
					if ($gets[$key])
					$dat[]=trim($cell);

				}
				$tbl[]=$dat;
			}
		}
		return $tbl;
	}

	////
	//! get sql output
	// table - sql table name
	// fields - list of fields eg "id, firstname, lastname"
	// data - array(
	//		array("firstnames","lastnames"),
	//		array("William","ClinTon"),
	//		array("George","Bush"),
	//		)
	// first_row - elliminate firs row 
	// insert - actually insert data into the sql base
	//
	function db_insert($table,$fields,$data,$first_row=true,$insert=false)
	{
		if ($first_row) //esimesel real on ilmselt kirjeldused
		{
			unset($data[0]);
		}
//echo $fields;
		$query="insert into $table($fields) values ";

		foreach($data as $key=> $val)
		{
			$got=0;
				foreach($val as $key => $val)
				{
					$got=$val?"yes":$got;
					$va[$key]="'".addslashes(trim(strip_tags($val)))."'";
				}

				if($got)
				{
					$value="(".implode(",",$va).")";
					$q=$query.$value.";";
					$total++;
				}
				else
				{
//					echo "tühi kirje!<br>";
				}

			if ($insert)
			{
				echo $q."\n";
				$this->db_query($q);
			}
			else
			{
				echo $q."\n";
			}

		}
		return $total;
	}


	////
	// ! get data csv output
	// data - array(array(1,2,3),array(2,2,3)...)
	// first_row elliminate first row
	function csv_data($data,$first_row=false)
	{
		if ($first_row) //esimesel real on ilmselt kirjeldused
		{
			unset($data[0]);
		}
//echo $fields;
		foreach($data as $key=> $val)
		{
			$got=0;

			
				foreach($val as $val)
				{
					$got=$val?"yes":$got;
					$values[]="\"".addslaches($val)."\"";
				}
				if($got)
				{
					$csv[]=implode(";",$values).";";
					$total++;
				}
				else
				{
					echo "tühi kirje!<br>";
				}
		}
		return implode("\n\r",$csv);
	}





	////
	// !this gets called when the user submits the object's form
	// parameters:
	// id - if set, object will be changed, if not set, new object will be created
	function submit($arr)
	{
		extract($arr);
		if ($id)
		{

		if ($do=="conf"){
			$this->upd_object(array(
				"oid" => $id,
				"metadata" => array(
					"starts"=>$starts,
					"ruul"=>$ruul,
					"match"=>$match,
					)
			));
		}
		elseif($do=="sqlconf"){
			$this->upd_object(array(
				"oid" => $id,
				"metadata" => array(
					"add_id"=>$add_id,
					"sql_ruul"=>$sql_ruul,
					)
				));
		}
		else{
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => array(
					"single"=>$single,
					"source_path"=>$source_path,
					"example"=>$example,
					"db_table"=>$db_table,
					"mk_my_table"=>$mk_my_table,
					"mk_my_query"=>$mk_my_query,
					"mk_my_csv"=>$mk_my_csv,
					"mk_my_xml"=>$mk_my_xml,
					"output"=>$output,
//					"separator"=>$separator,
					)
				));
			$do="change";
		}


		}
		else
		{
			$id = $this->new_object(array(
				"parent" => $parent,
				"name" => $name,
				"class_id" => CL_HTML_IMPORT,
				"comment" => $comment,
				"metadata" => array(
					"source_path"=>$source_path,
					"example"=>$example,
				)
			));
			$do="change";
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}
		return $this->mk_my_orb($do, array("id" => $id, "return_url" => urlencode($return_url)));
	}


	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	// alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($args)
	{
		extract($args);
			
		return $this->show(array("id" => $alias["target"]));
	}


	////////////////////////////////////
	// object persistance functions - used when copying/pasting object
	// if the object does not support copy/paste, don't define these functions
	////////////////////////////////////

	////
	// !this should create a string representation of the object
	// parameters
	// oid - object's id
	function _serialize($arr)
	{
		extract($arr);
		$ob = $this->get_object($oid);
		return aw_serialize($row);
	}

	////
	// !this should create an object from a string created by the _serialize() function
	// parameters
	// str - the string
	// parent - the folder where the new object should be created
	function _unserialize($arr)
	{
		extract($arr);
		$row = unserialize($str);
		$row["parent"] = $parent;
		$id = $this->new_object($row);
		return true;
	}

	////
	// !this is not required 99% of the time, but you can override adding aliases to documents - when the user clicks
	// on "pick this" from the aliasmanager "add existing object" list and this function exists in the class, then it will be called
	// parameters
	//   id - the object to which the alias is added
	//   alias - id of the object to add as alias
	function addalias($arr)
	{
		extract($arr);
		// this is the default implementation, don't include this function if you're not gonna change it
		$this->add_alias($id,$alias);
		header("Location: ".$this->mk_my_orb("list_aliases",array("id" => $id),"aliasmgr"));
	}

	////
	// !called, when adding a new object 
	// parameters:
	// parent - the folder under which to add the object
	// return_url - optional, if set, the "back" link should point to it
	// alias_to - optional, if set, after adding the object an alias to the object with oid alias_to should be created
	function add($arr)
	{
		extract($arr);
		if ($return_url != "")
		{
			$this->mk_path(0,"<a href='$return_url'>Tagasi</a> / Lisa HTML import");
		}
		else
		{
			$this->mk_path($parent,"Lisa HTML import");
		}
		$this->read_template("add.tpl");

		$this->vars(array(
			"toolbar" => $this->my_toolbar(array("id"=>$id,"sid"=>$ob["meta"]["sid"])),
			"name" => "uus HTML import",
			"reforb" => $this->mk_reforb("submit", 
				array(
					"parent" => $parent, 
					"alias_to" => $alias_to, 
					"return_url" => $return_url,
				)),
		));
		return $this->parse();
	}


	function my_toolbar($arr)
	{
		extract($arr); //id
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/blue/awicons"));
		$toolbar->add_button(array(
			"name" => "save",
			"tooltip" => "salvesta",
			"url" => "javascript:document.add.submit()",
			"imgover" => "save_over.gif",
			"img" => "save.gif",
		));
		$toolbar->add_button(array(
			"name" => "change",
			"tooltip" => "change",
			"url" => $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url))),
			"imgover" => "change_over.gif",
			"img" => "change.gif",
		));

		$toolbar->add_button(array(
			"name" => "conf",
			"tooltip" => "conf",
			"url" => $this->mk_my_orb("conf", array("id" => $id, "return_url" => urlencode($return_url))),
			"imgover" => "conf_over.gif",
			"img" => "conf.gif",
		));
		$toolbar->add_button(array(
			"name" => "sqlconf",
			"tooltip" => "sqlconf",
			"url" => $this->mk_my_orb("sqlconf", array("id" => $id, "return_url" => urlencode($return_url))),
			"imgover" => "conf_over.gif",
			"img" => "conf.gif",
		));
		return $toolbar->get_toolbar();
	}


}




/*
	function tag_strip($target,$tag,$replace="")
	{
		foreach($tag as $key =>$val)
		{
			$tag[$key]="/<($val)([^>]*)>([^<>]*)<\/($val)>/i";
		}
		$html=preg_replace($tag, '\3'.$replace, $target);
		return $html;
	}
*/


?>