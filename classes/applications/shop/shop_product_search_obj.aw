<?php

class shop_product_search_obj extends _int_object
{
	function get_order_center()
	{
		return new object($this->prop('oc'));
	}
}
