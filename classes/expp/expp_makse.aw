<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_makse.aw,v 1.1 2005/10/17 10:32:05 dragut Exp $
// expp_makse.aw - Expp makse 
/*

@classinfo syslog_type=ST_EXPP_MAKSE relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class expp_makse extends class_base {
	var $cy;
	var $cp;

var $lingid = array(
	'hansapank' => array(
		'url'		=> "javascript:oppwin('my_link&pank=hansapank');",
		'text'	=> "LC_EXPP_HANSAPANK",
	),
	'yhispank'	=> array(
		'url'		=> "javascript:oppwin('my_link&pank=yhispank');",
		'text'	=> "LC_EXPP_YHISPANK",
	),
	'sampopank'	=> array(
		'url'		=> "javascript:oppwin('my_link&pank=sampopank');",
		'text'	=> "LC_EXPP_SAMPOPANK",
	),
	'krediidipank'	=> array(
		'url'		=> "javascript:oppwin('my_link&pank=krediidipank');",
		'text'	=> "LC_EXPP_KREDIIDIPANK",
	),
	'nordeapank'	=> array(
		'url'		=> "javascript:oppwin('my_link&pank=nordeapank');",
		'text'	=> "LC_EXPP_NORDEAPANK",
	),
	'postiga'	=> array(
		'url'		=> "javascript:oppwin('my_link&pank=post');",
		'text'	=> "LC_EXPP_POSTIGA",
	),
);
var $pangad = array(
	""					=> "--- Vali makse meetod ---",
	"hanza.net"		=> "tasun hanza.net-is - Hansapanga internetipank",
	"U-net"			=> "tasun U-net-is - SEB Eesti &Uuml;hispanga internetipank",
	"samponet"		=> "tasun S@mpo Internetipangas",
	"nordeapank"	=> "tasun Solo Internetis - Nordea Internetipangas",
	"krediidipank"	=>"tasun Krediidipanga i-pangas",
	"kontor"			=> "m&auml;rgin &uuml;les arve andmed ja tasun nende alusel pangakontoris",
	"kodu"			=> "tellin arve postiga ja tasun selle alusel",
);
	function expp_makse() {
		$this->init(array(
			"tpldir" => "expp",
			"clid" => CL_EXPP_MAKSE
		));
		$this->cy = get_instance( CL_EXPP_JAH );
		$this->cp = get_instance( CL_EXPP_PARSE );
		lc_site_load( "expp", $this );
	}

	function show($arr) {
		global $lc_expp;
		$retHTML		= '';
		$_okleping	= '';
		$_teleping	= '';
		
		if( empty( $this->cp->pids )) return $retHTML;

		if( isset( $GLOBALS['HTTP_POST_VARS']['edasi'] ) || isset( $GLOBALS['HTTP_POST_VARS']['edasi_y'] )) {
			return $this->parsePost();
		};

		$this->read_template("expp_maksevalik.tpl");

		$sql = "SELECT a.arvenr, a.viitenumber, a.maksumus, a.algus, a.lisarida, t.toote_nimetus"
			." FROM expp_arved a LEFT JOIN expp_valjaanne t ON a.vaindeks=t.pindeks"
			." WHERE session='".session_id()."' AND leping='ok'";
		$this->db_query( $sql );
		$_ok_count = $this->num_rows();
		if( $_ok_count > 0 ) {
			$_okline = '';
			$_oklink	= '';
			$_hansacase = '';
			while( $row = $this->db_next()) {
				$this->vars(array(
					'OKLEPINGUNR'	=> $row["arvenr"],
					'OKVIITENR'		=> $row["viitenumber"],
					'TOODE'			=> stripslashes($row["toote_nimetus"]),
					'LEPING'			=> ($row["algus"]==date("d.m.Y")?"<b>".$row["lisarida"]."</b>":"alates <b>".$row["algus"]."</b>"),
					'HIND'			=> $row["maksumus"],
					'INPUT'			=> ($_ok_count > 1 ? 'radio' : 'hidden' ),
				));
				$_okline .= $this->parse( 'OKLINE' );
			}	//	while
			if( $_ok_count > 1 ) {
				$_hansacase = $this->parse( 'HANSACASE' );
			}
			foreach( $this->lingid as $key => $val ) {
				$this->vars(array(
					'url'		=> $val['url'],
//					'target'
					'text'	=> $lc_expp[$val['text']],
				));
				$_oklink .= $this->parse( 'OKLINK' );
			}
			$this->vars(array(
				'HANSACASE'	=> $_hansacase,
				'OKLINE'		=> $_okline,
				'OKLINK'		=> $_oklink,
			));
			$_okleping .= $this->parse( 'OTSEKORRALDUS' );
		}
		$sql = "SELECT a.arvenr, a.viitenumber, a.maksumus, a.algus, a.lopp, a.lisarida, t.toote_nimetus"
			." FROM expp_arved a LEFT JOIN expp_valjaanne t ON a.vaindeks=t.pindeks"
			." WHERE session='".session_id()."' AND leping='tel'";
		$this->db_query( $sql );
		$_tel_count = $this->num_rows();
		if( $_tel_count > 0 ) {
			$_summa = 0;
			$_teline = '';
			$_tellep = '';
			$_old_arve = '';
			$_old_viide = '';
			while( $row = $this->db_next()) {
				if( $_old_arve != $row['arvenr'] ) {
					if( !empty( $_teline )) {
						$this->vars(array(
							'TELLINE' => $_teline,
							'SUMMA'	 => $_summa,
						));
						$_tellep .= $this->parse( 'TELLEP' );
					}
					$this->vars(array(
						'LEPINGUNR'	=> $row['arvenr'],
						'VIITENR'	=> $row["viitenumber"],
					));
					$_old_arve	= $row['arvenr'];
					$_teline 	= '';
					$_summa		= 0;
				}
				$_summa	+= $row['maksumus'];
				$this->vars(array(
					'TOODE'	=> stripslashes($row["toote_nimetus"]),
					'LEPING'	=> ( $row["algus"]==date("d.m.Y")?$row["lisarida"]:"<b>".$row["algus"]."</b> - <b>".$row["lopp"]."</b>"),
					'HIND'	=> $row["maksumus"],
				));
				$_teline .= $this->parse( 'TELLINE' );
			}
			if( !empty( $_teline )) {
				$this->vars(array(
					'TELLINE' => $_teline,
					'SUMMA'	 => $_summa,
				));
				$_tellep .= $this->parse( 'TELLEP' );
			}
			$_pangad = html::select(array(
					'name' => 'maakond',
						'options' => $this->pangad,
						'selected' => '',
						'class' => 'formElement',
					));
			$this->vars(array(
				'TELLEP' => $_tellep,
				'makse_meetod' =>	$_pangad,
			));
			$_teleping = $this->parse( 'TAVALEPING' );
		}
		if( $_tel_count < 1 && $_ok_count < 1 ) {
			return $retHTML;
		}

		$_aid = $this->cp->getPid( 2 );
		$myURL = $this->cp->addYah( array(
				'link' => 'makse',
				'text' => $lc_expp['LC_EXPP_TITLE_MAKSMINE'],
			));

		$this->read_template("expp_maksevalik.tpl");
		$this->vars(array(
			'ACTION'			=> $myURL,
			'OTSEKORRALDUS'=> $_okleping,
			'TAVALEPING'	=> $_teleping,
		));
		$retHTML .= $this->parse();
		return $retHTML;
	}

	function parsePost() {
		$myURL = $this->cp->addYah( array(
				'link' => 'makse',
				'text' => 'Edasi Hansapanka',
			));
		$this->read_template("expp_pank.tpl");
		return $this->parse();
	}
}
?>