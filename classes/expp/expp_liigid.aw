<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_liigid.aw,v 1.1 2005/10/17 10:32:05 dragut Exp $
// expp_liigid.aw - Expp liigid 
/*

@classinfo syslog_type=ST_EXPP_LIIGID relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

/*

DROP TABLE IF EXISTS `expp_liigid`;
CREATE TABLE IF NOT EXISTS `expp_liigid` (
  `id` int(11) NOT NULL auto_increment,
  `tyyp_id` int(11) NOT NULL default '0',
  `sort` tinyint(4) NOT NULL default '0',
  `esilehel` tinyint(4) NOT NULL default '0',
  `liik` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `tyyp_id` (`tyyp_id`),
  KEY `sort` (`sort`),
  KEY `esilehel` (`esilehel`)
) TYPE=MyISAM;

INSERT INTO `expp_liigid` ( `tyyp_id` , `sort` , `esilehel` , `liik` )
VALUES
 ( '1', '1', '1', 'P&auml;evalehed' )
,( '1', '2', '1', 'N&auml;dalalehed' )
,( '1', '3', '1', 'Makonnalehed' )
,( '0', '1', '1', 'Naistele' )
,( '0', '2', '1', 'Meestele' )
,( '0', '3', '1', 'Lastele' )
,( '0', '4', '1', 'Seltskond' )
,( '0', '5', '1', 'Kodu' )
,( '0', '6', '1', 'Kultuur ja Haridus' )
,( '0', '7', '1', 'Sport' )
,( '0', '8', '1', 'Majandus' )
,( '2', '1', '1', 'EE raamatud' )
,( '2', '2', '1', 'AK raamatud' )
;
*/
class expp_liigid extends class_base
{
	function expp_liigid()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "expp",
			"clid" => CL_EXPP_LIIGID
		));
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
//		$ob = new object($arr["id"]);
		$this->read_template("expp_liigid.tpl");

		$retLiik = '';
		$tempLiik = '';
		$retTyyp = '';
		$lastTyyp = '';
		$isCount = 0;

		$this->db_query("SELECT l.id, l.liik, t.nimi as tyyp FROM expp_liigid l, expp_tyybid t WHERE l.tyyp_id = t.id AND l.esilehel = 1 ORDER BY t.sort ASC, t.id, l.sort ASC, l.id");
		while ($row = $this->db_next()) {
			if( $lastTyyp != $row['tyyp'] ) {
				if( !empty( $lastTyyp ) && !empty( $tempLiik )) {
					$this->vars(array(
						'VAHE' => ($isCount == 0?'':$this->parse('VAHE')),
						'nimi' => $lastTyyp
					));
					$retTyyp .= $this->parse('TYYP');
					$this->vars(array(
						'LINE' => $tempLiik
					));
					$retLiik .= $this->parse('SISU');
					$isCount++;
				}
				$lastTyyp = $row['tyyp'];
				$tempLiik = '';
			}

			$this->vars(array(
//				'link' => $row['id'],
				'link' => $this->mk_my_orb( 'show_test', array( 'cid' => 1, 'id' => $row['id'] ),'expp_jupp'),
				'nimi' => $row['liik']
			));
			$tempLiik .= $this->parse('LINE');
		}
		if( !empty( $lastTyyp ) && !empty( $tempLiik )) {
			$this->vars(array(
				'VAHE' => (empty($lastTyyp)?'':$this->parse('VAHE')),
				'nimi' => $lastTyyp
			));
			$retTyyp .= $this->parse('TYYP');
			$this->vars(array(
				'LINE' => $tempLiik
			));
			$retLiik .= $this->parse('SISU');
		}
		$this->vars(array(
			'TYYP' => $retTyyp,
			'SISU' => $retLiik
		));
		return $this->parse();
	}

	//-- methods --//
}
?>