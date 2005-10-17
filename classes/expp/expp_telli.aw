<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_telli.aw,v 1.1 2005/10/17 10:32:05 dragut Exp $
// expp_telli.aw - Expp telli 
/*

@classinfo syslog_type=ST_EXPP_TELLI relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

/*
DROP TABLE IF EXISTS `expp_korv`;
CREATE TABLE `expp_korv` (
  `id` int(11) NOT NULL auto_increment,
  `session` varchar(63) NOT NULL default '',
  `pindeks` int(11) NOT NULL default '0',
  `eksemplar` int(11) NOT NULL default '0',
  `algus` varchar(6) NOT NULL default '',
  `leping` varchar(4) NOT NULL default '',
  `pikkus` int(11) NOT NULL default '0',
  `kogus` int(11) NOT NULL default '0',
  `time` timestamp(14) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `session` (`session`),
  KEY `pindeks` (`pindeks`)
) TYPE=MyISAM;

*/
class expp_telli extends class_base {

	var $cy;
	var $cp;
	var $post_errors = array();
	var $lang;

	function expp_telli() {
		$this->lang = aw_global_get("admin_lang_lc");

		$this->init(array(
			"tpldir" => "expp",
			"clid" => CL_EXPP_TELLI
		));
		$this->cy = get_instance( CL_EXPP_JAH );
		$this->cp = get_instance( CL_EXPP_PARSE );
		lc_site_load( "expp", $this );
	}

