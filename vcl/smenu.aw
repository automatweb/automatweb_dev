<?php
// see väga simpel "menüü" kuvamise klass
class smenu {
	var $tpl_act; // aktiivse elemendi template 
	var $tpl_deact; // deaktiivse elemendi template
	var $menu; // siia paigutame valmismenüü
	////
	// !Konstruktor
	function smenu($args = array())
	{
		$this->tpl_act = $args["tpl_act"];
		$this->tpl_deact = $args["tpl_deact"];
		$this->menu = "";
	}

	////
	// !Lisab uue elemendi menüüsse
	// active - aktiivne?
	function add_menu($args = array())
	{
		$tpl = ($args["active"]) ? $this->tpl_act : $this->tpl_deact;
		$this->menu .=  preg_replace("/{VAR:(.+?)}/e","\$args[\"\\1\"]",$tpl); 
	}

	////
	// !Väljastab joonistet menüü
	function get_menu()
	{
		return $this->menu;
	}
};
?>

