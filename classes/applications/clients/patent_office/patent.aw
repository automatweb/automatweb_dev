<?php
// patent.aw - Trademark
/*

@classinfo syslog_type=ST_PATENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
@extends applications/clients/patent_office/intellectual_property
@tableinfo aw_trademark index=aw_oid master_table=objects master_index=brother_of

#TRADEMARK
@groupinfo name=trademark caption=Kaubam&auml;rk
@default group=trademark
	@property type type=select
	@caption T&uuml;&uuml;p

	@property undefended_parts type=textbox
	@caption Mittekaitstavad osad

	@property word_mark type=textbox
	@caption S&otilde;nam&auml;rk

	@property colors type=textarea
	@caption V&auml;rvide loetelu (juhul, kui on v&auml;rviline)

	@property trademark_character type=textarea
	@caption Kaubam&auml;rgi iseloomustus

	@property element_translation type=textarea
	@caption V&otilde;&otilde;rkeelsete elementide t&otilde;lge

	@property reproduction type=fileupload reltype=RELTYPE_REPRODUCTION form=+emb
	@caption Lisa reproduktsioon

	@property g_statues type=fileupload reltype=RELTYPE_G_STATUES form=+emb
	@caption Garantiim&auml;rgi p&otilde;hikiri

	@property c_statues type=fileupload reltype=RELTYPE_C_STATUES form=+emb
	@caption Kollektiivm&auml;rgi p&otilde;hikiri

	@property trademark_type type=select multiple=1
	@caption T&uuml;&uuml;p

#tooted ja teenused
@groupinfo products_and_services caption="Kaupade ja teenuste loetelu"
@default group=products_and_services
	@property products_and_services_tbl type=table
	@caption Kaupade ja teenuste loetelu

@default group=priority
	@property childtitle110 type=text store=no subtitle=1
	@caption Konventsiooniprioriteet
		@property convention_nr type=textbox
		@caption Taotluse number

		@property convention_date type=date_select
		@caption Kuup&auml;ev

		@property convention_country type=textbox
		@caption Riigi kood

	@property childtitle111 type=text store=no subtitle=1
	@caption N&auml;ituseprioriteet
		@property exhibition_name type=textbox
		@caption N&auml;ituse nimi

		@property exhibition_date type=date_select
		@caption Kuup&auml;ev

		@property exhibition_country type=textbox
		@caption Riigi kood

// RELTYPES
@reltype C_STATUES value=12 clid=CL_FILE
@caption Kollektiivp&otilde;hikiri

@reltype G_STATUES value=13 clid=CL_FILE
@caption Garantiip&otilde;hikiri



*/

class patent extends intellectual_property
{
	function __construct()
	{
		parent::__construct();
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_PATENT
		));
		$this->info_levels[1] = "trademark";
		$this->info_levels[2] = "products_and_services";
		$this->types = array(t("S&otilde;nam&auml;rk"),t("Kujutism&auml;rk"),t("Kombineeritud m&auml;rk"),t("Ruumiline m&auml;rk"));
		$this->trademark_types = array(t("Kollektiivkaubam&auml;rk"),t("Garantiim&auml;rk"));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "type":
				$prop["options"] = $this->types;
				break;
			case "trademark_type":
				$prop["options"] = $this->trademark_types;
				break;
			default:
				$retval = parent::get_property($arr);
		}

		return $retval;
	}

	function _get_products_and_services_tbl(&$arr)
	{
		classload("vcl/table");
		$t = &$arr["prop"]["vcl_inst"];

		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
		));
