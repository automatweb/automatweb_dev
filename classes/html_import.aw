<?php

/*
	@default table=objects
	@groupinfo general caption=üldine default=1
	@default group=general
	@property comment type=textarea field=comment cols=40 rows=3
	@caption Kommentaar

	@default field=meta
	@default method=serialize

	@property file_list type=select
	@caption failid remote/local

	@property source_path type=textbox size=50
	@caption kohalikud failid asuvad siin

	@property files type=textarea cols=60 rows=3
	@caption urlid/failid

	@property example type=textarea cols=60 rows=3
	@caption näitefailid töötlemiseks

	@property single type=select
	@caption tüüp

////////////////////////////////////////////////

	@default group=output_conf
	@groupinfo output_conf caption=väljund
	@property output type=select
	@caption tüüp

	@property mk_my_table type=textbox
	@caption sql tabeli nimi 'html_import_'

	@property olemas type=text
	@caption NB olemasolevad tabelid:
////////////////////////////////////////////////

	@default group=visual_conf
	@groupinfo visual_conf caption=visio

	@property reset type=button editonly=1
	@caption reset table

	@property starts type=textbox
	@caption hidden

	@property match type=textarea
	@caption faili unikaalne string siia:

	@property testi type=text
	@caption testi

	@property ruuls_setup type=text
	@caption ruuls setup

	@property ruul type=textarea
	@caption

////////////////////////////////////////////////

	@default group=sqlconf
	@groupinfo sqlconf caption=sql tabeli seaded
	@property making_sql type=text
	@caption tabeli kokkupanek

	@property show_create type=text
	@caption selline tabel on olemas

	@property show_create_table type=text
	@caption selline tabel tehakse

	@property add_id type=checkbox ch_value=1
	@caption lisa id veerg (id int primary key auto_increment)

	@property create_link type=text
	@caption loo tabel

	@property drop_link type=text
	@caption kustuta tabel

	@property empty_link type=text
	@caption tühjenda tabel

	@property view_link type=text
	@caption vaata tabelit

	@property import_link type=text
	@caption IMPORT

	@property sql_ruul type=textbox
	@caption sql_ruul hidden

///////////////////////////////////////////////

*/


define("PREFIX","html_import_"); // sql tabelitele ette, et mingit jama ei tekiks


class html_import extends class_base
{

	function html_import()
	{
		$this->init(array(
			'clid' => CL_HTML_IMPORT,
		));
	}


