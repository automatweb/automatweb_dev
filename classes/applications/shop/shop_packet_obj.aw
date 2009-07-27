<?php

class shop_packet_obj extends _int_object
{
	public function get_products($arr)
	{
		$ol = new object_list(array(
			"class_id" => CL_SHOP_PRODUCT,
			"lang_id" => array(),
			"site_id" => array(),
			"CL_SHOP_PRODUCT.RELTYPE_PRODUCT(CL_SHOP_PACKET)" => $this->id()
		));
		return $ol;
	}
}

?>
