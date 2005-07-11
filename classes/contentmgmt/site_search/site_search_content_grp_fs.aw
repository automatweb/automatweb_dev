<?php
// $Header: /home/cvs/automatweb_dev/classes/contentmgmt/site_search/site_search_content_grp_fs.aw,v 1.1 2005/07/11 13:01:47 kristo Exp $
// site_search_content_grp_fs.aw - Otsingu failis&uuml;steemi indekseerija 
/*

@classinfo syslog_type=ST_SITE_SEARCH_CONTENT_GRP_FS relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general

@property path type=textbox field=meta method=serialize
@caption Kataloog, mida indekseerida

*/

class site_search_content_grp_fs extends class_base
{
	function site_search_content_grp_fs()
	{
		$this->init(array(
			"tpldir" => "contentmgmt/site_search/site_search_content_grp_fs",
			"clid" => CL_SITE_SEARCH_CONTENT_GRP_FS
		));
	}

	function get_property($arr)
	{
		$this->ss_gen_static_content(array(
			"obj" => $arr["obj_inst"]
		));

		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
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


	function ss_gen_static_content($arr)
	{
		$o = $arr["obj"];

		$path = $o->prop("path");

		$this->do_crawl($path, $o);
	}

	function do_crawl($path, $o)
	{
		$this->pages = array();
		classload("contentmgmt/site_search/parsers/parser_finder");

		$this->path = $path;
		$this->queue = get_instance("core/queue");

		if ($o->meta("crawl_state") != "started")
		{
			// get new crawl index
			// mark crawl as started
			$o->set_meta("crawl_state", "started");
			$o->set_meta("crawl_tm", time());
			$o->save();

			$this->queue->push($path);
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
		
		$this->db_query("DELETE FROM static_content WHERE site_id = ".$o->id()." AND created_by = 'ss_fs' AND last_modified < ".$o->meta("crawl_tm"));
		$o->save();

		echo "all done, fetched ".count($this->pages)." files, containing ".$this->size." bytes of text <br>\n";
		flush();
		die();
	}

	function process_queue($indexer_id)
	{
		$cnt = 0;
		while ($this->queue->has_more())
		{
			$path = $this->queue->get();

			if (isset($this->pages[$path]))
			{
				continue;
			}

			$i = parser_finder::instance($path);
			if ($i === NULL)
			{
				continue;
			}

			$this->pages[$path]["o"] =& $i;
			$page =& $this->pages[$path]["o"];

			$this->_store_content($page, $indexer_id);

			$paths = $page->get_links();
			foreach($paths as $path)
			{
				if (!isset($this->pages[$path]) && !$this->queue->contains($path))
				{
					$this->queue->push($path);
				}
			}

			if (++$cnt > 100)
			{
				$this->_store_queue_for_restart(obj($indexer_id));
				$cnt = 0;
				echo "page count over 100, storing queue for restart, queue contains ".$this->queue->count()." items <br>\n";
				flush();
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

	function _store_content(&$page, $indexer_id)
	{
		$h_id = md5($page->get_url());
		$fc = $page->get_text_content();
		$modified = $page->get_last_modified();
		$title = $page->get_title();
		$url = $page->get_url();

		$this->quote(&$fc);
		$this->quote(&$title);

		$this->size += strlen($fc);

		// see if we already got this hash-indexer-site_id copy and if we do, update it
		$cnt = $this->db_fetch_field("SELECT count(*) AS cnt FROM static_content WHERE id = '$h_id' AND created_by = 'ss_fs' AND site_id = '$indexer_id'", "cnt");
		if ($cnt > 0)
		{
			$q = "
				UPDATE static_content SET 
					content = '$fc', modified = '$modified',
					title = '$title', last_modified = '".time()."'
				WHERE
					id = '$h_id' AND created_by = 'ss_fs' AND site_id = '$indexer_id'
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
					'$title',					'$url',						'ss_fs',
					'$indexer_id', ".time()."
				)
			";
		}
		$this->db_query($q);
	}
}
?>
