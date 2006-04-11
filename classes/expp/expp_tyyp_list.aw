<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_tyyp_list.aw,v 1.4 2006/04/11 11:16:06 dragut Exp $
// expp_tyyp_list.aw - Expp tyybi list 
// vana kood!
/*

@classinfo syslog_type=ST_EXPP_TYYP_LIST relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

class expp_tyyp_list extends class_base
{
	function expp_tyyp_list()
	{
	}

	function show($arr)
	{
		$retHTML = '';
		$retArr = array();
		$htmlArr = array();
		$coutArr = array();
//		$ob = new object($arr["id"]);
		$this->read_template("expp_tyyp_list.tpl");

		$_tyyp = $arr[0];
		if( is_numeric( $_tyyp )) {
			$sql = "SELECT id,nimi FROM expp_tyybid WHERE id = '{$_tyyp}'";
		} else {
			$_tyyp = addslashes( urldecode( $_tyyp ));
			$sql = "SELECT id,nimi FROM expp_tyybid WHERE nimi = '{$_tyyp}'";
		}
echo $sql;
		$row = $this->db_fetch_row($sql);
		if (!is_array($row)) {
			return $retHTML;
		}
		
		$_tyyp_nimi = $row['nimi'];
		$_tyyp_id = $row['id'];

		$sql = "SELECT DISTINCT"
			." v.toote_nimetus, l.liik"
			." FROM expp_valjaanne v, expp_liigid l, expp_va_liik vl"
			." WHERE v.toote_tyyp = '{$_tyyp_id}'"
			." AND l.tyyp_id = '{$_tyyp_id}'"
			." AND vl.toote_nimetus = v.toote_nimetus"
			." AND vl.liik_id = l.id";
		$this->db_query($sql);
		while ($row = $this->db_next()) {
			$retArr[$row['liik']][] = $row['toote_nimetus'];
		}
		$_row_count = max( count($retArr), 1);
		$this->colspan = min($_row_count,$this->colspan);
		for( $i = 0; $i< $this->colspan; $i++ ) {
			$countArr[$i] = 0;
			$htmlArr[$i] = '';
		}
		foreach( $retArr as $key => $val ) {
			$_idx = 0;
			for( $i = 0; $i< $this->colspan; $i++ ) {
				if( $countArr[$i] < $countArr[$_idx] ) {
					$_idx = $i;
				}
			}
/*
			ksort( $countArr );
			asort( $countArr );
			reset( $countArr );
			$_idx = key( $countArr );
*/
			$countArr[$_idx] += count($val);
			$_tmp = '';
			foreach( $val as $key1 => $val1 ) {
				$this->vars(array(
					'link' => aw_ini_get("tell_dir").urlencode( $val1 ),
//					$this->mk_my_orb( 'show_test', array( 'cid' => 4, 'id' => ($val1) ),'expp_jupp'),
					'nimi' => $val1
				));
				$_tmp .= $this->parse('LINE');
			}
			$this->vars(array(
				'liik' => $key,
				'LINE' => $_tmp
			));
			$htmlArr[$_idx] .= $this->parse('SISU_BOX');
		}
		$_tmp = '';
		foreach( $htmlArr as $val ) {
			$this->vars(array(
				'SISU_BOX' => $val
			));
			$_tmp .= $this->parse('VISU_BOX');
		}
		$this->vars(array(
			'tyyp' => $_tyyp_nimi,
			'colspan' => $this->colspan,
			'VISU_BOX' => $_tmp
		));
		return $this->parse();
	}

	//-- methods --//
}
?>
