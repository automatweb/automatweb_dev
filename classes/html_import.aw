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
	//! get everything between body tags
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
//! mage variant
	function list_db_fields($table,$all=false)
	{

/*		$res=@mysql_query("select * from $table limit 1");
		$i = 0;
		while ($i < @mysql_num_fields($res)) 
		{
			$meta = @mysql_fetch_field($res);
			if ($meta) 
			{
				if ($all)
					$arr[$meta->name]=$meta;
				else
					$arr[$meta->name]=$meta->name;
			}
			$i++;
		}
*/
/*
		$fields = mysql_list_fields("", $table);
$columns = mysql_num_fields($fields);

for ($i = 0; $i < $columns; $i++) {

    echo mysql_field_name($fields, $i) . "\n";
}
*/
		return $arr;
	}



////
//




/*
		$what=$ob["meta"]["single"]?$ob["meta"]["ruul"]:$ob["meta"]["cell"];

		foreach($what as $key => $val)
		{
			$cells[]=$val["mk_field"];
		}
		$fields=$this->field_list($cells);
*/
	function mk_my_table($what,$tablename,$create=false)
	{
		if (is_array($what))
		foreach($what as $key => $val)
		{
			if ($val["mk_field"])
			$cols[]=$val["mk_field"]." varchar(20)";
		}
		$cols=$this->field_list($cols);
		//lisame ka id
		$cols="id int primary key auto_increment,".$cols;
//print_r($cols);
		$q="create table html_import_".$tablename." ($cols)";
		if ($create)
		{
			$this->db_query($q);
		}
//echo $q;
		return $q;
	}



	function field_list($arr)
	{
		if (is_array($arr))
		foreach($arr as $key =>$val)
		{
			if ($val) $fi[$key]=$val;
		}
		else return 0;
		return @implode(",",$fi);
	}

	
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
		$what=$ob["meta"]["single"]?$ob["meta"]["ruul"]:$ob["meta"]["cell"];

		foreach($what as $key => $val)
		{
			$cells[]=$val["mk_field"];
		}
		$fields=$this->field_list($cells);


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
					$total+=$this->db_insert("html_import_".$ob["meta"]["mk_my_table"],$fields,$tbl,true);
					echo "imported data from $file<br>";
				}
				else
				{
//					echo "could not import data from $file !! File unique string doesn't match<br>";
				}
			}
		}
		echo "total: $total<br>";
		echo "kui errorit ei tekkinud, siis andmed läksid baasi";
		die("<br>finished");
	}

	function ruultest($source,$ruuls=array())
	{
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
		return $source;
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

		if ($reset)
		{
			$starts="";
		}	
		else
		{
			$starts=$starts?$starts:$ob["meta"]["starts"];
		}
		$this->set_object_metadata(array("oid" => $id,"key" => "starts","value" => $starts,));

		$what=$ob["meta"]["single"]?$ob["meta"]["ruul"]:$ob["meta"]["cell"];		

		if ($ob["meta"]["example"])
		{
			$html=$this->getfile($ob["meta"]["example"]);
			$source=$this->getbody($html);
			$source=preg_replace("/(<([\/]?)table[^>]*>)/ei", '\$this->caunt($link,"\\2","$starts")', $source);

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
					));
					$fields=$this->parse("fields");					
					$this->vars(array(
						"begin"=>$rule[$key]["begin"],
						"end"=>$rule[$key]["end"],
						"fields"=>$fields,
					));
					$ruulid.=$this->parse("ruul");
				}
				$ruulid=$this->parse("ruulbar").$ruulid;
				$source=$this->ruultest($html,$ob["meta"]["ruul"]);
			
			}
			else
			if($starts)//&&$ends)
			{
//				$what=$ob["meta"]["cell"];
				$source=$this->gettable($source,"",$starts,$starts);
				$tmp=spliti("<tr", $source);		//leiame veergude arvu
				$cnt=preg_match_all("/(<td)/i", $tmp[1], $null); 
//				print_r($source);
//				$tbl=$this->table_to_array($source,);
				$this->cell=$what;
				$source=preg_replace("/(<td[^\!>]*>)/ie", '\$this->sel_field("\\1")', $source,$cnt);
			}
		}

		$this->vars(array(
			"ruul"=>"eraldajad",
			"mis"=>"separator",
			"begin"=>$ob["meta"]["separator"]["eraldajad"]["begin"],
			"end"=>$ob["meta"]["separator"]["eraldajad"]["end"],
			"fields"=>"",
		));
		$separators=$this->parse("ruul");

