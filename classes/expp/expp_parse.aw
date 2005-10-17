<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_parse.aw,v 1.1 2005/10/17 10:32:05 dragut Exp $
// expp_parse.aw - Expp URL parser 
/*

@classinfo syslog_type=ST_EXPP_PARSE relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

*/

class expp_parse extends class_base {

	var $pids = array();
	var $pidpos = 1;
	var $cy;

	function expp_parse() {

		global $lc_expp;

		$this->init(array(
			"tpldir" => "expp",
			"clid" => CL_EXPP_PARSE
		));

		lc_site_load( "expp", $this );

		$URL = $GLOBALS['REQUEST_URI'];
		$inURL = aw_ini_get("tell_dir");
		$inLen = strlen( $inURL );

		if( $inLen > strlen($URL)) $inLen--;
		if( strncmp( $URL, aw_ini_get("tell_dir"), $inLen ) === 0 ) {
			if( strpos( $URL, "?" )) {
				parse_str( substr( $URL, strpos( $URL, "?" )+1), $GLOBALS['HTTP_GET_VARS'] );
				$URL = substr( $URL, 0, strpos( $URL, "?" ));
			}
			$this->pids = explode( "/", $URL );

		} else {
			$this->pids = array(
				"",
				"tellimine",
				"",
			);
		}
		$this->cy = get_instance( CL_EXPP_JAH );

		$this->addYah( array(
				'link' => $this->pids[1],
				'text' => $lc_expp['LC_EXPP_DB_AVALEHT'],
			));
	}

	function callback_mod_reforb($arr) {
		$arr["post_ru"] = post_ru();
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	/**  
		
		@attrib name=show is_public="1" caption="Expp" nologin="1" default="1" all_args="1"
		
		@param id type=int
		
		@returns
		
		@comment

	**/
	function show($arr) {
		$retHTML = '';
		if( empty( $this->pids ) ) {
			return $retHTML;
		}
// arr( $this->pids );
		$pid = $this->getPid( 1 );
		switch( $pid ) {
			case 'makse' :
				$cl = get_instance( CL_EXPP_MAKSE );
				$retHTML = $cl->show();
				break;
			case 'korv' :
				$cl = get_instance( CL_EXPP_KORV );
				$retHTML = $cl->show();
				break;
			case 'telli' :
				$cl = get_instance( CL_EXPP_TELLI );
				$retHTML = $cl->show();
				break;
			case 'arve' :
				$cl = get_instance( CL_EXPP_ARVE );
				$retHTML = $cl->show();
				break;
			case 'tellija' :
			case 'saaja' :
				$cl = get_instance( CL_EXPP_ISIK );
				$retHTML = $cl->show();
				break;
		}
		if( !empty( $retHTML )) return $retHTML;

		$this->pidpos = 1;
		$cl = get_instance( CL_EXPP_VA );
		return $cl->show();
	}

	function getPid( $pos ) {
		$PID = urldecode( $this->pids[ $pos+ 1 ]);
		if(ereg("\'|\"", $PID)) {
			$PID = false;
		}
		$this->pidpos = $pos + 1;
		return $PID;
	}

	function nextPid() {
		return $this->getPid( $this->pidpos );
	}

	function addYah( $arr ) {
		return $this->cy->addLink( $arr );
	}

	function getVal( $name ) {
		return ($GLOBALS['HTTP_POST_VARS'][$name]?$GLOBALS['HTTP_POST_VARS'][$name]:($GLOBALS['HTTP_GET_VARS'][$name]?$GLOBALS['HTTP_GET_VARS'][$name]:''));
	}
}
?>