<?php
// poll_ng.aw - New generation poll
// $Header: /home/cvs/automatweb_dev/classes/Attic/poll_ng.aw,v 1.2 2002/12/30 20:16:53 duke Exp $

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

	function parse_alias($args = array())
	{
		$target = $args["alias"]["target"];
		$poll = $this->get_object($target);
		if (!$poll)
		{
			return false;
		};
		$choices = new aw_array($poll["meta"]["alias_reltype"]);
		// so, load the fscking objects
		$retval = "<div style='border: 1px #cccccc solid'>";
		$retval .= "<div style='background: #cccccc;'>$poll[name]</div>";
		$url = $this->mk_my_orb("vote",array("oid" => $args["oid"],"poll" => $poll["oid"],"section" => aw_global_get("section")),"poll_ng");
		$votes = $poll["meta"]["votes"];
		foreach($choices->get() as $key => $val)
		{
			if ($val == 1)
			{
				$choice = $this->get_object($key);
				$retval .= sprintf("%d ",$votes[$key]);
				$retval .= html::href(array(
					"url" => $url . "&choice=" . $key,
					"caption" => $choice["name"],
				)) . "<br>";
			};
		};
		$retval .= "</div>";
		return $retval;
	}

	function vote($args = array())
	{
		// XXX: block voting robots, IP check, etc ...
		extract($args);
		// first we have to check whether there really is a relation between the object containing
		// the alias and the actuall poll object  oid==poll
		$relation = $this->db_fetch_field("SELECT id from aliases WHERE source = $oid AND target = $poll","id");
		if (!$relation)
		{
			die ("there is no relation between those objects");
		};
		// no check, whether this poll actually has a choice with this id
		// poll <==> choice
		$pobj = $this->get_object($poll);
		$choices = $pobj["meta"]["alias_reltype"];
		if (!$choices[$choice] == 1)
		{
			die ("invalid choice");
		};
		// everything seems OK, record the vote
		$votes = $pobj["meta"]["votes"];
		if ($votes[$choice])
		{
			$votes[$choice]++;
		}
		else
		{
			$votes[$choice] = 1;
		};
		$this->upd_object(array(
			"oid" => $poll,
			"metadata" => array("votes" => $votes),
		));
		return $this->cfg["baseurl"] . "/$section";
	}
};
?>
