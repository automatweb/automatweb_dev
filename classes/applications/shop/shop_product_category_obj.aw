<?php

class shop_product_category_obj extends _int_object
{
	/** return categories
		@attrib api=1
		@returns
			object list
	**/
	public function get_categories($id = NULL)
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT_CATEGORY,
			"lang_id" => array(),
			"site_id" => array(),
			new object_list_filter(array(
				"logic" => "OR",
				"conditions" => array(
					"parent" => is_oid($id) ? $id : $this->id(),
					"CL_SHOP_PRODUCT_CATEGORY.RELTYPE_CATEGORY" => is_oid($id) ? $id : $this->id(),
				),
			)),
			"sort_by" => "jrk asc, name asc",
		));
		return $ol;
	}

	public function get_categories_hierarchy($id = NULL, $depth = NULL)
	{
		$retval = array();
		if($depth > 0 || $depth === NULL)
		{
			foreach($this->get_categories($id)->ids() as $_id)
			{
				$retval[$_id] = $this->get_categories_hierarchy($_id, $depth -1);
			}
		}
		return $retval;
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

	/** Returns products for category/categories.
		@attrib api=1 params=pos
		@param id optional type=int/array acl=view
		@returns
			Object list or array of object lists if id is given and is array.
	**/
	public function get_products($id = NULL)
	{
		$prms = array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_SHOP_PRODUCT.RELTYPE_CATEGORY" => $id !== NULL ? $id : $this->id(),
		);

		if(is_array($id))
		{
			$ols = array();
			$odl = new object_data_list(
				$prms,
				array(
					CL_SHOP_PRODUCT => array("categories")
				)
			);
			foreach($odl->arr() as $oid => $odata)
			{
				foreach((array)$odata["categories"] as $cat)
				{
					if(!isset($ols[$cat]))
					{
						$ols[$cat] = new object_list;
					}
					$ols[$cat]->add($oid);
				}
			}
			return $ols;
		}
		else
		{
			$ol = new object_list($prms);
			return $ol;
		}
	}

//----------------- static functions -------------------------


}

?>
