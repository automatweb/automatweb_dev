<?php

class shop_product_category_obj extends _int_object
{
	/** return categories
		@attrib api=1
		@returns
			object list
	**/
	public function get_categories()
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT_CATEGORY,
			"lang_id" => array(),
			"site_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"parent" => $this->id(),
					"CL_SHOP_PRODUCT_CATEGORY.RELTYPE_CATEGORY" => $this->id(),
				),
			))
		));
		return $ol;
	}

	/**
		@attrib api=1
	**/
	public function set_category($id)
	{
		$this->connect(array(
			"to" => $id,
			"reltype" => "RELTYPE_CATEGORY",
		));
	}

	/** adds cate3gory type to category... type in category
		@attrib api=1
	**/
	public function add_type($id)
	{
		$this->connect(array(
			"to" => $id,
			"reltype" => "RELTYPE_CATEGORY_TYPES",
		));
	}

	/** removes category type from category... type in category
		@attrib api=1
	**/
	public function remove_type($id)
	{
		$this->disconnect(array("from" => $id));
	}

	/** sets category type - category under type
		@attrib api=1
		@returns
			none
	**/
	public function set_category_type($id)
	{
		$o = new object($id);
		$o->add_category($this->id());
	}

	/** return category types
		@attrib api=1
		@returns
			object list
	**/
	public function get_gategory_types()
	{
		$ol = new object_list();
		$conn = $this->connections_from(array(
			"type" => "RELTYPE_CATEGORY_TYPES",
		));
		foreach($conn as $c)
		{
			$ol->add($c->prop("to"));
		}
		return $ol;
	}

	/** return category products
		@attrib api=1
		@returns
			object list
	**/
	public function get_products()
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_SHOP_PRODUCT.RELTYPE_CATEGORY" => $this->id(),
		));
		return $ol;

	}


}

?>
