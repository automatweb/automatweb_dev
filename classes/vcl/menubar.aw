<?php
//this is almost like tabpanel class
//
class menubar extends aw_template
{
	////
	// !Initializes a menubar object
	function menubar($args = array())
	{
		$this->init("tabpanel");
		$tpl = isset($args["tpl"]) ? $args["tpl"] . ".tpl" : "menus.tpl";
		$this->read_template($tpl);
		$this->tabs = array();
		$this->tabcount = array();
		$this->hide_one_tab = 0;	//kas seda kasutatakse kusagil
		$this->showselected = array();  //additional information to YAH bar
	}

	////
	// !Adds a new tab to the panel
	// active(bool) - whether to use the "selected" subtemplate for this tab
	// caption(string) - text to display as caption
	// link(string)
	function add_tab($args = array())
	{

		if (isset($args["active"]) && $args["active"])
		{
			//array_unshift($this->showselected,' ['.$args["caption"].'] ');
			$this->showselected[] = ' ['.$args["caption"].'] ';
			$subtpl = "selected_";
		}
		else
		{
			$subtpl = "";
		};

		if (isset($args["disabled"]) && $args["disabled"])
		{
			$subtpl = "disabled_";
		};
		// no link? so let's show the tab as disabled
		if (isset($args["link"]) && strlen($args["link"]) == 0)
		{
			$subtpl = "disabled_";
		};

		if ($args['parent'])
		{
			$subtpl .= 'menuitem';
		}
		else
		{
			$subtpl .= 'menubutton';
		}

		$arr = array(
			"caption" => $args["caption"],
			"link" => $args["link"],
			'id' => $args['id'],
			'parent' => $args['parent'],
			'subtpl' => $subtpl,
		);

		if ($args['parent'])
		{
			$this->vars($arr);
			$this->submenus[$args['parent']] .= $this->parse($subtpl);
		}
		else
		{
			$this->mainmenu[$args['id']] = $arr;
		}

	}

	////
	// !Generates and returns the tabpanel
	// content(string) - contents of active panel
	function get_tabpanel($args = array())
	{
		$submenus = '';
		$mainmenu = '';
		if (is_array($this->submenus))
		{
			foreach($this->submenus as $key => $val)
			{
				$this->vars(array(
					'parent' => $key,
					'menuitem' => $val,
				));
				$submenus .= $this->parse('menu');
			}
		}

		if (is_array($this->mainmenu))
		{
			foreach($this->mainmenu as $key => $val)
			{
				$this->vars($val);
				if (isset($this->submenus[$key]))
				{
					$mainmenu .= $this->parse($val['subtpl']);
				}
				else
				{
					$mainmenu .= $this->parse($val['subtpl'].'_nosub');
				}
			}
		}

		$GLOBALS['site_title'] .= ' | '.implode(' => ',$this->showselected);

		$this->vars(array(
			'menubutton' => $mainmenu,
			'menu' => $submenus,
		));

		$toolbar = isset($args["toolbar"]) ? $args["toolbar"] : "";
		$toolbar2 = isset($args["toolbar2"]) ? $args["toolbar2"] : "";

		$this->vars(array(
			"toolbar" => $toolbar,
			"content" => $args["content"],
		));
		return $this->parse();
	}
};
?>