	function visual_conf($ob)
	{
		$meta=$ob['meta'];
		$examples=explode("\n",$meta["example"]);
		$what=$meta["ruul"];
		$html=$this->getfile($examples[0]);

		if ($meta["example"])
		{
			if ($meta["single"])
			{

			load_vcl("table");
			$t = new aw_table(array(
				"prefix" => "html_ruul_conf",
			));

			$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

			$t->define_field(array(
				'name' => 'ruul',
				'caption' => 'reegel',
			));
			$t->define_field(array(
				'name' => 'begin',
				'caption' => 'algus string',
			));
			$t->define_field(array(
				'name' => 'desc',
				'caption' => 'kirjeldus/sql',
			));
			$t->define_field(array(
				'name' => 'end',
				'caption' => 'lõpu string',
			));


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
				$j++;
				$ruul["ruul_".$j]="ruul_".$j;


				foreach($ruul as $key=>$val)
				{

					$data[]=array(
						'ruul' => $key,
						'begin' =>
html::textarea(array('name'=> 'ruul['.$key.'][begin]', 'cols'=>25, 'rows' => 4, 'value' => $rule[$key]['begin'])),
						'desc' =>
'<table border=0 cellpadding=0 cellspacing=0><tr><td>kirjeldus<br>'.
html::textbox(array('name'=> 'ruul['.$key.'][desc]', 'value'=>$rule[$key]["desc"]))
."<br /><b>sql veerg</b>".
html::textbox(array('name'=> 'ruul['.$key.'][mk_field]', 'value'=>$rule[$key]['mk_field']))
.'<nobr>strip html'.
html::checkbox(array('name'=>'ruul[ruul_'.$i.'][strip_html]', 'value' => 1,'checked' => $what['ruul_'.$i]['strip_html']))
.'</nobr></td></tr></table>',
						'end'=>
html::textarea(array('name'=> 'ruul['.$key.'][end]', 'cols'=>25, 'rows' => 4, 'value' => $rule[$key]['end'])),
					);
				}



				$arr = new aw_array($data);
				foreach($arr->get() as $row)
				{
					$t->define_data(
						$row
					);
				}
				$t->sort_by();
				$ruulid = $t->draw();


//				$source=$this->ruultest($html,$meta["ruul"]);
				$show="fail: ".$examples[0]."<br /><textarea cols=95 rows=15>".$html."</textarea><br /><br />".$ruulid;
			}
			if (!$meta["single"])
			{
				$starts=$meta['starts'];
				$source=preg_replace("/(<([\/]?)table[^>]*>)/ei", '\$this->caunt("\\2","$starts")', $this->getbody($html));
				if($meta['starts'])
				{
					$source=$this->gettable($source,"",$starts,$starts);
					$tmp=spliti("<tr", $source);		//leiame veergude arvu
					$cnt=preg_match_all("/(<td)/i", $tmp[1], $null);
	//				print_r($source);
					$tbl=$this->table_to_array($source,0);
					$this->ruul=$what;
					$source=preg_replace("/<input.*>/Ui", '', $source);
					$source=preg_replace("/<script.*<\/script>/Ui", '\1 nome=\3', $source);
					$source=preg_replace("/<form [^>]*>/i", '', $source);
//$source=preg_replace("/(<input [^>]*)(name\=)([^>]*>)/i", '\1 nome=\3', $source);//et formi elmendid ei hakkak AW-d segama

					for ($i=1;$i<count($tbl[1]);$i++)
//					foreach($what as $key => $val)
					{
						$rhuul=
'<font color=blue size=-1><p>kirjeldus<br />'.
html::textbox(array('name'=> 'ruul[ruul_'.$i.'][desc]', 'value'=>$what['ruul_'.$i]['desc'],'size'=>15))
.'<br /><b>sql veerg</b><br />'.
html::textbox(array('name'=> 'ruul[ruul_'.$i.'][mk_field]', 'value'=>$what['ruul_'.$i]['mk_field'],'size'=>10)).
' <nobr>strip html'.
html::checkbox(array('name'=>'ruul[ruul_'.$i.'][strip_html]', 'value' => 1,'checked' => $what['ruul_'.$i]['strip_html']))
.'</nobr></p></font>';
							$source=preg_replace("/(<td[^\!>]*>)/i", '<td !>'.$rhuul, $source,1);
					}
				}
				$notest=1;
				$show="fail: ".$examples[0]."<br /><textarea cols=95 rows=10>".$html."</textarea><br /><br />".$source;
			}
		}
		return $meta["database"].$show.($notest?'':$exmpl);
	}


	function create_table($arr)
	{
		extract($arr);
		$ob=$this->get_object($id);
		$q=$this->mk_my_table($ob["meta"]["mk_my_table"],$ob["meta"]['ruul'],$ob["meta"]['sql_ruul'],$ob["meta"]["add_id"],true);
		header('Location:'.$return_url);
	}

	function empty_table($arr)
	{
		extract($arr);
		$ob=$this->get_object($id);
		$this->db_query("delete from ".PREFIX.$ob['meta']['mk_my_table']);
		header('Location:'.$return_url);
	}


	function drop_table($arr)
	{
		extract($arr);
		$ob=$this->get_object($id);
		$this->db_query("DROP table ".PREFIX.$ob["meta"]["mk_my_table"]);
		$this->set_object_metadata(array( //$overwrite
			"oid" => $id,
			"key" => "db_table_contents",
			"value" => '',
		));
		header('Location:'.$return_url);
	}


	function set_property($args = array())
	{
		$data = &$args["prop"];
		$form = &$args["form_data"];
		$retval = PROP_OK;
		switch($data['name'])
		{

		};
		return $retval;
	}


	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = true;
		$meta=$args['obj']['meta'];
		$id=$args['obj']['oid'];

		static $tbl_exists,$tables;
		if (!isset($tables))
		{
			$all_db_tables=$this->db_query('show tables');
			while ($row = $this->db_next())
			{
				if (is_int(strpos($row['Tables_in_samaw'],PREFIX)))
				{
					$tables[$row['Tables_in_samaw']] = $row['Tables_in_samaw'];
					if ($row['Tables_in_samaw']==PREFIX.$meta["mk_my_table"])
					{
						$tbl_exists=true;
					//break 1;
					}
				}

			}
		}

