<?php
// poll_ng.aw - New generation poll
// $Header: /home/cvs/automatweb_dev/classes/Attic/poll_ng.aw,v 1.12 2005/03/21 12:50:39 kristo Exp $

/*

@default group=general
@default table=objects
@default field=meta
@default method=serialize

@property choices type=generated callback=callback_get_choices group=choices
@caption Variandid

@groupinfo choices caption=Vastusevariandid

@classinfo relationmgr=yes syslog_type=ST_POLL_NG

@reltype ANSWER value=1 clid=CL_DOCUMENT
@caption polli vastus

*/
class poll_ng extends class_base
{
	function poll_ng()
	{
		$this->init(array(
			"clid" => CL_POLL_NG,
		));
	}

	function callback_get_choices($args = array())
	{
		$this->t = new aw_table(array(
			"prefix" => "poll_choices",
			"layout" => "generic"
		));
		$this->t->define_field(array(
			"name" => "id",
			"caption" => t("ID"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"width" => "30",
		));
		$this->t->define_field(array(
			"name" => "name",
			"caption" => t("Nimi"),
			"talign" => "center",
			"nowrap" => "1",
		));
		$this->t->define_field(array(
			"name" => "clicks",
			"caption" => t("Klikke"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
			"numeric" => 1,
		));
		$this->t->define_field(array(
			"name" => "percent",
			"caption" => t("Protsent"),
			"talign" => "center",
			"align" => "center",
			"nowrap" => "1",
		));
	
		$o = $args["obj_inst"];
		foreach($o->connections_from(array("type" => "RELTYPE_ANSWER")) as $c)
		{
			$this->t->define_data(array(
				"id" => $c->prop("to"),
				"name" => $c->prop("to.name"),
				"clicks" => " 0",
				"percent" => "0%",
			));
		};
	
		$node = array(
			"type" => "text",
			"caption" => t("Variandid"),
			"value" => $this->t->draw(),
		);
		$retval[] = $node;
		return $retval;
	}

	function parse_alias($args = array())
	{
		$target = $args["alias"]["target"];
		if (!$target)
		{
			return false;
		};

		$poll = obj($target);

		$retval = "<div style='border: 1px #cccccc solid'>";
		$retval .= "<div style='background: #cccccc;'>".$poll->name()."</div>";
		$url = $this->mk_my_orb("vote",array(
			"oid" => $args["oid"],
			"poll" => $poll->id(),
			"section" => aw_global_get("section")
		),"poll_ng");

		$votes = $poll->meta("votes");

		foreach($poll->connections_from(array("type" => "RELTYPE_ANSWER")) as $c)
		{
			$retval .= sprintf("%d ",$votes[$c->prop("to")]);
			$retval .= html::href(array(
				"url" => $url . "&choice=" . $c->prop("to"),
				"caption" => $c->prop("to.name"),
			)) . "<br />";
		};
		$retval .= "</div>";
		return $retval;
	}

	/**  
		
		@attrib name=vote params=name nologin="1" default="0"
		
		@param oid required type=int
		@param poll required type=int
		@param choice required type=int
		@param section optional
		
		@returns
		
		
		@comment

	**/
	function vote($args = array())
	{
		// XXX: block voting robots, IP check, etc ...
		extract($args);
		// first we have to check whether there really is a relation between the object containing
		// the alias and the actuall poll object  oid==poll
		$tmp_o = obj($oid);
		if (count($tmp_o->connections_from(array("to" => $poll))) < 1)
		{
			die (t("there is no relation between those objects"));
		};
		// no check, whether this poll actually has a choice with this id
		// poll <==> choice

		$pobj = obj($poll);
		$conn = $pobj->connections_from(array(
			"type" => "RELTYPE_ANSWER",
			"to" => $choice
		));
		if (count($conn) < 1)
		{
			die (t("invalid choice"));
		};

		// everything seems OK, record the vote
		$votes = $pobj->meta("votes");

		if ($votes[$choice])
		{
			$votes[$choice]++;
		}
		else
		{
			$votes[$choice] = 1;
		};

		// acl will fuck us here :(
		$tmp = obj($poll);
		$tmp->set_meta("votes",$votes);
		$tmp->save();
		return $this->cfg["baseurl"] . "/$section";
	}
};
?>
