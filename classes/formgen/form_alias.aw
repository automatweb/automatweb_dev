<?php
// $Header: /home/cvs/automatweb_dev/classes/formgen/form_alias.aw,v 1.1 2002/10/28 13:00:47 kristo Exp $

classload("formgen/form");
class form_alias extends form_base
{
	function form_alias()
	{
		$this->form_base();
	}

	function new_entry_alias($arr)
	{
		extract($arr);
		$this->read_template("add_form_alias.tpl");
		$this->mk_path(0, "<a href='".$return_url."'>Tagasi</a> / Lisa sisestuse alias");

		if ($form_submit)
		{
			global $HTTP_GET_VARS;
			$f = get_instance("formgen/form");
			$f->process_entry(array(
				"id" => $sf,
				"entry_id" => $entry_id,
				"values" => $HTTP_GET_VARS
			));

			$entry_id = $f->entry_id;
		}

		if ($sf)
		{
			$f = get_instance("formgen/form");
			$form = $f->gen_preview(array(
				"id" => $sf,
				"reforb" => $this->mk_reforb("new_entry_alias",array("no_reforb" => true,"parent" => $parent, "return_url" => $return_url,"sf" => $sf,"entry_id" => $entry_id,"form_submit" => true,"alias_to" => $alias_to,"id" => $id),"form_alias"),
				"entry_id" => $entry_id,
				"form_action" => "orb.".$this->cfg["ext"],
				"method" => "GET"
			));

			if ($entry_id)
			{
				$entry = $f->show(array(
					"id" => $sf,
					"entry_id" => $entry_id,
					"op_id" => 1
				));
			}
		}

		$this->vars(array(
			"reforb" => $this->mk_reforb("new_entry_alias", array("no_reforb" => true, "parent" => $parent, "return_url" => $return_url,"alias_to" => $alias_to,"id" => $id)),
			"sfs" => $this->picker($sf,$this->get_flist(array("type" => FTYPE_SEARCH))),
			"form" => $form,
			"entry" => $entry,
			"a_reforb" => $this->mk_reforb("submit_entry_alias", array("parent" => $parent, "return_url" => $return_url,"sf" => $sf, "entry_id" => $entry_id,"alias_to" => $alias_to,"id" => $id))
		));
		if ($entry != "")
		{
			$this->vars(array("results" => $this->parse("results")));
		}
		if ($form != "")
		{
			$this->vars(array("show_form" => $this->parse("show_form")));
		}
		return $this->parse();
	}

	function submit_entry_alias($arr)
	{
		extract($arr);

		if ($alias_to)
		{
			$this->add_alias($alias_to,$entry_id,serialize(array("type" => "show", "output" => 1, "form_id" => $sf)));	
		}
		else
		if ($id)
		{
			// change alias
			$this->change_alias(array(
				"id" => $id,
				"target" => $entry_id,
				"extra" => serialize(array("type" => "show", "output" => 1, "form_id" => $sf))
			));
		}

		return $return_url;
	}

	function change_entry_alias($arr)
	{
		extract($arr);

		$this->db_query("SELECT * FROM aliases WHERE target = '$id'");
		$ret = $this->db_next();
		$dat = aw_unserialize($ret["data"]);

		return $this->new_entry_alias(array(
			"sf" => $dat["form_id"],
			"return_url" => $return_url,
			"entry_id" => $id,
			"id" => $ret["id"]
		));
//		$this->mk_path(0, "<a href='".$return_url."'>Tagasi</a> / Muuda sisestuse aliast");
//		return $this->parse();
	}

	///
	// !Kasutatakse ntx dokumendi sees olevate aliaste asendamiseks. Kutsutakse välja callbackina
	function parse_alias($args = array())
	{
		extract($args);
		if (!is_array($this->entryaliases))
		{
			$this->entryaliases = $this->get_aliases(array(
								"oid" => $oid,
								"type" => CL_FORM_ENTRY,
							));
		};
		$alias_data = unserialize($alias["data"]);

		$fo = get_instance("formgen/form");

		if ($alias_data["type"] == "show")
		{
			$replacement = $fo->show(array(
				"id" => $alias_data["form_id"],
				"entry_id" => $alias["target"],
				"op_id" => $alias_data["output"]
			));
		}
		else
		{
			$replacement = $fo->gen_preview(array(
				"id" => $alias_data["form_id"],
				"entry_id" => $alias["target"],
			));
		}

		return $replacement;
	}

}
?>
