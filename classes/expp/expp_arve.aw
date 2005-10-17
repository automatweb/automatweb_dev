<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_arve.aw,v 1.1 2005/10/17 10:32:05 dragut Exp $
// expp_arve.aw - Expp arve 
/*

@classinfo syslog_type=ST_EXPP_ARVE relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

class expp_arve extends class_base {
	var $cp;
	var $valjad = array(
		'nimi'		=> 'nimi',
		'enimi'		=> 'enimi',
		'email'		=> 'email',
		'tanav'		=> 'tanav',
		'maja'		=> 'maja',
		'korter'		=> 'korter',
		'telefon'	=> 'telefon',
		'faks'		=> 'fax',
		'indeks'		=> 'indeks',
		'aadress'	=> 'aadress',
		'isikukood'	=> 'isikukood',
	);
	function expp_arve() {
		$this->init(array(
			"tpldir" => "expp",
			"clid" => CL_EXPP_ARVE
		));
		$this->cp = get_instance( CL_EXPP_PARSE );
		lc_site_load( "expp", $this );
	}

	function show($arr) {
		global $lc_expp;
		if( isset( $GLOBALS['HTTP_POST_VARS']['tagasi'] ) || isset( $GLOBALS['HTTP_POST_VARS']['tagasi_y'] )) {
			$this->returnPost();
		}
		if( isset( $GLOBALS['HTTP_POST_VARS']['edasi'] ) || isset( $GLOBALS['HTTP_POST_VARS']['edasi_y'] )) {
			$this->parsePost();
		}
		$_action = $this->cp->addYah( array(
				'link' => 'arve',
				'text' => 'Arve koostamine',
			));

		$sql = "SELECT * FROM expp_tellija WHERE session='".session_id()."' AND staatus='tellija' ORDER BY time DESC LIMIT 1";
		$row = $this->db_fetch_row( $sql );
		if( $this->num_rows() < 1 ) {
			header( "Location: ".aw_ini_get("baseurl")."/tellimine/korv/" );
			exit;
		}
		$this->read_template("expp_arve.tpl");

		$_kood	= ($row['tyyp']=="firma")?"Registri nr. <b>".$row["isikukood"]."</b>":"Isikukood <b>".$row["isikukood"]."</b>";
		$_isik1	= $this->getIsik( $row );
		$this->vars( array(
			'PEALKIRI' => 'Tellija andmed:',
			'SISU' => $_isik1,
		));
		$_isik = $this->parse( 'ISIK' );
		
		if( $row['toimetus'] != 'sama' ) {
			$sql = "SELECT * FROM expp_tellija WHERE session='".session_id()."' AND staatus='saaja' ORDER BY time DESC LIMIT 1";
			$row = $this->db_fetch_row( $sql );
			if( $this->num_rows() < 1 ) {
				header( "Location: ".aw_ini_get("baseurl")."/tellimine/tellija/" );
				exit;
			}
			$_isik1 = $this->getIsik( $row );
		}
		$this->vars( array(
			'PEALKIRI' => 'Saaja andmed:',
			'SISU' => $_isik1,
		));
		$_isik .= $this->parse( 'ISIK' );


		$sql = "SELECT k.id"
			.", k.eksemplar"
			.", k.algus"
			.", k.leping"
			.", k.kogus"
			.", t.valjaande_nimetus"
			.", h.kestus"
			.", h.hinna_tyyp"
			.", h.baashind"
			.", h.juurdekasv"
		." FROM expp_korv k ,expp_valjaanne t, expp_hind h"
		." WHERE k.session='".session_id()."' AND k.pindeks=t.pindeks AND k.pikkus=h.id"
		." ORDER BY k.leping DESC, t.valjaande_nimetus ASC";
		$this->db_query($sql);
		if( $this->num_rows() == 0 ) {
			header( "Location: ".aw_ini_get("baseurl")."/tellimine/korv/" );
			exit;
		}
		$_out_rows = array();
		$_sum_rows = array();
		while ($row = $this->db_next()) {
			$_toode = stripslashes( $row["valjaande_nimetus"] );
			$_algus = ($row["algus"] == "ASAP")?$lc_expp['LC_EXPP_ASAP']."<br />": (($row["algus"] == "CONT")?$lc_expp['LC_EXPP_CONT']."<br />" :	get_lc_month(intval(substr( $row["algus"],4,2)))." ".substr( $row["algus"],0,4));
			if (!isset( $_out_rows[$row["leping"]]))		$_out_rows[$row["leping"]] = "";
			$_eksemplar	= intval( $row["eksemplar"] );
			$_kogus		= intval( $row["kogus"] );
			$_kestus		= intval( $row["kestus"] );
			$_hind		= $_eksemplar*(float)$row["baashind"];
			if ( $_kogus > 1 )
				$_hind += ($_kogus- 1)*(float)$row["juurdekasv"]*$_kestus*$_eksemplar;
			if (!isset( $_sum_rows[$row["leping"]]))
				$_sum_rows[$row["leping"]] = $_hind;
			else
				$_sum_rows[$row["leping"]]+= $_hind;
			$_kestus = $_kogus* $_kestus;
			switch( $row["hinna_tyyp"] ) {
				case 0: $_hinnatyyp=( $_kestus== 1 ?"kuu":"kuud");
					break;
				case 1:	$_hinnatyyp=( $_kestus== 1 ?"n&auml;dal":"n&auml;dalat");
					break;
				case 2:	$_hinnatyyp=( $_kestus== 1 ?"p&auml;ev":"p&auml;eva");
					break;
				case 3:	$_hinnatyyp=( $_kestus== 1 ?"number":"numbrit");
					break;
				case 4:	$_hinnatyyp=( $_kestus== 1 ?"aasta":"aastat");
					break;
	 			case 5:	$_hinnatyyp=( $_kestus== 1 ?"poolkuu":"poolkuud");
			}
			$_hind = sprintf( "%1.0d", $_hind );
			$this->vars(array(
				'TOODE'  => $_toode,
				'KOGUS'  => $_eksemplar,
				'LEPING' => "{$_algus} ({$_kestus} {$_hinnatyyp})",
				'HIND'   => $_hind,
				'LINK'   => $_action.'?kustuta='.intval($row['id']),
			));
			$_out_rows[$row['leping']] .= $this->parse( 'RIDA' );
		}
		$_leping = '';
		foreach( $_out_rows as $key => $_rida ) {
			if( $key != 'ok' ) {
				$this->vars(array(
					'KOKKU'	  => sprintf( "%1.2d EEK", $_sum_rows[$key] ),
				));
				$_summa = $this->parse( 'SUMMA' );
			} else {
				$_summa = '';
			}
			$this->vars(array(
				'PEALKIRI'	=> ( $key == 'ok' ? $lc_expp['LC_EXPP_OK'] : $lc_expp['LC_EXPP_TEL'] ),
				'RIDA'		=> $_rida,
				'SUMMA'		=> $_summa,
			));
			$_leping .= $this->parse( 'LEPING' );
		}
		$this->vars(array(
			'ACTION' => $_action,
			'LEPING'	=> $_leping,
			'ISIK' => $_isik,
		));
		return $this->parse();
	}
	
	function getIsik( $row ) {
		$content = '';
		if ( $row['tyyp'] == "firma" )	$content.=stripslashes( $row["firmanimi"])."<br>\n";
		$content.=stripslashes( $row["eesnimi"]." ".$row["perenimi"])."<br>\n";
		if ( !empty( $row["email"] ))	$content.="e-post: ".stripslashes( $row["email"])."<br>\n";
		if ( !empty( $row["telefon"] ))	$content.="tel: ".stripslashes( $row["telefon"])."<br>\n";
		if ( !empty( $row["faks"] ))	$content.="faks: ".stripslashes( $row["faks"])."<br>\n";
		$content.="<br>";
		if ( !empty( $row["tanav"] ))	$content.=stripslashes( $row["tanav"]);
		if ( !empty( $row["maja"] ))	$content.=" ".stripslashes( $row["maja"]);
		if ( !empty( $row["korter"] ))	$content.="-".stripslashes( $row["korter"]);
		$content.="<br>\n";
		if ( !empty( $row["linn"] ))	$content.=stripslashes( $row["linn"])."<br>\n";
		if ( !empty( $row["indeks"] ))	$content.=stripslashes( $row["indeks"])." ";
		if ( !empty( $row["maakond"] ))	$content.=stripslashes( $row["maakond"]);
		return $content;
	}

	function returnPost() {
		header( "Location: ".aw_ini_get("baseurl")."/tellimine/" );
		exit;
	}

	function getNextArve() {
		$this->save_handle();
//		return true;
//		$sql	= "LOCK TABLES expp_arvenr WRITE";
//		$this->db_query($sql);
		$sql	= "UPDATE expp_arvenr SET arvenr=arvenr+1 WHERE id=1";
		$this->db_query($sql);
		$sql	= "SELECT arvenr FROM expp_arvenr WHERE id=1";
		$arow = $this->db_fetch_row( $sql );
//		$sql	= "UNLOCK TABLES expp_arvenr";
//		$this->db_query($sql);
		$retVal = sprintf("%08s",$arow["arvenr"]);
		$this->restore_handle();
		return $retVal;
	}

	function leia_731( $S ) {
		$Kontroll = "";
		$L = strlen( $S );
		for ( $i = 0; $i < $L ; $i ++)
			if (( $S[ $i ] != " " ) and ( $S[ $i ] != "-" ))
				$Kontroll.= (integer)$S[$i];
		$S = $Kontroll;
		$m1 = 7;
		$K = 0;
		$L = strlen( $S );
		for ( $i = ($L - 1); $i > -1; $i--) {
			$K+= ( $S[ $i ] * $m1 );
			switch ( $m1 ) {
				case 7 : $m1 = 3; break;
				case 3 : $m1 = 1; break;
				case 1 : $m1 = 7; break;
			}
		}
		$K1 = ( $K - ( $K % 10 ) + 10 );
		$K2 = $K1 - $K;
		if ( $K2 > 9 ) $K2 -= 10;
		return $K2;
	}

	function parsePost() {
		$sql = "SELECT * FROM expp_tellija WHERE session='".session_id()."' AND staatus='tellija' ORDER BY time DESC LIMIT 1";
		$_tellija = $this->db_fetch_row( $sql );
		if( $this->num_rows() < 1 ) {
			header( "Location: ".aw_ini_get("baseurl")."/tellimine/tellija/" );
			exit;
		}
		if ( $_tellija['tyyp'] == "firma" ) {
			$_tellija['nimi'] = $_tellija["firmanimi"];
			$_tellija['enimi'] = $_tellija["eesnimi"]." ".$_tellija["perenimi"];
		} else {
			$_tellija['nimi'] = $_tellija["eesnimi"]." ".$_tellija["perenimi"];
			$_tellija['enimi'] = '';
		}
		$_tellija['aadress'] = $_tellija['linn'].','.$_tellija['maakond'];
		if( $_tellija['toimetus'] == 'sama' ) {
			$_saaja = $_tellija;
		} else {
			$sql = "SELECT * FROM expp_tellija WHERE session='".session_id()."' AND staatus='saaja' ORDER BY time DESC LIMIT 1";
			$_saaja = $this->db_fetch_row( $sql );
			if( $this->num_rows() < 1 ) {
				header( "Location: ".aw_ini_get("baseurl")."/tellimine/saaja/" );
				exit;
			}
			if ( $_saaja['tyyp'] == "firma" ) {
				$_saaja['nimi'] = $_saaja["firmanimi"];
				$_saaja['enimi'] = $_saaja["eesnimi"]." ".$_saaja["perenimi"];
			} else {
				$_saaja['nimi'] = $_saaja["eesnimi"]." ".$_saaja["perenimi"];
				$_saaja['enimi'] = '';
			}
			$_saaja['aadress'] = $_saaja['linn'].','.$_saaja['maakond'];
		}
		$sql1 = '';
		foreach( $this->valjad as $key => $val ) {
			$sql1 .= " s{$val}='".$_saaja[$key]."',";
			$sql1 .= " m{$val}='".$_tellija[$key]."',";
		}
		$sql1 .= " vvotja='TK',"
			." kanal='WEB',"
			." tyyp='U'";
		$sql = "SELECT k.*"
			.", t.toimetus"
			.", h.baashind"
			.", h.hkkood"
		." FROM expp_korv k ,expp_valjaanne t, expp_hind h"
		." WHERE k.session='".session_id()."' AND k.pindeks=t.pindeks AND k.pikkus=h.id"
		." AND k.leping = 'ok'"
		." ORDER BY k.leping DESC, t.valjaande_nimetus ASC";
		$this->db_query($sql);
		while ($row = $this->db_next()) {
			$_arve = $this->getNextArve();
			$_viitenr = "205".$row["toimtunnus"].$_arve;
			$_viitenr.= $this->leia_731( $_viitenr );
			if ( $row["algus"] == "ASAP" ) {
				$algus	= mktime( 0,0,0,date("m"),date("d"),date("Y"));
				$lopp	= mktime( 0,0,0,date("m")+1,date("d")-1,date("Y"));
				$lisarida	= addslashes( "Nii ruttu, kui v&otilde;imalik" );
			} else if ( $row["algus"] == "CONT" ) {
				$algus	= mktime( 0,0,0,date("m"),date("d"),date("Y"));
				$lopp	= mktime( 0,0,0,date("m")+1,date("d")-1,date("Y"));
				$lisarida	= addslashes( "Kehtiva tellimuse l&otilde;pust" );
			} else {
				$algus		= mktime(0,0,0,(int)substr( $row["algus"],4,2),1,(int)substr( $row["algus"],0,4));
				$lopp		= mktime(0,0,0,1+(int)substr( $row["algus"],4,2),0,(int)substr( $row["algus"],0,4));
				$lisarida	= "";
			}

			$sql = "INSERT INTO expp_arved SET $sql1, tellkpv='".date("d.m.Y")."',"
				." arvenr='{$_arve}',"
				." vaindeks='".$row["pindeks"]."',"
				." algus='".date("d.m.Y",$algus)."',"
				." lopp='".date("d.m.Y",$lopp)."',"
				." lisarida='{$lisarida}',"
				." eksempla='".$row["eksemplar"]."',"
				." rhkkood='".$row["hkkood"]."',"
				." maksumus='".($row["baashind"]*(int)$row["eksemplar"]*(int)$row["kogus"])."',"
				." leping='".$row["leping"]."',"
				." trykiarve='0',"
				." trykiokpakkumine='0',"
				." viitenumber='{$_viitenr}',"
				." session='".session_id()."',"
				." time=NOW()";
			$this->db_query($sql);
//			$my_ok += @mysql_affected_rows( $dbh );
		}
// ----------------------------------------
//							arvega asjad
		$sql = "SELECT k.*"
			.", t.toimetus"
			.", h.baashind"
			.", h.kestus"
			.", h.hinna_tyyp"
			.", h.hkkood"
		." FROM expp_korv k ,expp_valjaanne t, expp_hind h"
		." WHERE k.session='".session_id()."' AND k.pindeks=t.pindeks AND k.pikkus=h.id"
		." AND k.leping = 'tel'"
		." ORDER BY k.leping DESC, t.valjaande_nimetus ASC";

		$this->db_query($sql);
		if( $this->num_rows() > 0 ) {
			$_arve = $this->getNextArve();

			if ( $this->num_rows() > 1 ) {
				$_viitenr = "10599{$_arve}";
				$_viitenr.= leia_731( $_viitenr );
			}

			while ($row = $this->db_next()) {
				if ( $this->num_rows() == 1 ) {
					$_viitenr = "105".$row["toimtunnus"].$_arve;
					$_viitenr.= $this->leia_731( $_viitenr );
				}
				$kestus	= (int)$row["kestus"]*(int)$row["kogus"];
				switch( $row["hinna_tyyp"] ) {
					case 0:	//	kuu
						$lisarida	=( $kestus== 1 )?"kuu":"kuud";
						$my_m		= (int)$row["kestus"]*(int)$row["kogus"];
						$my_d		= 0;
						$my_y		= 0;
						break;
					case 1:	//	nädal
						$lisarida	=( $kestus== 1 )?"n&auml;dal":"n&auml;dalat";
						$my_m		= 0;
						$my_d		= (int)$row["kestus"]*7*(int)$row["kogus"];
						$my_y		= 0;
						break;
					case 3:	//	number
						$lisarida	=( $kestus== 1 )?"number":"numbrit";
						$my_m		= 0;
						$my_d		= (int)$row["kestus"]*7*(int)$row["kogus"];
						$my_y		= 0;
						break;
					case 2:	//	päev
						$lisarida	=( $kestus== 1 )?"p&auml;ev":"p&auml;eva";
						$my_m		= 0;
						$my_d		= (int)$row["kestus"]*(int)$row["kogus"];
						$my_y		= 0;
						break;
					case 4:	//	aasta
						$lisarida	=( $kestus== 1 )?"aasta":"aastat";
						$my_m		= 0;
						$my_d		= 0;
						$my_y		= (int)$row["kestus"]*(int)$row["kogus"];
						break;
		 			case 5:	//	poolkuud
						$lisarida	= ( $kestus== 1 )?"poolkuu":"poolkuud";
						$my_m		= (int)$row["kestus"]*(int)$row["kogus"];
						$my_d		= 0;
						$my_y		= 0;
						break;
				}
				if ( $row["algus"] == "ASAP" ) {
					$algus		= mktime( 0,0,0,date("m"),date("d"),date("Y"));
					$lopp		= mktime( 0,0,0,date("m")+$my_m,date("d")+$my_d-1,date("Y")+$my_y);
					$lisarida	= "Nii ruttu, kui v&otilde;imalik<br><b>$kestus</b> $lisarida";
				} else if ( $row["algus"] == "CONT" ) {
					$algus		= mktime( 0,0,0,date("m"),date("d"),date("Y"));
					$lopp		= mktime( 0,0,0,date("m")+$my_m,date("d")+$my_d-1,date("Y")+$my_y);
					$lisarida	= "Kehtiva tellimuse l&otilde;<br><b>$kestus</b> $lisarida";
				} else if ( $row["pindeks"] != "69830" ) {
					$algus		= mktime(0,0,0,(int)substr( $row["algus"],4,2),1,(int)substr( $row["algus"],0,4));
					$lopp		= mktime(0,0,0,(int)substr( $row["algus"],4,2)+$my_m,$my_d,(int)substr( $row["algus"],0,4)+$my_y);
					$lisarida	= "";
				} else {	// Eesti ekspress
					$algus		= mktime(0,0,0,(int)substr( $row["algus"],4,2),1,(int)substr( $row["algus"],0,4));
					$ajut		= date("w", $algus);
					$ajut		= ($ajut <= 4)?(4-$ajut):(11-$ajut);
					$algus		= mktime(0,0,0,(int)substr( $row["algus"],4,2),1+$ajut,(int)substr( $row["algus"],0,4));
					$lopp		= mktime(0,0,0,(int)substr( $row["algus"],4,2)+$my_m,$my_d+$ajut-6,(int)substr( $row["algus"],0,4)+$my_y);
					$lisarida	= "";
				}
				$sql = "INSERT INTO expp_arved SET {$sql1}, tellkpv='".date("d.m.Y")."',"
					." arvenr='{$_arve}',"
					." vaindeks='".$row["pindeks"]."',"
					." algus='".date("d.m.Y",$algus)."',"
					." lopp='".date("d.m.Y",$lopp)."',"
					." lisarida='".addslashes($lisarida)."',"
					." eksempla='".$row["eksemplar"]."',"
					." rhkkood='".$row["hkkood"]."',"
					." maksumus='".($row["baashind"]*(int)$row["eksemplar"]*(int)$row["kogus"])."',"
					." leping='".$row["leping"]."',"
					." trykiarve='0',"
					." trykiokpakkumine='0',"
					." viitenumber='{$_viitenr}',"
					." session='".session_id()."',"
					." time=NOW()";
				$this->db_query($sql);
//				$my_ok += @mysql_affected_rows( $dbh );
			}
		}
		
/*
if ( $my_ok == 0 ) {
	$pid = korv;
	header( "location: index.php3".make_argc( "id", "pid" ));
	exit;
}
*/

/*$query = "UPDATE tellija SET session='".date("Ymd - ").session_id()."' WHERE session='".session_id()."'";
@mysql_db_query( $db_base, $query, $dbh );
$query = "UPDATE korv SET session='".date("Ymd - ").session_id()."' WHERE session='".session_id()."'";
@mysql_db_query( $db_base, $query, $dbh );*/

		header( "Location: ".aw_ini_get("baseurl")."/tellimine/makse/" );
		exit;
	}
}
?>