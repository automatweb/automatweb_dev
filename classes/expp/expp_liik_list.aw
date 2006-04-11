<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_liik_list.aw,v 1.4 2006/04/11 11:05:46 dragut Exp $
// expp_liik_list.aw - Expp liigi list 
// vana kood!
/*

@classinfo syslog_type=ST_EXPP_LIIK_LIST relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

class expp_liik_list extends class_base
{
	function expp_liik_list()
	{
	}

	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//

		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"]));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$retHTML = '';
		$retArr = array();
		$htmlArr = array();
		$coutArr = array();
//		$ob = new object($arr["id"]);

		$this->read_template("expp_liik_list.tpl");

		$_liik = $arr[0];
		if( is_numeric( $_liik )) {
			$sql = "SELECT id,tyyp_id,liik FROM expp_liigid WHERE id = '{$_liik}'";
		} else {
			$_liik = addslashes(urldecode( $_liik ));
			$sql = "SELECT id,tyyp_id,liik FROM expp_liigid WHERE liik = '{$_liik}'";
		}
echo $sql;
		$row = $this->db_fetch_row($sql);
		if (!is_array($row)) {
			return $retHTML;
		}

		$_liik_nimi = $row['liik'];
		$_liik_id = $row['id'];
		$_tyyp_id = $row['tyyp_id'];

		$sql = "SELECT DISTINCT"
			." v.toote_nimetus"
			." FROM expp_valjaanne v, expp_va_liik vl"
			." WHERE vl.liik_id = '{$_liik_id}'"
			." AND vl.toote_nimetus = v.toote_nimetus";
		$this->db_query($sql);
		$_row_count = max( $this->num_rows(), 1);
		$this->colspan = min( $_row_count, $this->colspan );
		$_row_count = ceil( $_row_count / $this->colspan );
		for( $i = 0; $i< $this->colspan; $i++ ) {
			$htmlArr[$i] = '';
		}
		$_idx = 0;
		$i = 0;
		while ($row = $this->db_next()) {
			$this->vars(array(
				'link' => $this->mk_my_orb( 'show_test', array( 'cid' => 4, 'id' => ($row['toote_nimetus']) ),'expp_jupp'),
				'nimi' => $row['toote_nimetus']
			));
			$htmlArr[$_idx] .= $this->parse('LINE');
			$i++;
			if( $i >= $_row_count ) {
				$_idx++;
				$i = 0;
			}
		}
		$_tmp = '';
		foreach( $htmlArr as $val ) {
			$this->vars(array(
				'SISU_BOX' => $val
			));
			$_tmp .= $this->parse('VISU_BOX');
		}
		$this->vars(array(
			'liik' => $_liik_nimi,
			'colspan' => $this->colspan,
			'VISU_BOX' => $_tmp
		));
		return $this->parse();
	}

	//-- methods --//
}
?>
