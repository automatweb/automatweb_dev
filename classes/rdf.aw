<?php
// $Header: /home/cvs/automatweb_dev/classes/Attic/rdf.aw,v 2.6 2002/06/10 15:50:54 kristo Exp $
// miski simpel klass rss-i väljastamiseks

define(ITEM_TPL,"
<item rdf:about=\"%s\">
<title>%s</title>
<link>%s</link>
<dc:description>%s</dc:description>
<dc:creator>%s</dc:creator>
<dc:publisher>%s</dc:publisher>
<dc:date>%s</dc:date>
<dc:language>ee</dc:language>
</item>
");

class rdf 
{
	var $li;
	var $items;
	function rdf($args = array())
	{
		$this->li = "";
		$this->items = "";
		extract($args);
		$this->link = $link;
		$this->title = $this->_convert($title);
		$this->description = $this->_convert($description);
		$this->about = $about;
		lc_load("definition");
	}

	////
	// !Generates a RSS feed from one or more documents
	function gen_feed($args = array())
	{


	}

	////
	// !Konverdib sisendstringi sobivale kujule, ehk 2x läbi htmlentities funktsiooni
	// Ärge küsige minult, miks seda vaja on
	function _convert($str)
	{
		$str = preg_replace("/#(\w+?)(\d+?)(v|k|p|)#/i","",strip_tags($str));
		return htmlentities(htmlentities($str));
	}

	function add_item($args = array())
	{
		$baseurl = $this->cfg["baseurl"];
		$ext = $this->cfg["ext"];
		if (is_array($args))
		{
			extract($args);
			$id = ($id) ? $id : $docid;
			$description = ($description) ? $description : $lead;
			$creator = $this->_convert($author);
			$link = sprintf("%s/index.%s/section=%d",$baseurl,$ext,$id);
			$title = $this->_convert($title);
			$description = $this->_convert($description);
			$publisher = $this->_convert($publisher);
			$date = date("Y-m-d",$args["modified"]) . "T" . date("H:i",$args["modified"]) . "+02:00";
			$this->li .= sprintf("<rdf:li resource=\"%s\"/>\n",$link);
			$this->items .= sprintf(ITEM_TPL,$link,$title,$link,$description,$creator,aw_ini_get("document.publisher"),$date); 
		}
	}


	function gen_output()
	{
		$retval = "";
	        $retval .= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n\n";
	        $retval .= "<rdf:RDF\n";
	        $retval .= "xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n";
	        $retval .= "xmlns:dc=\"http://purl.org/dc/elements/1.1/\"\n";
	       	$retval .= "xmlns=\"http://purl.org/rss/1.0/\"\n";
	        $retval .= ">\n\n";

		$retval .= sprintf("<channel rdf:about=\"%s\">\n",$this->about);
		$retval .= sprintf("<title>%s</title>\n",$this->title);
		$retval .= sprintf("<link>%s</link>\n",$this->link);
		$retval .= sprintf("<description>%s</description>\n",$this->description);
		$retval .=  "<items>\n<rdf:Seq>\n";
		$retval .= $this->li;
		$retval .= "</rdf:Seq>\n</items>\n</channel>\n\n";

		$retval .= $this->items;

		$retval .= "\n\n</rdf:RDF>\n";
		return $retval;
	}
};
?>
