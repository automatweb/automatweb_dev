<?php
// $Id: doc.aw,v 2.1 2002/11/26 12:38:02 duke Exp $
// doc.aw - document class which uses cfgform based editing forms
// this will be integrated back into the documents class later on
/*

@default table=documents

@property title type=textbox size=60
@caption Pealkiri

@property subtitle type=textbox size=60
@caption Alapealkiri

@property author type=textbox size=60
@caption Autor

@property photos type=textbox size=60
@caption Fotode autor

@property keywords type=textbox size=60
@caption Võtmesõnad

@property names type=textbox size=60
@caption Nimed

@property lead type=textarea richtext=1 cols=60 rows=10
@caption Lead

@property content type=textarea richtext=1 cols=60 rows=30
@caption Sisu

@property is_forum type=checkbox 
@caption Foorum

@property showlead type=checkbox
@caption Näita leadi

@property show_modified type=checkbox
@caption Näita muutmise kuupäeva

//---------------
@property no_right_pane type=checkbox
@caption Ilma parema paanita

@property no_left_pane type=checkbox
@caption Ilma vasaku paanita

@property title_clickable type=checkbox
@caption Pealkiri klikitav

@property clear_styles type=checkbox
@caption Tühista stiilid

@property link_keywords type=checkbox
@caption Lingi võtmesõnad

@property esilehel type=checkbox
@caption Esilehel

@property frontpage_left type=checkbox
@caption Esilehel tulbas

@property dcache
@caption Cache otsingu jaoks

@property show_title type=checkbox
@caption Näita pealkirja

@property no_search type=checkbox
@caption Jäta otsingust välja

@property cite type=textarea cols=60 rows=10
@caption Tsitaat

@property tm type=textbox size=20
@caption Kuupäev

@property referer type=generated
@caption Ref

@property sections type=select multiple=1 size=20 group=vennastamine
@caption Sektsioonid

@classinfo toolbar=yes
@classinfo objtable=documents
@classinfo objtable_index=docid
@classinfo relationmgr=yes
@classinfo corefields=status

*/

class doc extends aw_template
{
	function doc($args = array())
	{
		$this->init(array(
			"clid" => CL_DOCUMENT,
		));
	}

	function get_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "sections":
				$d = get_instance("document");
				list($selected,$options) = $this->get_brothers(array(
					"id" => $args["obj"]["oid"],
				));
				$data["options"] = array("" => "") + $options;
				$data["selected"] = $selected;
				break;
		};
		return $retval;
	}

	function set_property($args = array())
	{
		$data = &$args["prop"];
		$retval = PROP_OK;
		switch($data["name"])
		{
			case "sections":
				$this->update_brothers(array(
					"id" => $args["obj"]["oid"],
					"sections" => $args["form_data"]["sections"],
				));
				break;

		};
	}

	function callback_pre_save($args = array())
	{
		// map title to name
		$coredata = &$args["coredata"];
		$objdata = &$args["objdata"];
		if ($objdata["title"])
		{
			$coredata["name"] = $objdata["title"];
		};
	}

	function callback_get_toolbar($args = array())
	{
		$toolbar = &$args["toolbar"];
		$toolbar->add_button(array(
                        "name" => "save",
                        "tooltip" => "Salvesta",
                        "url" => "javascript:document.changeform.submit()",
                        "imgover" => "save_over.gif",
                        "img" => "save.gif",
                ));
		$toolbar->add_button(array(
                        "name" => "brothering",
                        "tooltip" => "Vennastamine",
                        "url" => "#",
			# wtf is brothering supposed to mean?
                        "imgover" => "brothering_over.gif",
                        "img" => "brothering.gif",
                ));
		$toolbar->add_button(array(
                        "name" => "lists",
                        "tooltip" => "Teavita liste",
                        "url" => "#",
                        "imgover" => "lists_over.gif",
                        "img" => "lists.gif",
                ));
		$toolbar->add_button(array(
                        "name" => "archive",
                        "tooltip" => "Arhiiv",
                        "url" => "#",
                        "imgover" => "archive_over.gif",
                        "img" => "archive.gif",
                ));
		$toolbar->add_button(array(
                        "name" => "preview",
                        "tooltip" => "Eelvaade",
                        "url" => aw_global_get("baseurl") . "/" . $args["id"],
                        "imgover" => "preview_over.gif",
                        "img" => "preview.gif",
                ));
	}

	function show($args = array())
	{
		extract($args);
		$d = get_instance("document");
		return $d->gen_preview(array("docid" => $args["id"]));
	}

	function get_brothers($args = array())
	{
		extract($args);
		$sar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($arow = $this->db_next())
		{
			$sar[$arow["parent"]] = $arow["parent"];
		}

		$ob = get_instance("objects");
		$ol = $ob->get_list(true);

		$conf = get_instance("config");
		$df = $conf->get_simple_config("docfolders".aw_global_get("LC"));
		$xml = get_instance("xml");
		$_df = $xml->xml_unserialize(array("source" => $df));

		$ndf = array();

		foreach($_df as $dfid => $dfname)
		{
			$ndf[$dfid] = $ol[$dfid];
		}
		
		if (count($ndf) < 2)
		{
			$ndf = $ol;
		}

		return array($sar,$ndf);
	}

	function update_brothers($args = array())
	{
		extract($args);
		$obj = $this->get_object($id);

		$sar = array(); $oidar = array();
		$this->db_query("SELECT * FROM objects WHERE brother_of = $id AND status != 0 AND class_id = ".CL_BROTHER_DOCUMENT);
		while ($row = $this->db_next())
		{
			$sar[$row["parent"]] = $row["parent"];
			$oidar[$row["parent"]] = $row["oid"];
		}

		$not_changed = array();
		$added = array();
		if (is_array($sections))
		{
			reset($sections);
			$a = array();
			while (list(,$v) = each($sections))
			{
				if ($sar[$v])
				{
					$not_changed[$v] = $v;
				}
				else
				{
					$added[$v] = $v;
				}
				$a[$v]=$v;
			}
		}
		$deleted = array();
		reset($sar);
		while (list($oid,) = each($sar))
		{
			if (!$a[$oid])
			{
				$deleted[$oid] = $oid;
			}
		}

		reset($deleted);
		while (list($oid,) = each($deleted))
		{
			$this->delete_object($oidar[$oid]);
		}
		reset($added);
		while(list($oid,) = each($added))
		{
			if ($oid != $id)	// no recursing , please
			{
				$noid = $this->new_object(array("parent" => $oid,"class_id" => CL_BROTHER_DOCUMENT,"status" => $obj["status"],"brother_of" => $id,"name" => $obj["name"],"comment" => $obj["comment"],"period" => $obj["period"]));
			}
		}
	}
};
?>
