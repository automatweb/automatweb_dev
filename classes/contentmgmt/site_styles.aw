<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_styles.aw,v 1.2 2005/09/30 10:33:22 kristo Exp $
// site_styles.aw - Saidi stiilid 
//
// Usage: Create object, define alias, stylesheet files, default and random option.
// add {VAR:styles} to template
// Change styles with url argument /?set_style_(alias)=val where (alias) means object's alias 
// and val is one of: prev, next, last, random or numeric value representing style order num.
/*
HANDLE_MESSAGE
// not right now

(MSG_ON_SITE_SHOW_IMPORT_VARS, on_site_show)

@classinfo syslog_type=ST_SITE_STYLES no_comment=1

@default table=objects
@default group=general

@property alias type=textbox
@caption Alias

@property styles type=text store=no
@caption Stiilifailide URLid

@property default_style type=textbox size=3 field=meta method=serialize
@caption Vaikimisi stiili jrk.

@property random type=chooser field=meta method=serialize 
@caption Juhuslik valik

*/

define(SITE_STYLES_NO_RANDOM, 1);
define(SITE_STYLES_RAND_SESSION, 2);
define(SITE_STYLES_RAND_REFRESH, 3);

class site_styles extends class_base
{
	var $selected;

	function site_styles()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_SITE_STYLES
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
			case 'styles':
				$prop['value'] = $this->admin_stylepicker_get($arr);
			break;
			case 'random':
				$prop['options'] = array(
					SITE_STYLES_NO_RANDOM => t("Ei valita juhuslikult"),
					SITE_STYLES_RAND_SESSION => t("Juhuslik sessiooniti"),
					SITE_STYLES_RAND_REFRESH => t("Juhuslik igal laadimisel"),
				);
			break;
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
			case 'styles':
				$store = array ();
				// Reformat input into an array
				if (isset($arr['request']['styles']) && is_array($arr['request']['styles']))
				{	
					foreach ($arr['request']['styles'] as $in)
					{
						if (!strlen($in['url']))
						{
							continue; // delete empty styles
						}
						if (!is_numeric($in['ord']))
						{
							$noord[] = $in['url'];
						}
						else
						{
							$store[(int)$in['ord']] = $in['url'];
						}
					}
				}
				$store = array_flip($store);
				asort($store, SORT_NUMERIC);

				// Replace order numbers to sequence
				$final = array(null);
				foreach ($store as $url => $ord)
				{
					$final[] = $url;
				}
				foreach ($noord as $ord => $url)
				{
					$final[] = $url;
				}
				$arr['obj_inst']->set_meta('styles', $final);
			break;
		}
		return $retval;
	}	

	function admin_stylepicker_get($arr)
	{
		$html = "";
		$i = 0;
		$ord = 0;
		$value = $arr['obj_inst']->meta('styles');
		if (is_array($value))
		{
			foreach ($value as $ord => $url)
			{
				if (!strlen($url))
				{
					continue;
				}
				$html .= $this->_make_stylepicker_row($ord, $url, ++$i);
				$html .= '<br />';
			}
		}
		$html .= '<br />'.t("Lisa").":&nbsp;&nbsp;&nbsp;".$this->_make_stylepicker_row($ord+1, "", ++$i);
		return $html;
	}

	function _make_stylepicker_row($ord, $url, $idx)
	{
		$ret = t("jrk").": ".html::textbox(array(
			'name' => 'styles['.$idx.'][ord]',
			'value' => $ord,
			'size' => 3,
		));
		$ret .= " ".t("url").": ".html::textbox(array(
			'name' => 'styles['.$idx.'][url]',
			'value' => $url,
		));
		return $ret;
	}


//-- methods --//
	/**
		Select random style
	**/
	function select_random($arr)
	{ 
		$this->select($arr, rand(1, $this->last_style_ord($arr)));
	}

	/**
		Select next style
	**/
	function select_next($arr)
	{
		$this->select($arr, $this->selected_style_ord($arr) >= $this->last_style_ord($arr) ? $this->last_style_ord($arr) : $this->selected_style_ord($arr)+1);
	}

	/**
		Select previous style
	**/
	function select_prev($arr)
	{
		$this->select($arr, $this->selected_style_ord($arr)>1 ? $this->selected_style_ord($arr)-1 : 1);
	}

	/**
		Select last style
	**/
	function select_last($arr)
	{
		$this->select($arr, $this->last_style_ord($arr));
	}

	/**
		Select style nr $ord
	**/
	function select($arr, $ord)
	{
		$o = obj($arr['oid']);
		$alias = $o->prop('alias');
		$styles = $o->meta('styles');
		if (isset($styles[$ord]))
		{
			$_SESSION['style_'.$alias] = $ord;
			$this->selected = $ord;
		}
	}

	/**
		Get order number of selected style
	**/
	function selected_style_ord($arr)
	{
		if (is_null($this->selected))
		{
			$o = obj($arr['oid']);
			$alias = $o->prop('alias');
			if(isset($_SESSION['style_'.$alias]))
			{
				$this->selected = $_SESSION['style_'.$alias];
			}
			else
			{
				$def = $o->prop('default_style');
				$this->selected = is_numeric($def) ? $def : 1;
			}
		}
		return $this->selected;
	}

	/**
		Get order number of last style
	**/
	function last_style_ord($arr)
	{
		$o = obj($arr['oid']);
		$styles = $o->meta('styles');
		return max(array_keys($styles));
	}

	/**
		Get url for currently selected style 
	**/
	function selected_style_url($arr)
	{
		$ord = $this->selected_style_ord($arr);
		$o = obj($arr['oid']);
		$styles = $o->meta('styles');
		return $styles[$ord];
	}


	/**
		Called by message ON_SITE_SHOW_IMPORT_VARS, adds value for template variable {VAR:styles}
	**/
	function on_site_show($arr)
	{
		$styles = "";
		$ol = new object_list(array(
			'class_id' => CL_SITE_STYLES,
			'status' => STAT_ACTIVE,
		));

		for($o = $ol->begin(); !$ol->end(); $o = $ol->next())
		{
			$link = '<link href="%s" rel="stylesheet" type="text/css"';
			$alias = $o->prop('alias');
			$aoid = array('oid' => $o->id());
			if (!empty($alias))
			{
				$link .= ' id="'.$alias.'"';
			}	
			$inst = $o->instance();
			if (isset($_REQUEST['set_style_'.$alias]))
			{
				switch ($_REQUEST['set_style_'.$alias])
				{
					case 'next':
						$inst->select_next($aoid);
					break;
					case 'prev':
						$inst->select_prev($aoid);
					break;
					case 'last':
						$inst->select_last($aoid);
					break;
					case 'random':
						$inst->select_random($aoid);
					break;
					default:
						$inst->select($aoid, $_REQUEST['set_style_'.$alias]);
					break;
				}
			}
			else
			{
				$r = $o->prop('random');
				if ($r == SITE_STYLES_RAND_REFRESH || ($r == SITE_STYLES_RAND_SESSION && !isset($_SESSION['style_'.$alias]) ))
				{
					$inst->select_random($aoid);
				}
			}
			$styles .= sprintf($link.'>', $inst->selected_style_url($aoid));
		}
		$arr['inst']->vars(array(
			'styles' => $styles, 
		)); 
	}
}
?>