	function show($arr) {
		global $lc_expp;
		$retHTML = '';
		if( empty( $this->cp->pids )) return $retHTML;
		if( isset( $GLOBALS['HTTP_POST_VARS']['tyhista'] ) || isset( $GLOBALS['HTTP_POST_VARS']['tyhista_y'] )) {
			$this->returnPost();
		}
		if( isset( $GLOBALS['HTTP_POST_VARS']['salvesta'] ) || isset( $GLOBALS['HTTP_POST_VARS']['salvesta_y'] )) {
			$this->parsePost();
		}

		$_aid = $this->cp->getPid( 2 );
		if( empty( $_aid )) return $retHTML;

		$_pikkus = intval( $this->cp->getVal('pikkus'));
		$_leping = $this->cp->getVal( 'leping' );

// KIRJELDUS
		$_vanne = array();
		if( $this->lang != 'et' ) {
			$sql = "SELECT v.pindeks, v.toote_nimetus, v.kampaania, v.veebi_kirjeldus, tr1.nimetus as lang_va, tr2.nimetus as lang_toode"
					." FROM expp_valjaanne v"
					." LEFT JOIN expp_translate tr1 ON tr1.pindeks = v.pindeks AND tr1.tyyp = 'va' AND tr1.lang = '{$this->lang}'"
					." LEFT JOIN expp_translate tr2 ON tr2.pindeks = v.pindeks AND tr2.tyyp = 'toode' AND tr2.lang = '{$this->lang}'"
					." WHERE v.valjaande_nimetus='{$_aid}'";
		} else {
			$sql = "SELECT v.pindeks, v.toote_nimetus, v.kampaania, v.veebi_kirjeldus"
					." FROM expp_valjaanne v"
					." WHERE v.valjaande_nimetus='{$_aid}'";
		}
		$row = $this->db_fetch_row( $sql );
		if( $this->num_rows() == 0 ) return $retHTML;

		$_ch_logo = str_replace( '%', '#', urlencode( stripslashes( $row['toote_nimetus'] ))); // ."_logo";
		$cl = get_instance( CL_EXPP_SITE_LOGO );
		$cl->register( $_ch_logo );
		if( $this->lang != 'et' ) {
			$_laid = ( isset( $row['lang_toode'] ) && !empty( $row['lang_toode'] ) ? $row['lang_toode'] : $_aid );
		} else {
			$_laid = $_aid;
		}

		$myURL = $this->cp->addYah( array(
				'link' => 'telli/'.urlencode( $_aid ),
				'text' => $lc_expp['LC_EXPP_TELLIMINE'].': '.$_laid,
			));

		$this->vars(array(
			'TEXT' => stripslashes( $row['veebi_kirjeldus'] ),
		));
		$_kirjeldus	= $this->parse( 'KIRJELDUS' );
		$_pindeks	= $row['pindeks'];

		$sql = "SELECT h.* FROM"
				." expp_hind h, expp_valjaanne v"
				." WHERE h.pindeks=v.pindeks"
//				." AND (h.algus is null OR h.algus <= now())"
//				." AND (h.lopp is null OR h.lopp >= now())"
				." AND (h.algus+0 = 0 OR h.algus <= now())"
				." AND (h.lopp+0 = 0 OR h.lopp >= now())"
//				." AND h.hinna_liik not like '%SALAJANE%'"
				." AND h.hinna_liik in ( 'OKHIND', 'AVALIK_OKHIND', 'TAVAHIND', 'AVALIK_TAVAHIND' )"
				." AND v.valjaande_nimetus='{$_aid}'";
		$this->db_query( $sql );
		if( $this->num_rows() == 0 ) return $retHTML;

		$my_otsek 	= array();
		$my_tavah	= array();
		while ( $row = $this->db_next()) {
			$row['baashind']  = sprintf( "%1.0f", $row['baashind'] );
			switch( $row["hinna_liik"] ) {
				case 'OKHIND':
				case 'AVALIK_OKHIND':
				case 'SALAJANE_OKHIND':
					$my_otsek[]	= $row;
					if( $_pikkus > 0 && $_pikkus == $row['id'] ) $_leping = 'ok';
					break;
/*
				case 'OK_TAVA':
					$my_otsek[]	= $row;
*/
				case 'TAVAHIND':
				case 'AVALIK_TAVAHIND':
				case 'SALAJANE_TAVAHIND':
				default:
					$my_tavah[]	= $row;
					if( $_pikkus > 0 && $_pikkus == $row['id'] ) $_leping = 'tel';
			}	// switch
		}
		$kogus		= intval($GLOBALS["HTTP_POST_VARS"]["kogus"]?$GLOBALS["HTTP_POST_VARS"]["kogus"]:$GLOBALS["HTTP_GET_VARS"]["kogus"]?$GLOBALS["HTTP_GET_VARS"]["kogus"]:1);
		if( $kogus < 1 ) $kogus = 1;

		$this->read_template("expp_periood.tpl");

		$_action			= $this->cy->getURL().urlencode( $_aid );
		$_pealkiri		= stripslashes( $_aid );;

// TINGIMUSED
		$_link = "";
		$this->vars(array(
			'LINK' => $_link,
		));
		$_tingimused   = $this->parse( 'TINGIMUSED' );

// VEAD
		if( !empty( $this->post_errors )) {
			$_viga = "";
			foreach( $this->post_errors as $_text ) {
				$this->vars(array(
					'TEXT' => $_text,
				));
				$_viga .= $this->parse( 'VIGA' );
			}
			$this->vars(array(
				'VIGA' => $_viga,
			));
			$_vead = $this->parse( 'VEAD' );
		} else {
			$_vead = '';
		}

// KUUPAEV
		$_kuup_options = array(
			'' => '-------------------------'
		);
		if ( strncmp ( $_aid, 'Postimees', 9 ) != 0 )
			$_kuup_options['ASAP'] = $lc_expp['LC_EXPP_ASAP'];
		$_kuup_options['CONT'] = $lc_expp['LC_EXPP_CONT'];
		if ( date("d",mktime(0,0,0,date("m"),date("d")+14,date("Y")))< 15 ) $jn = 1;
		else	$jn = 0;
		for ( $in=1;$in<13;$in++ ) {
			$ajut = mktime(0,0,0,date("m")+$in+$jn,1,date("Y"));
			$year = date("Y",$ajut);
			$month = date("m",$ajut);
			$_value = date("Ym",$ajut);
			$_text = get_lc_month(intval($month))." $year";
			$_kuup_options[$_value] = $_text;
		}	// for
		$_kuupaev = html::select( array(
			'name' => 'algus',
			'options' => $_kuup_options,
			'selected' => $this->cp->getVal('algus'),
			'class' => 'formElement',
		));

/*
		$_kuupaev = "";
		if ( strncmp ( $_aid, 'Postimees', 9 ) != 0 ) {
			$_value = 'ASAP';
			$_checked = ($_algus == $_value?'selected':'');
			$_text = $lc_expp['LC_EXPP_ASAP'];
			$this->vars(array(
				'VALUE'	=> $_value,
				'CHECKED'=> $_checked,
				'TEXT'   => $_text,
			));
			$_kuupaev .= $this->parse( 'KUUPAEV' );
		}
		if ( date("d",mktime(0,0,0,date("m"),date("d")+14,date("Y")))< 15 ) $jn = 1;
		else	$jn = 0;
		for ( $in=1;$in<13;$in++ ) {
			$ajut = mktime(0,0,0,date("m")+$in+$jn,1,date("Y"));
			$year = date("Y",$ajut);
			$month = date("m",$ajut);

			$_value = date("Ym",$ajut);
			$_checked = ($_algus == $_value?'selected':'');
			$_text = get_lc_month(intval($month))." $year";

			$this->vars(array(
				'VALUE'	=> $_value,
				'CHECKED'=> $_checked,
				'TEXT'   => $_text,
			));
			$_kuupaev .= $this->parse( 'KUUPAEV' );
		}	// for
		$this->vars(array(
			'VALUE'	=> $_value,
			'CHECKED'=> $_checked,
			'TEXT'   => $_text,
		));
		$_kuupaev      = $this->parse( 'KUUPAEV' );
*/


//	kui on olemas otsekorraldusega hinnad
// OTSEKORRALDUS
		if( count( $my_otsek ) && $_leping != 'tel' ) {
			$_hind = $my_otsek[0]["baashind"];
			switch( $my_otsek[0]["hinnatyyp"] ) {
				case 0: $_periood = $my_otsek[0]["kestus"]." kuu";
					break;
				case 1:	$_periood = $my_otsek[0]["kestus"]." n&auml;dal";
					break;
				case 2:	$_periood = $my_otsek[0]["kestus"]." p&auml;ev";
					break;
				case 3:	$_periood = $my_otsek[0]["kestus"]." number";
					break;
				case 4:	$_periood = $my_otsek[0]["kestus"]." aasta";
					break;
				case 5:	$_periood = $my_otsek[0]["kestus"]." poolkuu";
			}	// switch

			$_checked = ( $_leping == "ok" || !count($my_tavah) || $my_otsek[0]["id"] == $_pikkus ?"checked":"");
			$_okpikkus= $my_otsek[0]["id"];
			$this->vars(array(
				'CHECKED' => $_checked,
				'OKPIKKUS'=> $_okpikkus,
				'PERIOOD' => $_periood,
				'HIND'    => $_hind,
			));
			$_otsekorraldus= $this->parse( 'OTSEKORRALDUS' );
		} else {
			$_otsekorraldus= "";
		}

// TELLIMUS
		if( count( $my_tavah ) && $_leping != 'ok' ) {
			$_per_options = array(
				'' => '-------------------------'
			);
			$_check = 0;
/*
			if ( strncmp( $_aid, "Eesti Ekspress", 14 ) == 0 ) {
				for( $in=1;$in<13;$in++) {
					$_per_options[$in] = $in." ".(($in>1)?"kuud":"kuu");
				}
			} else {
*/
				for( $in=0;$in<count($my_tavah);$in++) {
					if( $my_tavah[$in]["id"] == $_pikkus ) $_check = 1;
					$_kestus = $my_tavah[$in]["kestus"];
					switch( $my_tavah[$in]["hinnatyyp"] ) {
						case 0: $_kestus.= ($my_tavah[$in]["kestus"]>1)?" kuud ":" kuu ";
							break;
						case 1:	$_kestus.= ($my_tavah[$in]["kestus"]>1)?" n&auml;dalat ":" n&auml;dal ";
							break;
						case 2:	$_kestus.= ($my_tavah[$in]["kestus"]>1)?" p&auml;eva ":" p&auml;ev ";
							break;
						case 3:	$_kestus.= ($my_tavah[$in]["kestus"]>1)?" numbrit ":" number ";
							break;
						case 4:	$_kestus.= ($my_tavah[$in]["kestus"]>1)?" aastat ":" aasta ";
							break;
						case 5:	$_kestus.= ($my_tavah[$in]["kestus"]>1)?" poolkuud ":" poolkuu ";
					}	// switch
					$_kestus.= $my_tavah[$in]["baashind"];
					$_per_options[$my_tavah[$in]["id"]] = $_kestus;
				}	// for
/*
			} // if
*/
			$_periood = html::select( array(
				'name' => 'pikkus',
				'options' => $_per_options,
				'selected' => $_pikkus,
				'class' => 'formElement',
			));
			if (( (int)count($my_tavah) > 1) || (strncmp( $aid, "Eesti Ekspress", 14 ) == 0)) {
				$_yxper	= $this->parse( 'YXPER' );
				$_mituper= '';
			} else {
				$_yxper = '';
				$_kogus = $this->cp->getVal('kogus');
				$this->vars(array(
					'KOGUS'  => ($_kogus == 0? 1 : $_kogus),
				));
				$_mituper = $this->parse( 'MITUPER' );
			}
			$_checked = ($_leping == "tel" || !count($my_otsek) || $_check == 1 ?"checked":"");

			$this->vars(array(
				'CHECKED' => $_checked,
				'PERIOOD' => $_periood,
				'YXPER'   => $_yxper,
				'MITUPER' => $_mituper,
			));			
			$_tellimus     = $this->parse( 'TELLIMUS' );
		} else {
			$_tellimus     = "";
		}

		$_eksemplar	= intval( $this->cp->getVal( 'eksemplar' ));
		if( $_eksemplar < 1 ) $_eksemplar = 1;

		$this->vars(array(
			'ACTION'			=> $_action,
			'PEALKIRI'     => $_pealkiri,
			'TINGIMUSED'   => $_tingimused,
			'KIRJELDUS'    => $_kirjeldus,
			'VEAD'         => $_vead,
			'KUUPAEV'      => $_kuupaev,
			'OTSEKORRALDUS'=> $_otsekorraldus,
			'TELLIMUS'     => $_tellimus,
			'EKSEMPLAR'    => $_eksemplar,
			'PINDEKS'		=> $_pindeks,
		));
		return $this->parse();
	}
	//-- methods --//
	function parsePost() {
		global $lc_expp;
		$vead = array(
			"rid"			=> '',
			"eksemplar"	=>	'LC_EXPP_EKSEMPLAR',
			"algus"		=>	'LC_EXPP_ALGUS',
			"leping"		=>	'LC_EXPP_LEPING',
//			"kogus"		=>	'LC_EXPP_KOGUS',
			"tingimused"=> 'LC_EXPP_TINGIMUSED',
		);
		foreach( $vead as $key => $val ) {
			if( !isset( $GLOBALS['HTTP_POST_VARS'][$key] ) || empty( $GLOBALS['HTTP_POST_VARS'][$key] )) {
				$this->post_errors[] = $lc_expp[$val];
			}
			$name = "_$key";
			$$name = addslashes( $GLOBALS['HTTP_POST_VARS'][$key] );
		}

		$_eksemplar = intval( $_eksemplar);
		switch( $_leping ) {
			case "tel" :
				$_pikkus = $GLOBALS['HTTP_POST_VARS']['pikkus'];
				$_kogus = $GLOBALS['HTTP_POST_VARS']['kogus'];
				break;
			case "ok" :
				$_pikkus = $GLOBALS['HTTP_POST_VARS']['okpikkus'];
				$_kogus = 1;
				break;
		}
		$_pikkus = intval( $_pikkus );
		if ( $_pikkus == 0 ) {
			$this->post_errors[] = "viga:".$lc_expp['LC_EXPP_PIKKUS'];
		}
		$_kogus = intval( $_kogus );
		if ( $_kogus == 0 ) {
			$this->post_errors[] = "viga:".$lc_expp['LC_EXPP_KOGUS'];
		}

		if( !empty( $this->post_errors )) return;

		$sql = "INSERT INTO expp_korv SET session='".session_id()."'"
			.", pindeks='{$_rid}'"
			.", eksemplar='{$_eksemplar}'"
			.", algus='{$_algus}'"
			.", leping='{$_leping}'"
			.", pikkus='{$_pikkus}'"
			.", kogus='{$_kogus}'"
			.", time=now()";
		$this->db_query( $sql );
		
/*
//	Eesti Ekspress special
//	if (( $rid == 69830 ) and ( $leping == "tel" )) {
	if ( strncmp( $aid, "Eesti Ekspress", 14 ) == 0 and ( $leping == "tel" )) {
$query=<<<query
SELECT id
FROM hinnad
WHERE
	pindeks='$rid' AND
	ok_tava_hind='2'
query;
	$result	= @mysql_db_query( $db_base, $query, $dbh );
	if ( @mysql_num_rows( $result ) > 0 ) {
		$row = @mysql_fetch_array( $result );
		switch ( $algus ) {
			case "ASAP":
				$my_begin	= mktime( 0,0,0,date("m"),date("d")+14,date("Y"));
				$my_end		= mktime( 0,0,0,date("m")+$pikkus,date("d")+14,date("Y"));
				break;
			default:
				$my_begin	= mktime( 0,0,0,(int)substr($algus,4,2),1,(int)substr($algus,0,4));
				$my_end		= mktime( 0,0,0,$pikkus+(int)substr($algus,4,2),0,(int)substr($algus,0,4));
		}
		$first_day	= (date( "w",$my_begin)>4)?date( "w",$my_begin)-7:date( "w",$my_begin);
		$kogus = ($my_end - $my_begin)/60/60/24+1;
		$kogus = floor(($kogus+$first_day+2) / 7);
		$pikkus = $row["id"];
	} else {
		$pid = algus;
		header( "location: index.php3".make_argc( "id", "pid" ));
		exit;
	}
}	//	Eesti Ekspress special
*/
		header( "Location: ".aw_ini_get("baseurl")."/tellimine/korv/" );
		exit;
	}
	
	function returnPost() {
		$_aid = $this->cp->getPid( 2 );
		header( "Location: ".aw_ini_get("baseurl")."/tellimine/{$_aid}" );
		exit;
	}
}
?>