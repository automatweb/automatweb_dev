<?php

class links_display 
{
	////
	// !Hoolitseb ntx doku sees olevate extlinkide aliaste parsimise eest (#l2#)
	function parse_alias($args = array())
	{
		extract($args);

		$this->img = false;

		list($url,$target,$caption) = $this->draw_link($alias["target"]);
		if ($this->img)
		{
			$caption = $this->img;
		};
		$url = str_replace("'", "\"", $url);
		$vars = array(
			"url" => $url,
			"caption" => $caption,
			"target" => $target,
			"img" => $this->img,
			"real_link" => $this->real_link
		);
		if (isset($tpls["link"]))
		{
			$replacement = trim(localparse($tpls["link"],$vars));
		}
		else
		{
			if ($this->img)
			{
				$alt = $this->trans_get_val($this->cur_link, "alt");

				$replacement = sprintf("<a href='%s' %s alt='%s' title='%s'><img src='%s' alt='%s' border='0'></a>",$url,$target,$alt,$alt,$this->img,$alt);
			}
			else
			{
				$replacement = sprintf("<a href='%s' %s alt='%s' title='%s'>%s</a>",$url,$target,$alt,$alt,$caption);
			}
		};
		$this->img = "";
		return $replacement;
	}

	function trans_get_val($obj, $prop)
	{
		if ($prop == "name")
		{
			$val = $obj->name();
		}
		else
		{
			$val = $obj->prop($prop);
		}

		if (aw_ini_get("user_interface.content_trans") == 1 && ($cur_lid = aw_global_get("lang_id")) != $obj->lang_id())
		{
			$trs = $obj->meta("translations");
			if (isset($trs[$cur_lid]))
			{
				$val = $trs[$cur_lid][$prop];
			}
		}

		return $val;	
	}	

	function draw_link($target)
	{
		$link = obj($target);
		$this->cur_link = $link;

		$url_pv = $this->trans_get_val($link, "url");

		if (strpos($url_pv,"@") > 0)
		{
			$linksrc = $url_pv;
		}
		elseif (aw_ini_get("extlinks.directlink") == 1)
		{
			$linksrc = $url_pv;
		}
		else
		{
			$linksrc = aw_ini_get("baseurl")."/".$link->id();
		};
		$this->real_link = $url_pv;

		if ($link->prop("link_image_check_active") && ($link->prop("link_image_active_until") < 100 || $link->prop("link_image_active_until") >= time()) )
		{
			$img = new object_list(array(
				"parent" => $link->id(),
				"class_id" => CL_FILE
			));

			$awf = get_instance(CL_FILE);
			if ($img->count() > 0 && $awf->can_be_embedded($o =& $img->begin()))
			{
				$img = $awf->get_url($o->id(),"");
			}
			else
			{
				$img = "";
			};

			$this->img = $img;
		}

		if ($link->prop("use_javascript"))
		{
			$target = sprintf("onClick='javascript:window.open(\"%s\",\"w%s\",\"toolbar=%d,location=%d,menubar=%d,scrollbars=%d,width=%d,height=%d\")'",
				$linksrc,
				$link->id(),
				$link->prop("newwintoolbar"),
				$link->prop("newwinlocation"),
				$link->prop("newwinmenu"),
				$link->prop("newwinscroll"),
				$link->prop("newwinwidth"),
				$link->prop("newwinheight")
			);
			$url = "javascript:void(0)";
		}
		else
		{
			$url = $linksrc;
			$target = $link->prop("newwindow") ? "target='_blank'" : "";
		};


		return array($url,$target,$this->trans_get_val($link, "name"));
	}
}
?>
