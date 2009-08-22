<?php

class products_show_obj extends _int_object
{
	/** returns template name
		@attrib api=1
		@returns string
			template file name
	**/
	public function get_template()
	{
		if($this->prop("template"))
		{
			return $this->prop("template");
		}
		return "show.tpl";
	}

	/** returns products
		@attrib api=1
		@returns object list
	**/
	public function get_products()
	{
		$ol = new object_list();
		foreach($this->prop("categories") as $category)
		{
			$c = obj($category);
			$ol->add($c->get_products());
		}
		return $ol;
	}

	/** returns all items for showing
		@attrib api=1
		@returns object list
			product , packaging or packet objects
	**/
	public function get_web_items()
	{
enter_function("products_show::get_web_items");
		$categories = $this->get_categories();

		if(!$categories->count() && (!is_array($this->prop("packets")) || !sizeof($this->prop("packets"))))
		{
			$categories = $this->all_lower_categories();
		}

		$ol = new object_list();
		if(false)
		{
			foreach($categories->arr() as $c)
			{
				$products = $c->get_packagings();
				$ol->add($products);
			}
		}
		elseif(false)
		{
			foreach($categories->arr() as $c)
			{
				$products = $c->get_products();
				$ol->add($products);
			}
		}
		else
		{
			$filter = array(
				"class_id" => CL_SHOP_PACKET,
				"lang_id" => array(),
				"site_id" => array(),
				"CL_SHOP_PACKET.RELTYPE_CATEGORY" => $categories->ids(),
			);
			$ol = new object_list($filter);
		}

		if(is_array($this->prop("packets")))
		{
			foreach($this->prop("packets") as $packet)
			{
				$ol->add($packet);
			}
		}
exit_function("products_show::get_web_items");
		return $ol;
	}

	/** returns order center object
		@attrib api=1 params=pos
		@returns object
	**/
	public function get_oc()
	{
		$ol = new object_list(array("class_id" => CL_SHOP_ORDER_CENTER));
		$ol = $ol->arr();
		return reset($ol);
	}

	/** returns categories
		@attrib api=1
		@returns object list
	**/
	public function get_categories()
	{
		$ol = new object_list();	
		if(is_array($this->prop("categories")))
		{
			foreach($this->prop("categories") as $category)
			{
				$ol->add($category);
			}
		}
		return $ol;
	}

	private function all_lower_categories()
	{
		$ol = new object_list();
		$menu = $this->parent();
		$ot = new object_tree(array(
			"parent" => $menu,
//			"class_id" => CL_FILE
		));

	
		foreach($ot->ids() as $s)
		{
			$o = obj($s);
			if($o->class_id() == CL_PRODUCTS_SHOW)
			{
				$ol->add($o->get_categories());
			}
		}
		return $ol;
	}

	/** adds category
		@attrib api=1 params=pos
		@param cat required oid
		@returns true
	**/
	public function add_category($cat)
	{
		if(is_oid($cat))
		{
			$cat = array($cat);
		}
		foreach($cat as $category)
		{
			$this->connect(array(
				"to" => $category,
				"reltype" => "RELTYPE_CATEGORY",
			));
		}
		return true;
	}

	/** removes category
		@attrib api=1 params=pos
		@param cat required oid
	**/
	public function remove_category($cat)
	{
		$this->disconnect(array(
			"from" => $cat,
		));
	}

	/** returns document id where product show object is connected to
		@attrib api=1
		@returns oid
			document object id
	**/
	public function get_document()
	{
		foreach($this->connections_to(array("from.class_id" => CL_DOCUMENT)) as $c)
		{
			return $c->prop("from");
		}
		return null;
	}

	/** returns category site menu id
		@attrib api=1 params=pos
		@param cat required oid
		@returns oid
			menu object id
	**/
	public function get_category_menu($cat)
	{
		$category = obj($cat);
		$ol = new object_list(array(
			"class_id" => CL_PRODUCTS_SHOW,
			"CL_PRODUCTS_SHOW.RELTYPE_CATEGORY" => $cat,
		));
		$ol = $ol->arr();
		$o = reset($ol);
		if(is_object($o))
		{
			$document = $o->get_document();
			if(is_oid($document))
			{
				$doc = obj($document);
				return $doc->parent();
			}
		}

		return null;
	}
}

?>
