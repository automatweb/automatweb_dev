<?php
/*
@classinfo syslog_type=ST_PRODUCTS_SHOW relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=smeedia
@tableinfo aw_products_show master_index=brother_of master_table=objects index=aw_oid

@default table=aw_products_show
@default group=general

	@property packets type=relpicker multiple=1 store=connect reltype=RELTYPE_PACKET
	@caption Paketid
	@comment Paketid, mida kuvatakse

	@property categories type=relpicker multiple=1 store=connect reltype=RELTYPE_CATEGORY 
	@caption Tootekategooriad
	@comment Tootekategooriad millesse toode peaks kuuluma, et teda kuvataks

	@property columns type=textbox field=aw_columns
	@caption Tulpasid

	@property template type=select
	@caption Toodete n&auml;itamise template

	@property product_template type=select
	@caption &Uuml;he toote n&auml;itamise templeit

	@property type type=select
	@caption N&auml;idatavad klassi t&uuml;&uuml;bid

	@property oc type=relpicker reltype=RELTYPE_OC
	@caption Tellimiskeskkond
	@comment veebipood, mille tooteid see n&auml;itamise objekt n&auml;itab


### RELTYPES

@reltype CATEGORY value=1 clid=CL_SHOP_PRODUCT_CATEGORY
@caption Tootekategooria

@reltype PACKET value=2 clid=CL_SHOP_PACKET
@caption Pakett

@reltype OC value=3 clid=CL_SHOP_ORDER_CENTER
@caption Tellimiskeskkond


*/

class products_show extends class_base
{
	function products_show()
	{
		$this->init(array(
			"tpldir" => "applications/shop/products_show",
			"clid" => CL_PRODUCTS_SHOW
		));
		$this->types = array(
			CL_SHOP_PRODUCT => t("Toode"),
			CL_SHOP_PRODUCT_PACKAGING => t("Pakend"),
			CL_SHOP_PACKET => t("Pakett"),
		);
	}

	/** returns products showing template selection
		@attrib api=1
	**/
	public function templates()
	{
		$tm = get_instance("templatemgr");
		$ret = $tm->template_picker(array(
					"folder" => "applications/shop/products_show/"
				));;
		return $ret;
	}

	/** returns product showing template selection
		@attrib api=1
	**/
	public function product_templates()
	{
		$tm = get_instance("templatemgr");
		$ret = array();

		$dir = "applications/shop/shop_packet";
		$ret = $ret + $tm->template_picker(array(
			"folder" => $dir
		));

		$dir = "applications/shop/shop_product";
		$ret = $ret + $tm->template_picker(array(
			"folder" => $dir
		));
		$dir = "applications/shop/shop_product_packaging";

		$ret = $ret + $tm->template_picker(array(
			"folder" => $dir
		));
		return $ret;
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{
			case "template":
				$prop["options"] = $this->templates();
				if(sizeof($prop["options"]) < 2)
				{
					$prop["caption"].= "<br>".t("templates/applications/shop/products_show/");
				}
				break;

			case "product_template":
				$tm = get_instance("templatemgr");
				switch($arr["obj_inst"]->prop("type"))
				{
					case CL_SHOP_PACKET:
						$dir = "applications/shop/shop_packet";
						break;

					case CL_SHOP_PRODUCT:
						$dir = "applications/shop/shop_product";
						break;

					case CL_SHOP_PRODUCT_PACKAGING:
						$dir = "applications/shop/shop_product_packaging";
						break;
				}
				if($dir)
				{
					$prop["options"] = $tm->template_picker(array(
						"folder" => $dir
					));
					if(sizeof($prop["options"]) < 2)
					{
						$prop["caption"].= "<br>".t("templates/").$dir;
					}
				}
				break;

			case "type":
				$prop["options"] = $this->types;
				break;
		}

		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;

		switch($prop["name"])
		{

		}

		return $retval;
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_products_show(aw_oid int primary key)");
			return true;
		}

		switch($f)
		{
			case "product_template":
			case "template":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "varchar(63)"
				));
				return true;
			
			case "aw_columns":
			case "type":
			case "oc":
				$this->db_add_col($t, array(
					"name" => $f,
					"type" => "int"
				));
				return true;
		}
	}

	function parse_alias($arr = array())
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		if(!empty($_GET["product"]) && $this->can("view" , $_GET["product"]))
		{
			$show_product = obj($_GET["product"]);
			$instance = get_instance($show_product->class_id());
			$instance->template = $ob->prop("product_template");
			return $instance->show(array(
				"id" => $_GET["product"],
				"oc" => $_GET["oc"],
			));
		}

		$this->read_template($ob->get_template());
		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		$products = $ob->get_web_items();
		$oc = $ob->get_oc();
		
		$prod = "";//templeiti muutuja PRODUCT v22rtuseks

		$rows = "";
		
		$max = 4;

		$count = $count_all = 0;
		foreach($products->arr() as $product)
		{
			$product_data = $product->get_data($oc->id());
			$product_data["checkbox"] = html::checkbox(array(
				"name" => "add_to_cart[".$product_data["product_id"]."]",
				"value" => 1,
			));

			$product_data["product_link"] = "/".aw_global_get("section")."?product=".$product_data["id"]."&oc=".$oc->id();
			$ids = $product->get_categories()->ids();
			$category = reset($ids);
			$product_data["menu"] = $ob->get_category_menu($category);
			$product_data["menu_name"] = get_name($product_data["menu"]);
			$this->vars($product_data);//arr($product_data);

			$count++;
			$count_all++;
			if($count >= $max && $this->is_template("ROW"))//viimane tulp yksk6ik mis reas
			{
				$count = 0;
				if($this->is_template("PRODUCT_END"))
				{
					$prod.= $this->parse("PRODUCT_END");
				}
				else
				{
					$prod.= $this->parse("PRODUCT");
				}
				$this->vars(array("PRODUCT" => $prod));
				$rows.= $this->parse("ROW");
				$prod = "";
			}
			elseif($count_all >= $products->count() && $this->is_template("ROW"))//viimane rida
			{
				$prod.= $this->parse("PRODUCT");
				$this->vars(array("PRODUCT" => $prod));
				$rows.= $this->parse("ROW");
			}
			else
			{
				$prod.= $this->parse("PRODUCT");
			}

		}

		$this->vars(array("ROW" => $rows));

		$data = array();
		$cart_inst = get_instance(CL_SHOP_ORDER_CART);
 		$data["submit_url"] = $this->mk_my_orb("submit_add_cart", array(
			"oc" => $oc->id(),
			"id" => $oc->prop("cart"),
		),CL_SHOP_ORDER_CART,false,false,"&amp;");

		if(!substr_count("orb.aw" ,$data["submit_url"] ))
		{
			$data["submit_url"] = str_replace(aw_ini_get("baseurl")."/" ,aw_ini_get("baseurl")."/orb.aw" , $data["submit_url"]);

		}
		$data["oc"] = $oc->id();
		$data["submit"] = html::submit(array(
			"value" => t("Lisa tooted korvi"),
		));
		$data["section"] = aw_global_get("section");
		$this->vars($data);
		return $this->parse();
	}
}

?>