//print_r($what=$ob["meta"]["cell"]);
		if (is_int(@strpos($html,$ob["meta"]["match"])))
		{
			$import_link="<a href=".$this->mk_my_orb("tiri",array("id"=>$id))." target=_blank>impordi</a>";
		}
		else
		{
			$import_link="unikaalne string puudu või vale";
		}

		$this->vars(array(
			"match"=>$ob["meta"]["match"],
			"ruul"=>$ruulid,
			"example"=>$ob["meta"]["example"],
			"reset"=> "<a href='$link&reset=1'>reset</a>",
			"separators"=>$separators,
			"singleon"=>checked($ob["meta"]["single"]),
			"singleoff"=>checked(!$ob["meta"]["single"]),
			"gogo"=> $import_link,
			"mk_my_table"=> $ob["meta"]["mk_my_table"],
			"mk_table"=> $this->mk_my_table($what,$ob["meta"]["mk_my_table"]),
			"name"=>$ob["name"],
			"source_path"=>$ob["meta"]["source_path"],//$this->picker(0,array("/home/axel/public_html/html"=>"/home/axel/public_html/html")),
			"comment"=>$ob["comment"],
			"source"=>"<textarea cols=95 rows=15>".$html."</textarea><br>".$source,
//			"ruul_test"=>($ob["meta"]["single"]&&$ob["meta"]["example"])?"<a href=".$this->mk_my_orb("ruul_test",array("id"=>$id))." target=_blank>test ruuls</a>":"",
//			"tables"=>$this->picker($ob["meta"]["db_table"],$db_tables),
//			"ruul"=>"",//et ei väljastaks kasutuses olnud muutujat
//			"fields"=>"",//et ei väljastaks kasutuses olnud muutujat
			"toolbar" => $this->my_toolbar(array("id"=>$id,"sid"=>$ob["meta"]["sid"])),
			"reforb" => $this->mk_reforb("submit", array("id" => $id, "starts"=>$starts, "return_url" => urlencode($return_url)))
		));
		return $this->parse();
	}


	function sel_field($more="")
	{
		static $fn,$tbl;if(!$fn)$fn=0;
		
		$this->vars(array(
			"mis"=>"cell",
			"ruul"=>$fn,
			"mk_field"=>$this->cell[$fn]["mk_field"],
			"desc"=>$this->cell[$fn]["desc"],
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




	function db_insert($table,$fields,$data,$first_row=true)
	{
		if ($first_row) //esimesel real on ilmselt kirjeldused
		{
			unset($data[0]);
		}
//echo $fields;
		foreach($data as $key=> $val)
		{
			$i=0;
				foreach($val as $key => $val)
				{
					if($val) $i=1;
					$va[$key]="'".addslashes($val)."'";
				}
				if($i)
				{
echo	 				$q="insert into $table($fields) values (".implode(",",$va).");"; //need querid võib ju tegelt kuskile faili panna et hiljem sisestada
//					$this->db_query($q);
//echo 					$csv=implode(";",$va)."\n"; //need querid võib ju tegelt kuskile faili panna et hiljem sisestada
					$total++;
				}
				else
					echo "_<br>";
		}

//siia peaks genereerima nüüd ka ming objekti loomise ntx: csv faili object, või sql tabeli objekt
		
		
		return $total;
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

				$meta=array(
					"single"=>$single,
					"starts"=>$starts,
					"source_path"=>$source_path,
					"example"=>$example,
					"db_table"=>$db_table,
					"mk_my_table"=>$mk_my_table,
					"match"=>$match,
					"separator"=>$separator,
				);
			if ($cell) $meta=$meta+array("cell"=>$cell);
			if ($ruul) $meta=$meta+array("ruul"=>$ruul);


			
			$this->upd_object(array(
				"oid" => $id,
				"name" => $name,
				"comment" => $comment,
				"metadata" => $meta
			));
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
		}

		if ($alias_to)
		{
			$this->add_alias($alias_to, $id);
		}

		return $this->mk_my_orb("change", array("id" => $id, "return_url" => urlencode($return_url)));
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