<?php
/*
	@default table=objects
	@default group=general

	@default field=meta
	@default method=serialize

	@property test type=popup_objmgr multiple=1 height=550 width=650
	@caption dfghdf1

	@property test2 type=popup_objmgr clid=CL_MAAKOND change=1
	@caption dfghdf2

	@property searching callback=search
*/

//define ('',6);

class popup_objmgr extends class_base
{
	function search($args = array())
	{
		extract($args);
		//print_r($args);
		$this->multiples = $multiple;
		$this->force_clid = $force_clid;

//		$this->check_name = $check_name;

		$this->read_template("search.tpl");
		$search = get_instance("search");
		$reltypes[0] = "alias";
		$reltypes = new aw_array($reltypes);
		$this->reltypes = $reltypes->get();
		$args["clid"] = "popup_objmgr";
		$form = $search->show($args);
		$this->search = &$search;

		$id = ($args["id"]) ? $args["id"] : $args["docid"];
		$this->id = $id;
		$obj = $this->get_object($id);


				$return_url='javascript:document.location="'.
					$this->mk_my_orb('instant_get',array('id' => $args['obj']['oid']),'popup_objmgr').
				'instant_get=" + encodeURI(document.location.href);';

		$this->vars(array(
			"reforb" => $this->mk_reforb("search",array(
				'multiple' => $multiple,
				"no_reforb" => 1,
				"search" => 1,
				"id" => $id,
				"reltype" => $reltype,
				'force_clid' => $force_clid,
				'return_url' => $return_url,
//				'clid' => $clid,
			)),
			"toolbar" => $this->mk_toolbar(),
			"form" => $form,
			"table" => $search->get_results(),
			'parent' => $parent,
//			'return_url' =>$return_url,
		));
		$results = $search->get_results();

		return $this->parse();
	}

	function instant_get($args)
	{
		extract($args);
arr($args,1);
//		echo $instant_get;

		$this->read_template('instant_get.tpl');


		return $this->parse();
	}

	function mk_toolbar()
	{
		$toolbar = get_instance("toolbar",array("imgbase" => "/automatweb/images/icons"));
		$choices = array();
		$classes = $this->cfg["classes"];
		//print_r($classes);
		foreach($classes as $clid => $cldat)
		{
			if (isset($cldat["alias"]))
			{
				$fil = $cldat["alias_class"] != "" ? $cldat["alias_class"] : $cldat["file"];
				preg_match("/(\w*)$/",$fil,$m);
				$lib = $m[1];
				// indent the names
				$choices[$lib] = $cldat["name"];
			}
		}
		asort($choices);

		$aliases = html::select(array("options" => $choices,"name" => "aselect"));

		$toolbar->add_cdata($aliases);

		$toolbar->add_button(array(
			"name" => "new",
			"tooltip" => "Lisa uus objekt",
			"url" => "javascript:new_object()",
			"imgover" => "new_over.gif",
			"img" => "new.gif",
		));

		$toolbar->add_button(array(
			"name" => "search",
			"tooltip" => "Otsi",
			"url" => "javascript:document.searchform.submit();",
			"imgover" => "search_over.gif",
			"img" => "search.gif",
		));

		$toolbar->add_button(array(
			"name" => "send_val",
			"tooltip" => "Vali",
			"url" => "javascript:SendValues();",
			"imgover" => "import_over.gif",
			"img" => "import.gif",
		));

		return $toolbar->get_toolbar();
	}

	function search_callback_popup_get(&$row,$args)
	{
		$name=$this->check_name?$this->check_name:'sel';
//		print_r($args);die();
		if (!$args['multiple'])
		{
			$row['change'] = html::radiobutton(array('name' => $name, 'value' => $row['oid']));
			$row['change'].= html::hidden(array('name' => 'selval['.$row['oid'].']', 'value' => htmlentities($row['name'])));
		}
		else
		{
			$row['change'] = html::checkbox(array('name' => $name, 'value' => $row['oid']));
			$row['change'].= html::hidden(array('name' => 'selval['.$row['oid'].']', 'value' => htmlentities($row['name'])));
		}

	}

	function search_callback_get_fields(&$fields,$args)
	{
		$fields = array();
		$fields['special'] = 'n/a';
//		$fields['class_id'] = 'n/a';
		$fields['server'] = 'n/a';
		$fields['site_id'] = 'n/a';
		$fields['period'] = 'n/a';
		$fields['lang_id'] = 'n/a';

		if (!$args['force_clid'])
		{
			$classes = $this->cfg["classes"];
		//print_r($classes);
			foreach($classes as $clid => $cldat)
			{
				if (isset($cldat["alias"]))
				{
//				$fil = $cldat["alias_class"] != "" ? $cldat["alias_class"] : $cldat["file"];
//				preg_match("/(\w*)$/",$fil,$m);
//				$lib = $m[1];
				// indent the names
//				$choices[$lib] = $cldat["name"];
					$choices[$clid] = $cldat["name"];
				}
			}
			asort($choices);
			$fields["class_id"] = array(
				"type" => "select",
				"caption" => "Klass",
				"options" => $choices,
//			"selected" => $args['force_clid'],
			);
		}
		else
		{

			$fields["class_id"] = array(
				"type" => "select",
				'multiple' => 1,
				"caption" => "Klass",
				"options" => array($args['force_clid'] => $this->cfg["classes"][$args['force_clid']]['name']),
				"selected" => $args['force_clid'],
			);
		}
	}


	function get_property($args)
	{
		$data = &$args['prop'];
		$retval = PROP_OK;
		$meta=$args['obj']['meta'];
		$id=$args['obj']['oid'];
		$parent=$args['obj']['parent'];
		switch($data['name'])
		{
			case 'jrk':
				$retval=PROP_IGNORE;
			break;

			case 'alias':
				$retval=PROP_IGNORE;
			break;
		};
		return $retval;
	}

	function popup_objmgr()
	{
		$this->init(array(
			'clid' => CL_POPUP_OBJMGR,
			"tpldir" => "popup_objmgr",
		));

	}

}
?>
