<?php
// $Header: /home/cvs/automatweb_dev/classes/expp/expp_site_content.aw,v 1.1 2005/10/17 10:32:05 dragut Exp $
// expp_site_content.aw - expp_site_content (nimi) 
/*

@classinfo syslog_type=ST_EXPP_SITE_CONTENT relationmgr=yes no_comment=1 no_status=1 prop_cb=1

@default table=objects
@default group=general

*/

class expp_site_content extends class_base
{

	var $image_tag = "";
	var $document_content = "";
	var $connections_to_links = array();

	function expp_site_content()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "automatweb/menuedit",
			"clid" => CL_EXPP_SITE_CONTENT
		));
	}

	function show($arr) {
		$retHTML = '';
		if( isset( $GLOBALS['expp_site'] ) && !empty($GLOBALS['expp_site'])) {
			$ol = new object_list(array(
				"class_id" => CL_EXPP_JOURNAL_MANAGEMENT,
				"code" => $GLOBALS['expp_site'],
			));
			if ($ol->count() > 0)
			{
				$o = $ol->begin();
				$image_obj = $o->get_first_obj_by_reltype("RELTYPE_COVER_IMAGE");

				if (!empty($image_obj))
				{
					$image_inst = get_instance(CL_IMAGE);
					$this->image_tag = $image_inst->make_img_tag_wl($image_obj->id());
		
				}

				$document_obj = $o->get_first_obj_by_reltype("RELTYPE_GENERAL_DOCUMENT");

				if (!empty($document_obj))
				{
					$this->document_content = $document_obj->prop("content");
		
				}

				$this->connections_to_links = $o->connections_from(array(
					"type" => "RELTYPE_GENERAL_LINK",
				));
/*				
				foreach ($connections_to_links as $conn_to_link)
				{
					$link = $conn_to_link->to();
		//			$link_id = $conn_to_link->prop("to");
					
				}
*/
			}
			
		}
//		return $retHTML;
	}

	function on_get_subtemplate_content($arr) {
		$this->show();
		if (empty($this->image_tag) && empty($this->document_content) && empty($this->connections_to_links))
		{
			return;
		} 
		$this->read_template("main.tpl");

		$lingid = "";
		foreach ($this->connections_to_links as $conn_to_link)
		{
			$link = $conn_to_link->to();
			$this->vars(array(
				"URL" => $link->prop("url"),
				"NAME" => $link->name(),
			));

			$lingid .= $this->parse("DOC_LINK_SUB");
		}
		if (!empty($lingid))
		{
			$this->vars(array(
				"DOC_LINK_SUB" => $lingid
			));
			$lingid = $this->parse("DOC_LINK");
		}
		$this->vars(array(
			"DOC_IMAGE" => $this->image_tag,
			"DOC_CONTENT" => $this->document_content,
			"DOC_LINK" => $lingid,
		));
		$arr["inst"]->vars(array(
			"VAIKE_DOC" => $this->parse("VAIKE_DOC"),
		));
	}
	
	function register( $in ) {
		$GLOBALS['expp_site'] = $in;
	}
}
?>