//		$t->define_field(array(
//			"name" => "class_name",
//			"caption" => t("Klassi nimi"),
//		));
		$t->define_field(array(
			"name" => "prod",
			"caption" => t("Kaup/teenus"),
		));

		if(is_array($arr["obj_inst"]->meta("products")))
		{
			foreach($arr["obj_inst"]->meta("products") as $key=> $val)
			{
				$product = obj($key);
			//	$parent = obj($product->parent());
				$t->define_data(array(
					"prod" => html::textarea(array("name" => "products[".$key."]" , "value" => $val, )),
					"class" => $key,
//					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
				));
			}
		}
		return $t->draw();
	}

	function get_results_table()
	{//arr($_SESSION["patent"]["delete"]);arr($_SESSION['patent']['products']);
		if($_SESSION["patent"]["delete"])
		{
			//$js.= 'alert("'.$_SESSION['patent']['errors'].'");';
			unset($_SESSION['patent']['products'][$_SESSION["patent"]["delete"]]);
			$_SESSION["patent"]["delete"] = null;
		}

		if(!is_array($_SESSION["patent"]["prod_selection"]) && !is_array($_SESSION["patent"]["products"]))
		{
			return;
		}

		classload("vcl/table");
		$t = new vcl_table(array(
			"layout" => "generic",
		));

		$t->define_field(array(
			"name" => "class",
			"caption" => t("Klass"),
		));
//		$t->define_field(array(
//			"name" => "class_name",
//			"caption" => t("Klassi nimi"),
//		));
		$t->define_field(array(
			"name" => "prod",
			"caption" => t("Kaup/teenus"),
		));
		$t->define_field(array(
			"name" => "delete",
			"caption" => "",
		));


		$classes = array();
		if(is_array($_SESSION["patent"]["prod_selection"]))
		{
			foreach($_SESSION["patent"]["prod_selection"] as $prod)
			{
				if(!$this->can("view" , $prod))
				{
					continue;
				}

				$product = obj($prod);
				$parent = obj($product->parent());
				$classes[$parent->comment()][$product->id()] = $product->prop("userta1");

//				$t->define_data(array(
//					"prod" => html::textarea(array("name" => "products[".$prod."]" , "value" => $product->name() . "(" .$product->prop("code").  ")", )),
//					"class" => $parent->comment(),
//					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
//				));
			}
			$_SESSION["patent"]["prod_selection"] = null;
		}


		if(is_array($_SESSION["patent"]["products"]))
		{
			foreach($_SESSION["patent"]["products"] as $key=> $val)
			{
				$classes[$key][] = $val;
//				$t->define_data(array(
//					"prod" => html::textarea(array("name" => "products[".$key."]" , "value" => $val, )),
//					"class" => $parent->comment(),
//					"class_name" => $parent->name(),
	//				"oid"	=> $prod->id(),
//				));
			}
//			$_SESSION["patent"]["prod_selection"] = null;
		}
		ksort($classes);
		foreach($classes as $class => $prods)
		{
			$t->define_data(array(
				"prod" => html::textarea(array("name" => "products[".$class."]" , "value" => join("\n" , $prods))),
				"class" => $class,
				"delete" => html::href(array(
					"url" => "#",
					"onclick" => 'fRet = confirm("'.t("Oled kindel, et soovid valitud klassi kustutada?").'"); if(fRet) {document.getElementById("delete").value="'.$class.'";document.getElementById("stay").value=1;
					document.changeform.submit();} else;',
					"caption" => t("Kustuta"),
				)),

			));
		}
		return $t->draw();
	}

	function get_vars($arr)
	{
		$data = parent::get_vars($arr);

		if(sizeof($_SESSION["patent"]["products"]))
		{
			$_SESSION["patent"]["request_fee"]=2200;
			if($_SESSION["patent"]["co_trademark"] || $_SESSION["patent"]["guaranty_trademark"])
			{
				$_SESSION["patent"]["request_fee"]=3000;
			}
			$_SESSION["patent"]["classes_fee"]= (sizeof($_SESSION["patent"]["products"]) - 1 )*700;
		}
		else
		{
			$_SESSION["patent"]["request_fee"] = 0;
			$_SESSION["patent"]["classes_fee"] = 0;
		}

		$data["type_text"] = $this->types[$_SESSION["patent"]["type"]];
		//$data["products_value"] = $this->_get_products_and_services_tbl();
		$data["type"] = t("S&otilde;nam&auml;rk ").html::radiobutton(array(
				"value" => 0,
				"checked" => !$_SESSION["patent"]["type"],
				"name" => "type",
				"onclick" => 'document.getElementById("wordmark_row").style.display = "";
				document.getElementById("reproduction_row").style.display = "none";
				document.getElementById("color_row").style.display = "none";
			document.getElementById("colors").value = "";

				document.getElementById("wordmark_caption").innerHTML = "* Kaubam&auml;rk";
				document.getElementById("foreignlangelements_row").style.display = "";
				 ',
			)).t("&nbsp;&nbsp;&nbsp;&nbsp; Kujutism&auml;rk ").html::radiobutton(array(
				"value" => 1,
		 		"checked" => ($_SESSION["patent"]["type"] == 1) ? 1 : 0,
				"name" => "type",
				"onclick" => '
				document.getElementById("wordmark_row").style.display = "none";
			document.getElementById("word_mark").value = "";
				document.getElementById("foreignlangelements_row").style.display = "none";
			document.getElementById("element_translation").value = "";


				document.getElementById("reproduction_row").style.display = "";
				document.getElementById("color_row").style.display = "";'
			)).t("&nbsp;&nbsp;&nbsp;&nbsp; Kombineeritud m&auml;rk ").html::radiobutton(array(
				"value" => 2,
				"checked" => ($_SESSION["patent"]["type"] == 2) ? 1 : 0,
				"name" => "type",
				"onclick" => '
				document.getElementById("color_row").style.display = "";
				document.getElementById("reproduction_row").style.display = "";
      				document.getElementById("wordmark_row").style.display = "none";
			document.getElementById("word_mark").value = "";
				document.getElementById("foreignlangelements_row").style.display = "";',
			)).t("&nbsp;&nbsp;&nbsp;&nbsp; Ruumiline m&auml;rk ").html::radiobutton(array(
				"value" => 3,
				"checked" => ($_SESSION["patent"]["type"] == 3) ? 1 : 0,
				"name" => "type",
				"onclick" => '
				document.getElementById("color_row").style.display = "";
				document.getElementById("reproduction_row").style.display = "";
				document.getElementById("wordmark_row").style.display = "none";
			document.getElementById("word_mark").value = "";
				document.getElementById("foreignlangelements_row").style.display = "";',
			));

		$data["wm_caption"] = ($_SESSION["patent"]["type"]  ? t("S&otilde;naline osa:") : t("Kaubam&auml;rk:"));

		$data["trademark_type"] = t("(kui taotlete kollektiivkaubam&auml;rki)").html::checkbox(array(
			"value" => 1,
			"checked" => $_SESSION["patent"]["co_trademark"],
			"name" => "co_trademark",
			"onclick" => 'document.getElementById("c_statues_row").style.display = "";'
			)).'<a href="javascript:;" onClick="MM_openBrWindow(\'16340\',\'\',\'width=720,height=540\')"><img src="/img/lk/ikoon_kysi.gif" border="0" /></a><br>'.

			t("(kui taotlete garantiikaubam&auml;rki)").html::checkbox(array(
				"value" => 1,
				"checked" => $_SESSION["patent"]["guaranty_trademark"],
				"name" => "guaranty_trademark",
				"onclick" => 'document.getElementById("g_statues_row").style.display = "";'
			)).'<a href="javascript:;" onClick="MM_openBrWindow(\'16341\',\'\',\'width=720,height=540\')"><img src="/img/lk/ikoon_kysi.gif" border="0" />';
		$data["trademark_type_text"] = ($_SESSION["patent"]["co_trademark"]) ? t("Kollektiivkaubam&auml;rk") : "";
		$data["trademark_type_text"].= " ";
		$data["trademark_type_text"].= ($_SESSION["patent"]["guaranty_trademark"]) ? t("Garantiim&auml;rk") : "";

		$data["find_products"] = html::href(array(
			"caption" => t("Sisene klassifikaatorisse") ,
			"url"=> "javascript:void(0);",
			"onclick" => 'javascript:window.open("'.$this->mk_my_orb("find_products", array("ru" => get_ru(), "print" => 1)).'","", "toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=400, width=600");',
		));

		$_SESSION["patent"]["prod_ru"] = get_ru();
		$data["results_table"] = $this->get_results_table();

		$data["show_link"] = "javascript:window.open('".$this->mk_my_orb("show", array("print" => 1 , "id" => $_SESSION["patent"]["trademark_id"], "add_obj" => $arr["alias"]["to"]))."','', 'toolbar=no, directories=no, status=no, location=no, resizable=yes, scrollbars=yes, menubar=no, height=600, width=800')";
		$data["convert_link"] = $this->mk_my_orb("pdf", array("print" => 1 , 	"id" => $_SESSION["patent"]["id"], "add_obj" => $arr["alias"]["to"]) , CL_PATENT);
		return $data;
	}

	/**
		@attrib name=find_products nologin=1
		@param ru required type=string
	**/
	function find_products($arr)
	{
		if($_POST["do_post"])
		{
			$_SESSION["patent"]["prod_selection"] =  $_POST["oid"];
			die("
				<script type='text/javascript'>
					window.opener.document.getElementById('stay').value=1;
					window.opener.document.changeform.submit();
					window.close();
				</script>"
			);
		}
//				window.opener.location.href='".$_SESSION["patent"]["prod_ru"]."';

		if($_POST["product"] || $_POST["class"])
		{
			if($_POST["class"])
			{
				$limit = 1700;
			}
			else
			{
				$limit = 500;
			}
			$tpl = "products_res.tpl";
			$is_tpl = $this->read_template($tpl,1);
			classload("vcl/table");
			$t = new vcl_table(array(
				"layout" => "generic",
			));

			$t->define_field(array(
				"name" => "class",
				"caption" => t("Klass"),
			));
			$t->define_field(array(
				"name" => "prod",
				"caption" => t("Kaup/teenus"),
			));
			$t->define_chooser(array(
				"name" => "oid",
				"field" => "oid",
				"caption" => t("Vali"),
			));

			$products = new object_list();
			if(strlen($_POST["class"]) == 1)
			{
				$_POST["class"] = "0".$_POST["class"];
			}
			$parents = new object_list(array(
				"comment" => "%".$_POST["class"]."%",
				"class_id" => CL_MENU ,
				"lang_id" => array(),
				"limit" => $limit,
			));
			$parents->sort_by(array(
				"prop" => "name",
				"order" => "asc"
			));

			foreach ($parents->ids() as $id)
			{
				$prod_list = new object_list(array(
					"userta1" => "%".$_POST["product"]."%",
					"parent" => $id,
					"class_id" => CL_SHOP_PRODUCT ,
					"lang_id" => array(),
					"limit" => $limit,
				));

				foreach($prod_list->arr() as $p)
				{
					if($p->prop("userch10"))
					{
						$prod_list->remove($p->id());
						$products->add($p->id());
					}
				}

				$prod_list->sort_by(array(
					"prop" => "name",
					"order" => "asc"
				));

				$products->add($prod_list);
			}

// 			$products = new object_list(array(
// 				"name" => "%".$_POST["product"]."%",
// 				"parent" => $parents->ids(),
// 				"class_id" => CL_SHOP_PRODUCT ,
// 				"lang_id" => array(),
// 				"limit" => $limit,
// 			));
			//arr(sizeof($products->ids()));
			if($is_tpl)
			{
				$c = "";
				foreach($products->arr() as $prod)
				{
					$parent = obj($prod->parent());
					if($prod->prop("userch10"))
					{
						$p = "<b>".$prod->prop("userta1")."</b>";
					}
					else
					{
						$p = $prod->prop("userta1");
					}
					$this->vars(array(
						"prod" => $p,
						"class" => $parent->name(),
						"code" => 132245,
						"oid"	=> $prod->id(),
					));
					$c .= $this->parse("PRODUCT");
				}
				$this->vars(array(
					"PRODUCT" => $c,
					"ru" => $arr["ru"]
				));
				$result_list =  $this->parse();
			}
			else
			{
				foreach($products->arr() as $prod)
				{
					$parent = obj($prod->parent());
					$t->define_data(array(
						"prod" => $prod->name(),
						"class" => $parent->name(),
						"code" => 132245,
						"oid"	=> $prod->id(),
					));
				}
				$result_list =  "<form action='' method=POST>".$t->draw()."
				<input type=hidden value=".$arr["ru"]." name=ru>
				<input type=hidden value=1 name=do_post>
				<input type=submit value='Lisa valitud terminid taotlusse'>";
			}
		}

		$tpl = "products.tpl";
		$is_tpl = $this->read_template($tpl);

		if($is_tpl)
		{
			$this->vars(array("result" => $result_list));
			return $this->parse();
		}

		$ret = "<form action='' method=POST>Klassi nr:".
		html::textbox(array("name" => "class"))."<br> Kauba/teenuse nimetus".html::textbox(array("name" => "product"))
		."<input type=hidden value=".$arr["ru"]." name=ru><input type=submit value='otsi'></form>";

		return $ret . $result_list;
	}

	protected function save_priority($patent)
	{
		$patent->set_prop("convention_nr" , $_SESSION["patent"]["convention_nr"]);
		$patent->set_prop("convention_date" , $_SESSION["patent"]["convention_date"]);
		$patent->set_prop("convention_country" , $_SESSION["patent"]["convention_country"]);
		$patent->set_prop("exhibition_name" , $_SESSION["patent"]["exhibition_name"]);
		$patent->set_prop("exhibition_date" , $_SESSION["patent"]["exhibition_date"]);
		$patent->set_prop("exhibition_country" , $_SESSION["patent"]["exhibition_country"]);
		$patent->save();
	}

	protected function save_trademark($patent)
	{
		$patent->set_prop("word_mark" , $_SESSION["patent"]["word_mark"]);
		$patent->set_prop("colors" , $_SESSION["patent"]["colors"]);
		$patent->set_prop("trademark_character" , $_SESSION["patent"]["trademark_character"]);
		$patent->set_prop("element_translation" , $_SESSION["patent"]["element_translation"]);
		$patent->set_prop("type" , $_SESSION["patent"]["type"]);
		$patent->set_prop("undefended_parts" , $_SESSION["patent"]["undefended_parts"]);
		$tr_type = array();
		if($_SESSION["patent"]["co_trademark"])
		{
			$tr_type[] = 0;
		}
		if($_SESSION["patent"]["guaranty_trademark"])
		{
			$tr_type[] = 1;
		}
		$patent->set_prop("trademark_type" , $tr_type);
		$patent->save();
	}

	protected function save_forms($patent)
	{
		$this->save_trademark($patent);
		$this->save_priority($patent);
		$this->save_fee($patent);
		$this->save_applicants($patent);
		$this->fileupload_save($patent);
		$this->final_save($patent);
		$patent->set_meta("products" , $_SESSION["patent"]["products"]);
	}

	protected function get_object()
	{
		if(is_oid($_SESSION["patent"]["id"]))
		{
			$patent = obj($_SESSION["patent"]["id"]);
		}
		else
		{
			$patent = new object();
			$patent->set_class_id(CL_PATENT);
			$patent->set_parent($_SESSION["patent"]["parent"]);
			$patent->save();
			$patent->set_name(" Kinnitamata taotlus nr [".$patent->id()."]");
		}
	}

	protected function get_payment_sum($arr)
	{
		$classes = array();
		$sum = 0;
		if(is_array($_SESSION["patent"]["products"]) && sizeof($_SESSION["patent"]["products"]))
		{
			$classes_fee = (sizeof($_SESSION["patent"]["products"]) - 1 )*700;
			if($_SESSION["patent"]["co_trademark"] || $_SESSION["patent"]["guaranty_trademark"])
			{
				$sum = 3000;
			}
			else
			{
				$sum = 2200;
			}
			$sum = $sum + $classes_fee;
			$_SESSION["patent"]["classes_fee"] = $classes_fee;
		}
		return $sum;
	}
}

?>
