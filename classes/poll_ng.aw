<?php
// poll_ng.aw - New generation poll
// $Header: /home/cvs/automatweb_dev/classes/Attic/poll_ng.aw,v 1.1 2002/12/30 19:19:12 duke Exp $

/*

@default group=general
@default table=objects
@default field=meta
@default method=serialize

@property choices type=generated callback=callback_get_choices
@caption Variandid

@classinfo relationmgr=yes


*/
class poll_ng extends class_base
{
	function poll_ng()
	{
		$this->init(array(
			  "clid" => CL_POLL_NG,
		  ));
	}

	function callback_get_rel_types()
	{
		return array("1" => "polli vastus");
	}

	function callback_get_choices($args = array())
	{
		$choice_objects = new aw_array($args["obj"]["meta"]["alias_reltype"]);
		$this->t = new aw_table(array(
                        "prefix" => "poll_choices",
                ));
                $this->t->parse_xml_def($this->cfg["basedir"]."/xml/generic_table.xml");
                $this->t->define_field(array(
                        "name" => "id",
                        "caption" => "ID",
                        "talign" => "center",
                        "align" => "center",
                        "nowrap" => "1",
                        "width" => "30",
                ));
                $this->t->define_field(array(
                        "name" => "name",
                        "caption" => "Nimi",
                        "talign" => "center",
                        "nowrap" => "1",
                ));
                $this->t->define_field(array(
                        "name" => "clicks",
                        "caption" => "Klikke",
                        "talign" => "center",
                        "align" => "center",
                        "nowrap" => "1",
			"numeric" => 1,
                ));
                $this->t->define_field(array(
                        "name" => "percent",
                        "caption" => "Protsent",
                        "talign" => "center",
                        "align" => "center",
                        "nowrap" => "1",
                ));
		
		$choice_objects = new aw_array($args["obj"]["meta"]["alias_reltype"]);
		foreach($choice_objects->get() as $key => $val)
		{
			if ($val == 1)
			{
				$obj = $this->get_object($key);
				$this->t->define_data(array(
					"id" => $obj["oid"],
					"name" => $obj["name"],
					"clicks" => " 0",
					"percent" => "0%",
				));
			};
		};
	
		$node = array(
			"type" => "text",
			"caption" => "Variandid",
			"value" => $this->t->draw(),
		);
		$retval[] = $node;
		return $retval;
	}
};
?>
