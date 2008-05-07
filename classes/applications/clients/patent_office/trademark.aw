<?php
// trademark.aw - Trademark
/*

@classinfo syslog_type=ST_TRADEMARK relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=markop
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



*/

class trademark extends intellectual_property
{
	function __construct()
	{
		parent::__construct();
		$this->init(array(
			"tpldir" => "applications/patent",
			"clid" => CL_TRADEMARK
		));
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
		//$products = nee object_list(array("class_id" => CL_SHOP_PRODUCT,"lang_id" => array()));
//		$address_inst = get_instance(CL_CRM_ADDRESS);
		$ret = "<form action='' method=POST>Klassi nr:".
		html::textbox(array("name" => "class"))."<br> Kauba/teenuse nimetus".html::textbox(array("name" => "product"))

		."<input type=hidden value=".$arr["ru"]." name=ru><input type=submit value='otsi'></form>";
//		foreach($address_inst->get_country_list() as $key=> $val)
//		{
//
//			$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.document.exhibition_country.value=".$key."'>".$val."</a><br>";
		//	$ret .= "<a href='javascript:void(0)' onClick='javascript:window.opener.changeform.exhibition_country.value=".$key."'>".$val."</a><br>";
//		}
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
			$patent->set_class_id(CL_TRADEMARK);
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
