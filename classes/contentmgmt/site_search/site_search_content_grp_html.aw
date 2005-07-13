<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_search/site_search_content_grp_html.aw,v 1.2 2005/07/13 14:27:06 kristo Exp $
// site_search_content_grp_html.aw - Otsingu html indekseerija 
/*

@classinfo syslog_type=ST_SITE_SEARCH_CONTENT_GRP_HTML relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default field=meta
@default method=serialize

@default group=general

	@property url type=textbox 
	@caption Sait, mida indekseerida

	@property i_status type=text store=no
	@caption Staatus

	@property run type=text store=no

@default group=ign_urls

	@property ignore_urls type=textarea rows=30 cols=80
	@caption Ignoreeritavad aadressid

@default group=regex

	@property title_regex type=textbox default="/<TITLE>(.*)<\/TITLE>/iUs"
	@caption Pealkirja regulaaravaldis

	@property content_regex type=textbox default=""
	@caption Sisu regulaarvaldis

@default group=indexer

	@property indexer_sleep type=textbox size=5
	@caption Mitu sekundit oodata lehtede vahel

	@property bg_run_always type=checkbox ch_value=1
	@caption Indekseerija k&auml;ib pidevalt

	@property rc_txt type=text subtitle=1
	@caption Kordus indekseerija k&auml;ivitamiseks

	@property recur_edit type=releditor reltype=RELTYPE_RECURRENCE use_form=emb rel_id=first
	@caption Automaatse impordi seadistamine
	

@groupinfo ign_urls caption="Ingoreeri aadresse"
@groupinfo regex caption="Regulaarvaldised"
@groupinfo indexer caption="Indekseerija"

@reltype RECURRENCE value=1 clid=CL_RECURRENCE
@caption kordus

*/

classload("core/run_in_background");
class site_search_content_grp_html extends run_in_background
{
	function site_search_content_grp_html()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/site_search/site_search_content_grp_html",
			"clid" => CL_SITE_SEARCH_CONTENT_GRP_HTML
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "run":
				return $this->bg_run_get_property_control($arr);
				break;

			case "i_status":
				return $this->bg_run_get_property_status($arr);
				break;
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case "bg_run_always":
				$this->bg_check_scheduler();
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function bg_run_init($o)
	{
		classload("contentmgmt/site_search/parsers/parser_finder");

		$this->pages = array();
		$this->baseurl = $o->prop("url");

		$this->queue = get_instance("core/queue");
		$this->queue->push($this->baseurl);

		$this->ignore_pages = $this->make_keys(explode("\n", trim($o->prop("ignore_urls"))));
	}

	function bg_run_continue($o)
	{
		$this->queue->set_all($o->meta("stored_queue"));
		$this->pages = $this->make_keys($o->meta("stored_visited_pages"));
		echo "restored queue, items = ".$this->queue->count()." pages = ".count($this->pages)."<br>";
	}

	function bg_run_step($o)
	{
		$url = $this->queue->get();

		if (isset($this->pages[$url]))
		{
			return $this->queue->has_more() ? BG_OK : BG_DONE;
		}

		$cont = true;
		foreach($this->ignore_pages as $ign_url)
		{
			if (strstr($url, $ign_url))
			{
				$cont = false;
			}
		}
		if (!$cont)
		{
			return $this->queue->has_more() ? BG_OK : BG_DONE;
		}
	
		$i = parser_finder::instance($url);
		if ($i === NULL)
		{
			return $this->queue->has_more() ? BG_OK : BG_DONE;
		}

		$this->pages[$url]["o"] =& $i;
		$page =& $this->pages[$url]["o"];

		$this->_store_content($page, $o->id());

		$urls = $page->get_links();
		foreach($urls as $url)
		{
			if (!$this->is_external($url) && !isset($this->pages[$url]) && !$this->queue->contains($url))
			{
				$this->queue->push($url);
			}
		}

		if ($o->prop("indexer_sleep") > 0)
		{
			sleep($o->prop("indexer_sleep"));
		}
		return $this->queue->has_more() ? BG_OK : BG_DONE;
	}