		switch($data["name"])
		{

			case 'testi':

				if (!$meta['single'])
				{
					$retval=PROP_IGNORE;
				}
				else
				{
					$examples=explode("\n",$meta['example']);
					foreach ($examples as $key => $val)
					{
						$exmpl.='testi :<a href='.$this->mk_my_orb('ruul_test',array("id"=>$id, "f"=>$key))." target=_blank>$val</a><br />";
					}
					$data['value']=$exmpl;
				}
			break;

			case 'ruuls_setup':
				$data['value']=$this->visual_conf($args['obj']);
			break;

			case 'reset':
				$data['value']='reset';
				$data['onclick']= "document.changeform.starts.value='';document.changeform.submit();";
			break;


			case 'ruul':
				$retval=PROP_IGNORE;
			break;
			case 'sql_ruul':
				$retval=PROP_IGNORE;
			break;

			case 'show_create_table':
				if (!$tbl_exists)
				{
					$data['value']=$this->mk_my_table($meta["mk_my_table"],$meta['ruul'],$meta['sql_ruul'],$meta["add_id"]);
				}
				else
				{
					$retval=PROP_IGNORE;
				}

			break;
			case 'making_sql':
				if ($meta['ruul'])
				{
					$data['value']=$this->making_sql($args['obj']);
				}
				else
				{
				$data['value']='test';
					$retval=PROP_IGNORE;
				}

			break;

			case 'create_link':
				if ($tbl_exists)
				{
					$retval=PROP_IGNORE;
				}
				else
				{
					$data['value'] = html::href(array(
						'caption'=>'CREATE',
						'url'=>$this->mk_my_orb('create_table', array(
							"id" => $id,
							'return_url' => urlencode($this->mk_my_orb('change',array('id' => $id,'group' => 'sqlconf'))),
						)),
//						'target'=> '_blank',
					));
				}
			break;

			case 'drop_link':
				if (!$tbl_exists)
				{
					$retval=PROP_IGNORE;
				}
				else
				{
					$data['value'] = html::href(array(
						'caption'=>'DROP',
						'url'=>$this->mk_my_orb('drop_table', array(
							"id" => $id,
							'return_url' => urlencode($this->mk_my_orb('change',array('id' => $id,'group' => 'sqlconf'))),
						)),
						//'target'=> '_blank',
					));
				}
			break;

			case 'empty_link':
				if ($tbl_exists)
				{
					$data['value'] = html::href(array(
						'caption'=>'EMPTY',
						'url'=>$this->mk_my_orb('empty_table', array(
							"id" => $id,
							'return_url' => urlencode($this->mk_my_orb('change',array('id' => $id,'group' => 'sqlconf'))),
						)),
//						'target'=> '_blank',
					));
				}
				else
				{
					$retval=PROP_IGNORE;
				}
			break;


			case 'show_create':
				if ($tbl_exists)
				{
					$data['value'] = $this->db_show_create_table(PREFIX.$meta["mk_my_table"]);
				}
				else
				{
					$retval=PROP_IGNORE;
				}

			break;


			case 'source_path':
				if ($meta['file_list']!='1')
				{
					$data['value'] =$meta['source_path'];
				}
				else
				{
					$retval=PROP_IGNORE;
				}
			break;
			case 'files':
				if ($meta['file_list']=='1')
				{
					$data['value'] =$meta['files'];
				}
				else
				{
					$retval=PROP_IGNORE;
				}
			break;


			case 'file_list':
				$data['options']=array(
					'0' => 'html source kataloog',
					'1'=>'failide nimekiri',
				);
				$data['selected'] = $args['obj']['meta']['file_list'];
			break;

			case 'single':
				$data['options']=array(
					'1' => 'leheküljel on üks kirje, millel elemendid võivad paikneda suvalises kohas',
					'0' => 'leheküljel on mitu kirjet tabeli kujul, üks rida = üks kirje',
				);
				$data['selected'] = $meta['single'];
			break;

			case 'output':
				$data['selected'] = $meta['output'];
				$data['options'] = array(
					'mk_my_table' => ' luuakse sql andmetabel',
					'mk_my_query' => 'luuakse sql insert laused ekraanile',
					'mk_my_csv' => 'luuakse csv tyypi fail(not yet implemented)',
					'mk_my_xml' => 'luuakse xml tyyp(not yet implemented)',
				);
			break;
			case 'mk_my_table':
				if ($meta['output']=='mk_my_table')
				{
					$data['value'] =$meta['mk_my_table'];
				}
				else
				{
					$retval=PROP_IGNORE;
				}
			break;
			case 'olemas':
//				if ($meta['output']=='mk_my_table')
				if ($tables)
				{
					$data['value'] = implode('<br />',$tables);
				}
				else
				{
					$data['value'] = "pole tabeleid";
//					$retval=PROP_IGNORE;
				}
			break;


			break;
			case 'import_link':
				if ($tbl_exists || ($meta['output']=='mk_my_query'))
				{
					$examples=explode("\n",$meta["example"]);
					$html=$this->getfile($examples[0]);
					if (is_int(strpos($html,$meta["match"]))) //&& create lause õige && veerge on defineeritud
					{
						$data['value']=html::href(array('target'=>'_blank','caption'=>'IMPORT', 'url'=>$this->mk_my_orb('import',array("id"=>$id))));
					}
					else
					{
						$data['value']="unikaalne string puudu või vale";
					}
				}

			break;
			case 'view_link':
				if ($tbl_exists){
					if (!$meta['db_table_contents'])
					{
						$meta['db_table_contents'] = $this->new_object(array(
							"parent" => $args['obj']['parent'],
							"name" => PREFIX.$meta["mk_my_table"],
							"class_id" => CL_DB_TABLE_CONTENTS,
							"comment" => "generated by html_import",
							"metadata" => array(
								'status' => 2,
								'db_base' =>55970,   ////////!!!!!!!!!!!!!!!  andmebaasi login objekt vaja konfis määratavaks teha
								'db_table' => PREFIX.$meta["mk_my_table"],
								'per_page' => 20,
							)
						));

						$this->set_object_metadata(array( //$overwrite
							"oid" => $id,
							"key" => "db_table_contents",
							"value" => $meta['db_table_contents'],
						));
					}
					$data['value']=html::href(array('caption' => 'tabeli sisu',
						'target' => '_blank',
						'url' =>
		$this->mk_my_orb("content", array('id'=>$meta['db_table_contents']),'db_table_contents'),
					));
				}
				else
				{
					$retval=PROP_IGNORE;
				}


			break;
		}

