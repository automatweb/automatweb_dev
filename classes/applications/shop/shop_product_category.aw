<?php
/*
@classinfo syslog_type=ST_SHOP_PRODUCT_CATEGORY relationmgr=yes no_comment=1 no_status=1 prop_cb=1 maintainer=kristo
@tableinfo aw_shop_product_category master_index=brother_of master_table=objects index=aw_oid

@default table=aw_shop_product_category
@default group=general

@property desc type=textarea rows=10 cols=50 field=aw_desc
@caption Kirjeldus

@property images type=repicker reltype=RELTYPE_IMAGE multiple=1 store=connect 
@caption Pildid

@property doc type=repicker reltype=RELTYPE_DOC field=aw_doc
@caption Dokument

@property folders type=table store=no no_caption=1
@caption Kaustad

*/

class shop_product_category extends class_base
{
	function shop_product_category()
	{
		$this->init(array(
			"tpldir" => "applications/shop/shop_product_category",
			"clid" => CL_SHOP_PRODUCT_CATEGORY
		));
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function do_db_upgrade($t, $f)
	{
		if ($f == "")
		{
			$this->db_query("CREATE TABLE aw_shop_product_category(aw_oid int, aw_desc text, aw_doc int)");
			return true;
		}
	}
}

?>