	function bg_checkpoint($o)
	{
		echo "page count over ".$this->bg_checkpoint_steps.", storing queue for restart, queue contains ".$this->queue->count()." items <br>\n";
		flush();

		$o->set_meta("stored_queue", $this->queue->get_all());
		$o->set_meta("stored_visited_pages", array_keys($this->pages));
		aw_disable_acl();
		$o->save();
		aw_restore_acl();
	}

	function bg_run_finish($o)
	{
		$o->set_meta("stored_visited_pages", "");
		$o->set_meta("stored_queue", "");
		$o->set_meta("bg_run_log", sprintf(t("Indekseerija k&auml;ivitati %s, l&otilde;petas edukalt %s.\nIndekseeriti %s lehte, mis sialdasid %s baiti teksti."), 
			date("d.m.Y H:i", $o->meta("crawl_tm")),
			date("d.m.Y H:i", time()),
			count($this->pages),
			number_format($this->size)
		));
		$this->db_query("DELETE FROM static_content WHERE site_id = ".$o->id()." AND created_by = 'ss_html' AND last_modified < ".$o->meta("bg_run_start"));
		echo "all done, fetched ".count($this->pages)." files, containing ".$this->size." bytes of text <br>\n";
	}

	function bg_run_get_log_entry($o)
	{
		$res  = sprintf(t("Indekseerija nimega %s alustas t&ouml;&ouml;d %s.\n"), $o->name(), date("d.m.Y H:i", $o->meta("bg_run_start")));
		$res .= sprintf(t("Hetkel on indekseeritud %s lehte.\n"), count($this->pages));
		$res .= sprintf(t("J&auml;rjekorras on %s lehte.\n"), $this->queue->count());
		$res .= sprintf(t("Viimati uuendati staatust %s.\n"), date("d.m.Y H:i"));

		return $res;
	}

	function bg_halt($o)
	{
		$o->set_meta("stored_visited_pages", "");
		$o->set_meta("stored_queue", "");
		$o->set_meta("bg_run_log", sprintf(t("Indekseerija k&auml;ivitati %s, peatati %s."), 
			date("d.m.Y H:i", $o->meta("bg_run_start")),
			date("d.m.Y H:i", time())
		));
	}

	function is_external($link)
	{
		if (substr($link, 0, 4) != "http" || (substr($link,0,4) == "http" && strpos($link, $this->baseurl) === false))
		{
			return true;
		}
		return false;
	}

	function _store_content(&$page, $indexer_id)
	{
		$o = obj($indexer_id);

		$h_id = md5($page->get_url());
		$fc = $page->get_text_content($o);
		$modified = $page->get_last_modified();
		$title = $page->get_title($o);
		$url = $page->get_url();

		$this->quote(&$fc);
		$this->quote(&$title);

		$this->size += strlen($fc);

		// see if we already got this hash-indexer-site_id copy and if we do, update it
		$cnt = $this->db_fetch_field("SELECT count(*) AS cnt FROM static_content WHERE id = '$h_id' AND created_by = 'ss_html' AND site_id = '$indexer_id'", "cnt");
		if ($cnt > 0)
		{
			$q = "
				UPDATE static_content SET 
					content = '$fc', modified = '$modified',
					title = '$title', last_modified = '".time()."'
				WHERE
					id = '$h_id' AND created_by = 'ss_html' AND site_id = '$indexer_id'
			";
		}
		else
		{
			$q = "
				INSERT INTO static_content(
					id, 					content, 					modified, 					 
					title,						url,						created_by,
					site_id, last_modified
				) 
				VALUES(
					'$h_id',				'$fc',						'$modified',				
					'$title',					'$url',						'ss_html',
					'$indexer_id', ".time()."
				)
			";
		}
		$this->db_query($q);
	}
}
?>