		return  $retval;
	}




	////
	// ! get source code of html page and remove confusing linebreakes and tabs
	// file - name of the file, must include path
	function getfile($file)
	{
		$text=@implode("",@file(trim($file)));

		$search = array (
				"'\t'", // strip tabs
				"'\n'", // strip linebrakes
				"'\r'",
				"'<textarea'",  // muidu pole võimalik testi õigesti kuvada
				"'<\/textarea>'", // muidu pole võimalik testi õigesti kuvada
		);

		$replace = array (
				"",
				"",
				"",
				"<t_extarea",
				"</t_extarea>",
		);

		$text = preg_replace ($search, $replace, $text);

		return $text;
	}

	////
	// ! get contents of html body
	// html - html code
	function getbody($html)
	{
		preg_match("'<body[^>]*?>(.*)?<\/body>'si", $html, $matches);
		return $matches[1];
	}

	////
	// ! get html table out of html file
	//
	function gettable($html,$val="",$begin,$end)
	{
		preg_match("'</tstart $begin>(<table[^>]*?>(.*)<\/table>)<\/tend $end>'si", $html, $matches);
		return $matches[1];
	}


	function making_sql($ob)
	{

		$tyyp=$this->db_list_field_types();
		$ruul=$ob["meta"]["ruul"];
		$sql_ruul=$ob["meta"]["sql_ruul"];

			load_vcl("table");
			$t = new aw_table(array(
				"prefix" => "html_tbl_conf",
			));

			$t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");

			$t->define_field(array(
				"name" => "comment",
				"caption" => "kommentaar",
			));
			$t->define_field(array(
				"name" => "sqlfield",
				"caption" => "sql veerg",
			));
			$t->define_field(array(
				"name" => "unique",
				"caption" => "unikaalne",
			));
/*			$t->define_field(array(
				"name" => "strip_html",
				"caption" => "strip html",
			));*/
			$t->define_field(array(
				"name" => "fieldtype",
				"caption" => "veeru tüüp",
			));
			$t->define_field(array(
					"name" => "size",
				"caption" => "suurus",
			));

			foreach($ruul as $key=>$val)
			{
				if ((($ruul[$key]["end"] || $ruul[$key]["begin"]) || !$ob["meta"]["single"]) && $ruul[$key]["mk_field"])
				{
					$mis="sql_ruul[$key]";
					$data[]=array(
						"comment" => $ruul[$key]["desc"],
						"sqlfield" => $ruul[$key]["mk_field"],
						"unique"=>html::checkbox(array('name'=>$mis."[unique]",'value'=>1,'checked'=>$sql_ruul[$key]["unique"])),
						"fieldtype" => html::select(array('name' => $mis."[type]", 'options' => $tyyp, 	'selected' => $sql_ruul[$key]["type"])),
						"size"=>html::textbox(array('name'=>$mis."[size]", 'size'=>5,'maxlength' => 5, 'value' => $sql_ruul[$key]['size'])),
					);
				}
			}
			$arr = new aw_array($data);
			foreach($arr->get() as $row)
			{
				$t->define_data(
					$row
				);
			}
			$t->sort_by();
			return $t->draw();
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
		$ruul=$ruul;
		$sql_ruul=$sql_ruul;
		if (is_array($ruul))
		foreach($ruul as $key => $val)
		{
			if ($ruul[$key]["mk_field"])
			{
				$size=$sql_ruul[$key]['size']?$sql_ruul[$key]["size"]:11;
				$tp=$this->mk_field_len($sql_ruul[$key]['type'],$size);
				$tp.=$sql_ruul[$key]['unique']?" unique":"";
				$cols[]=$val['mk_field']." ".$tp;
			}
		}
		$cols=$this->field_list($cols);
		if ($add_id)
		{
			$cols="\nid int primary key auto_increment,".$cols;
		}
		if ($add_source)
		{
			$cols=",source text";
		}

		$q="create table ".PREFIX.$tablename." ($cols \n)";
		if ($create)
		{
			$done=$this->db_query($q);
			if (!$done)
			{
				return false;
			}
		}
		return str_replace(",",",\n",$q);
	}



	////
	// ! implode list with comma, removes empty entries
	function field_list($arr)
	{
		if (is_array($arr))
		foreach($arr as $key =>$val)
		{
			if ($val)
				$fi[$key]=$val;
		}
		else
		{
			return 0;
		}

		return @implode(",",$fi);
	}


	////
	// ! will perform the import proccess
	function tiri($arr)
	{
		extract($arr);
		$ob = $this->get_object($id);

		if ($ob["meta"]["file_list"])
		{
			$files=$ob["meta"]["files"];
			$files=explode("\r",$files);
		}
		else
		{
			classload("linklist");
			$files=linklist::get_templates($ob["meta"]["source_path"]."/");// selle linklistis oleva meetodi peaks oopis coressse panema
		}

		if (!$files)
		{
			die("could not get files, check if if they really excist and can be opened");
		}

		//tabeli jrk nr
		$starts=$ob["meta"]["starts"];
		$what=$ob["meta"]["ruul"];

//print_r($what);

		foreach($what as $key => $val)
		{
			$cells[]=$val["mk_field"];
			$strip_html[$val["mk_field"]] = $val['strip_html']?true:false;
		}

		$fields=$this->field_list($cells);
		echo "<html><body><pre>";

		if (6<7){
			foreach($files as $key=>$val)
			{
				$this->pinu=array();//need peavad globaalselt iga kord nulli minema
				$this->tblnr=0;
				$file=$path.$val;
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

								$begin=preg_quote($val["begin"],'/');
								$end=preg_quote($val["end"],'/');
								preg_match("/($begin)(.*)($end)/sU", $source, $mm);
								//striptags?
								$tbl[1][]=$mm[2];
							}
						}

					}
					else
					{
						$source=preg_replace("/(<([\/]?)table[^>]*>)/ei", '\$this->caunt("\\2","$starts")', $source);
						$source=$this->gettable($source,"",$starts,$starts);
						$tbl=$this->table_to_array($source,$cells,$strip_html);
					}
