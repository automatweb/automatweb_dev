<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ecard/ecard.aw,v 1.1 2005/09/09 22:08:34 ekke Exp $
// ecard.aw - E-kaart 
// Sort of for internal use. Go see ecard_manager
/*

@classinfo syslog_type=ST_ECARD relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

@property comment type=textarea field=comment
@caption Tekst kaardil

@default method=serialize
@default field=meta

@property from_name type=textbox
@caption Saatja nimi

@property from_mail type=textbox
@caption Saatja aadress


@property to_name type=textbox
@caption Saaja nimi

@property to_mail type=textbox
@caption Saaja aadress

@property senddate type=date_select
@caption Saatmise kuupäev



@groupinfo image caption="Pilt"
@default group=image

	@property image type=releditor reltype=RELTYPE_IMAGE mode=form rel_id=first props=file
	@caption Pilt
	
@groupinfo conf caption="Seaded"
@default group=conf

	@property position type=chooser orient=vertical
	@caption Paigutus lehel

	@property hash type=text
	@caption Kood kaardi nägemiseks

	@property spy type=checkbox field=flags method=bitmask ch_value=16
	@caption Vaatamisel saada teade



@reltype IMAGE value=1 clid=CL_IMAGE
@caption Piltide kataloog


*/

class ecard extends class_base
{
	function ecard()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"clid" => CL_ECARD
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
			case 'position':
				$prop["options"] = array(
					1 => t("Tekst pildi all"),
					2 => t("Tekst pildi kõrval"),
				);
			break;
		};
		return $retval;
	}
/*
	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}
*/
//-- methods --//
}
?>
