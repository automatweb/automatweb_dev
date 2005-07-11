<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_search/site_search_content_grp_html.aw,v 1.1 2005/07/11 13:01:47 kristo Exp $
// site_search_content_grp_html.aw - Otsingu html indekseerija 
/*

@classinfo syslog_type=ST_SITE_SEARCH_CONTENT_GRP_HTML relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default field=meta
@default method=serialize

@default group=general

	@property url type=textbox 
	@caption Sait, mida indekseerida

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

	@property indexer_run_always type=checkbox ch_value=1
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

class site_search_content_grp_html extends class_base
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
				$prop["value"] = html::href(array(
					"caption" => t("K&auml;ivita"),
					"url" => $this->mk_my_orb("ss_gen_static_content", array(
						"id" => $arr["obj_inst"]->id()
					))
				));
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
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	/**

		@attrib name=ss_gen_static_content

		@param id required type=int 
	**/
	function ss_gen_static_content($arr)
	{
		$o = obj($arr["id"]);

		$url = $o->prop("url");

		$this->run_indexer($url, $o);
	}

	function run_indexer($url, $o)
	{
		$this->do_crawl($url, $o);
	}

	function do_crawl($url, $o)
	{
		$this->pages = array();
		classload("contentmgmt/site_search/parsers/parser_finder");

		$this->baseurl = $url;
		$this->queue = get_instance("core/queue");
		$this->ignore_pages = $this->make_keys(explode("\n", trim($o->prop("ignore_urls"))));

		if ($o->meta("crawl_state") != "started")
		{
			// get new crawl index
			// mark crawl as started
			$o->set_meta("crawl_state", "started");
			$o->set_meta("crawl_tm", time());
			$o->save();

			$this->queue->push($url);
		}
		else
		{
			// restore queue
			$this->queue->set_all($o->meta("stored_queue"));
			$this->pages = $this->make_keys($o->meta("stored_visited_pages"));
			echo "restored queue, items = ".$this->queue->count()." pages = ".count($this->pages)."<br>";
		}

		$this->process_queue($o->id());

		// mark crawl as finished
		// remove all docs whose last_modified time is less than start of crawl
		$o->set_meta("crawl_state", "done");
		$o->set_meta("stored_visited_pages", "");
		$o->set_meta("stored_queue", "");
		
		$this->db_query("DELETE FROM static_content WHERE site_id = ".$o->id()." AND created_by = 'ss_html' AND last_modified < ".$o->meta("crawl_tm"));
		$o->save();

		echo "all done, fetched ".count($this->pages)." files, containing ".$this->size." bytes of text <br>\n";
		flush();
		die();
	}

	function process_queue($indexer_id)
	{
		$o = obj($indexer_id);

		$cnt = 0;
		while ($this->queue->has_more())
		{
			$url = $this->queue->get();

			if (isset($this->pages[$url]))
			{
				continue;
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
				continue;
			}
		
			$i = parser_finder::instance($url);
			if ($i === NULL)
			{
				continue;
			}

			$this->pages[$url]["o"] =& $i;
			$page =& $this->pages[$url]["o"];

			$this->_store_content($page, $indexer_id);

			$urls = $page->get_links();
			foreach($urls as $url)
			{
				if (!$this->is_external($url) && !isset($this->pages[$url]) && !$this->queue->contains($url))
				{
					$this->queue->push($url);
				}
			}

			if (++$cnt > 100)
			{
				$this->_store_queue_for_restart(obj($indexer_id));
				$cnt = 0;
				echo "page count over 100, storing queue for restart, queue contains ".$this->queue->count()." items <br>\n";
				flush();
			}

			if ($o->prop("indexer_sleep") > 0)
			{
				sleep($o->prop("indexer_sleep"));
			}
		}
	}

	function _store_queue_for_restart($o)
	{
		$o->set_meta("stored_queue", $this->queue->get_all());
		$o->set_meta("stored_visited_pages", array_keys($this->pages));
		aw_disable_acl();
		$o->save();
		aw_restore_acl();
	}

	function is_external($link)
	{
		if (substr($link, 0, 4) != "http" || (substr($link,0,4) == "http" && strpos($link, $this->baseurl) === false))
		{
//			echo "is_external($link) returning true <br />";
			return true;
		}
//		echo "is_external($link) returning false<br />";
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