//print_r($tbl);


					$exec=($ob["meta"]["output"]=="mk_my_table")?true:false;
					$total+=$this->db_insert(PREFIX.$ob["meta"]["mk_my_table"],$fields,$tbl,true,$exec);
					echo "<br>OK ".trim($file)."<br>";
				}
				else
				{
					echo " !! ".trim($file)."<br>";
//					echo "could not import data from $file !! string comparision failed!!<br>";
				}
				flush();
				set_time_limit(30);
//				sleep(2);

			}
		}
		echo "#total: ".(int)$total."\n\r";
		if ($ob["meta"]["output"]=="mk_my_table")
			echo "kui errorit ei tekkinud, siis andmed läksid vist baasi";
		if ($ob["meta"]["output"]=="mk_my_query")
			echo "";
		echo "</pre></body></html>";
		die();
	}



	function preg_()
	{


	}

	////
	// ! does the specified ruul really work
	//  well it does if it looks pink
	// oid - required
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
			$begin=preg_quote($val["begin"],'/');
			$end=preg_quote($val["end"],'/');

			if($begin && $end)
			{
//				echo strpos($source,$begin);
//				echo strrpos($source,$begin);
//				if (strpos($source,$begin)<>strrpos($source,$begin)) // rohkem kui 1 match !!
				{
//					$warn="<font color=blue><b>algusstringe leiti rohkem kui üks!!</b></font>";
				}



				$source=preg_replace("/($begin)(.*)($end)/Us", '\\1<span title="'.$val["desc"]." - ".$val["mk_field"].'" style="background-color:#ffaaaa">\\2'.$warn.'</span>\\3', $source);
			}
		}
		echo $source;
		die();
	}


