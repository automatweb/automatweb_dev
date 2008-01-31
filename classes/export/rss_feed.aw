<?php
// rss_feed.aw - RSS voog
/*

@classinfo syslog_type=ST_RSS_FEED relationmgr=yes no_comment=1 no_status=1 prop_cb=1  maintainer=voldemar

@groupinfo dfn caption="Definitsioon"

@default table=objects
@default field=meta
@default method=serialize

@property channel_language type=hidden
@property channel_copyright type=hidden
@property channel_managingeditor type=hidden
@property channel_webmaster type=hidden
@property channel_pubdate type=hidden
@property channel_lastbuilddate type=hidden
@property channel_category type=hidden
@property channel_generator type=hidden
@property channel_docs type=hidden
@property channel_cloud type=hidden
@property channel_ttl type=hidden
@property channel_image type=hidden
@property channel_rating type=hidden
@property channel_textinput type=hidden
@property channel_skiphours type=hidden
@property channel_skipdays type=hidden
@property item_dfn type=hidden


@default group=general
	@property channel_title type=textbox
	@caption Kanali nimi

	@property channel_link type=textbox
	@caption Kanali link

	@property channel_description type=textarea rows=5 cols=20
	@caption Kanali kirjeldus

	@property classes type=select multiple=1 size=10
	@caption Klassid


@default group=dfn
	@property item_dfn_tbl type=table store=no
	@caption Objektide definitsioonid


@reltype CONTROLLER value=1 clid=CL_CFGCONTROLLER
@caption Kontroller


*/

class rss_feed extends class_base
{
	function rss_feed()
	{
		$this->init(array(
			"tpldir" => "export/rss_feed",
			"clid" => CL_RSS_FEED
		));
	}

	function _get_classes($arr)
	{
		$all_classes = aw_ini_get("classes");
		$options = array();

		foreach ($all_classes as $clid => $data)
		{
			$options[$clid] = $data["name"] . " [" . (false === strpos($data["file"], "/") ? $data["file"] : substr(strrchr($data["file"], "/"), 1)) . "]";
		}

		natcasesort($options);
		$arr["prop"]["options"] = $options;
	}

	function _get_item_dfn_tbl($arr)
	{
		$o = $arr["obj_inst"];
		$table =& $arr["prop"]["vcl_inst"];
		$all_classes = aw_ini_get("classes");
		$classes = $o->prop("classes");
		$options = array();
		$controller_options = array();
		$cl_cfgutils = get_instance("cfg/cfgutils");

		$table->define_field(array(
			"name" => "element",
			"caption" => t("Element")
		));

		$controllers = $o->connections_from(array("type" => "RELTYPE_CONTROLLER"));
		foreach ($controller_connections as $c)
		{
			$controller_options[" "] = t("Kontrollerid:");
			$controller_options[$c->prop("to")] = "&nbsp;&nbsp;" . $c->prop("to.name");
		}

		foreach ($classes as $clid)
		{
			$table->define_field(array(
				"name" => $clid,
				"caption" => $all_classes[$clid]["name"]
			));

			$properties = $cl_cfgutils->load_properties(array("clid" => $clid));
			$options[$clid] = array(0 => t("Omadused:"));

			foreach ($properties as $name => $data)
			{
				$options[$clid][$name] = "&nbsp;&nbsp;" . (empty($data["caption"]) ? $name : ($data["caption"] . " [" . $name . "]"));
			}

			$options[$clid] += $controller_options;
		}

		$elements = array(
			array(1, "title", t("The title of the item.")),
			array(2, "link", t("The URL of the item.")),
			array(3, "description", t("The item synopsis.")),
			array(4, "author", t("Email address of the author of the item.")),
			array(5, "category", t("Includes the item in one or more categories.")),
			array(6, "comments", t("URL of a page for comments relating to the item.")),
			array(7, "enclosure", t("Describes a media object that is attached to the item.")),
			array(8, "pubdate", t("Indicates when the item was published.")),
			array(9, "source", t("The RSS channel that the item came from.")),
		);
		$table->set_default_sortby("ord");
		$table->set_default_sorder("asc");
		$rssitemdfn = $o->prop("item_dfn");

		foreach ($elements as $el_data)
		{
			$data = array(
				"ord" => $el_data[0],
				"element" => '<span title="' . $el_data[2] . '">' . $el_data[1] . '</span>'
			);

			foreach ($classes as $clid)
			{
				$id = $el_data[1];
				$data[$clid] = html::select(array(
					"name" => "rssitemdfn[" . $clid . "][" . $id . "]",
					"options" => $options[$clid],
					"value" => isset($rssitemdfn[$clid][$id]) ? $rssitemdfn[$clid][$id] : null
				));
			}

			$table->define_data($data);
		}
	}

	function _set_item_dfn_tbl($arr)
	{
		$o->set_prop("item_dfn", $arr["request"]["rssitemdfn"]);
	}

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}
}

?>