//teeme pinu et siis kui mingi tabel on tabeli sees, algus ja lõpu id oleks õige
	function caunt($end,$aktiivne='')
	{
		if ($end)
		{
			$m=array_pop($this->pinu) ;
			return "</table></tend $m>";
		}
		else
		{
			$this->tblnr++;
			$t=$this->tblnr;
			$this->pinu[]=$t;
			$color=$aktiivne?"#ff9999":$this->color[$t%6];
			$html=html::button(array('value'=>'siit','onclick' => 'document.changeform.starts.value='.$t.';document.changeform.submit();')).
			"<br></tstart $t><table border=1 id=$t bgcolor=$color>";
			return $html;
		}
	}



	////
	// ! makes array out of html table
	// if html table is well formed and regular like
	// <table border=0><tr><td width=100>data1</td><td>data2</td></tr>...</table>
	// then returns array(
	//			array ("data_col_1","data_col_2",...),
	//			...
	//		)
	// table - html table
	// gets - list of columns to return (0 .. ), if not specified all columns will be returned

	function table_to_array($table,$gets=array(),$strip_html=array())
	{
		$html=preg_replace("/<\/td>/i", "#%#", $table);
		$html=preg_replace("/<td[^>]*?>/i", "", $html);
		$html=preg_replace("/<\/tr>/i", "#&#", $html);
		$html=preg_replace("/<tr[^>]*?>/i", "", $html);
		//$html=strip_tags($html);
		$rows=explode("#&#",trim($html));
//		print_r($strip_html);
		foreach($rows as $val)
		{
			$row=trim($val);
			if ($row)
			{
				$cells=explode("#%#",$row);
				$dat=array();
				foreach($cells as $key=>$cell)
				{
					if($gets[$key])
					{
						$cell=($strip_html[$gets[$key]])?strip_tags($cell):$cell;
						$dat[]=trim($cell);
					}
					elseif(!$gets) //kui veerud on üldse määramata, siis kogu tabel ->
					{
						$cell=($strip_html[$gets[$key]])?strip_tags($cell):$cell;
						$dat[]=trim($cell);
					}
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
	// first_row  bool - elliminate first row
	// insert bool - actually insert data into the sql base
	//
	function db_insert($table,$fields,$data,$first_row=true,$insert=false,$source="")
	{
		if ($first_row) //esimesel real on ilmselt kirjeldused
		{
			unset($data[0]);
		}
//echo $fields;
/*		if ($source=="yes")
		{
			$sorts=", source";
			$source=addslashes($source);
		}
		$query="insert into $table($fields, source) values ";
		*/
		$query="insert into $table($fields) values ";


		foreach($data as $key=> $val)
		{
			$got=0;

			$this->quote($val);
				foreach($val as $key => $val)
				{
					$val=$this->quote(trim($val));
					$got=$val?"yes":$got;
					$va[$key]="'".$val."'";
				}
				if($got)
				{
					$value="(".implode(",",$va).")";
					$q=$query.$value.";";
					$total++;

					if ($insert)
					{
//						echo $q."\n";
						$this->db_query($q);
					}
					else
					{
						echo $q."<br>";
					}
				}
				else
				{
					echo "tühi kirje!<br>";
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
}

// todo:
// logi, ja võimalus importi pauseda, ning jätkata
// update võimalus, andmete uuendamiseks, lisamiseks
// optional: andmete päritolu veeru lisamine
// optional: kuupäev

/*

foreach ($failid as $key => $val)
{

	$on=$this->db_fetch_field("select count(*) as olemas  from logi where sourcefail='$fail'", 'olemas');
	if ($on>0 && !$update)
	{
		siis skip
		 return skipping;
	}
	if (!$update)
	{
		$this->db_query("update ...where unikaalne veerg=$asi");
		return updated
	}
	else
	{
		$this->db_query("isert ...");
		return last insert id
	}

}

*/

?>
