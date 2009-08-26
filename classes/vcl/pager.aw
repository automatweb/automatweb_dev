<?php
// pager VCL component
/*
@classinfo maintainer=markop
*/
class pager extends class_base
{
	function pager()
	{
		$this->init(array(
			"tpldir" => "vcl/pager",
		));
		$this->elements = 1000;//default
		$this->per_page = 100;//default
	}

	function init_vcl_property($arr)
	{
		return array($this->name => $res);
	}

	function process_vcl_property($arr)
	{
	
	}

	function get_html()
	{
		$page = empty($_GET["page"]) ? 0 : $_GET["page"];

		$pages = $this->elements / $this->per_page;
		$pages = (int)$pages;
		if($this->elements % $this->per_page) $pages++;
		if($pages > 1)
		{
			$this->read_template("show.tpl");
			if($page > 2)
			{
				$this->vars(array("pager_url" => aw_url_change_var("page", $page - 1)));
				$this->vars(array("PAGE_PREV" => $this->parse("PAGE_PREV")));
			}
			if($page < ($pages-3))
			{
				$this->vars(array("pager_url" => aw_url_change_var("page", $page + 1)));
				$this->vars(array("PAGE_NEXT" => $this->parse("PAGE_NEXT")));
			}

			$page_str = "";
			
			$x = max(array(0,$page - 2));
			$y = 0;
			if($x+$y > 1)
			{
				$page_str.= $this->parse("PAGE_SEP");
			}
			while($y < 5)
			{
				if($x+$y >= $pages)
				{
					break;
				}
				$this->vars(array("pager_url" => aw_url_change_var("page", ($x+$y))));
				$this->vars(array("pager_nr" => ($x + $y + 1)));

				
				if($x+$y == $page)
				{
					$page_str.= $this->parse("PAGE_SEL");
				}
				else
				{
					$page_str.= $this->parse("PAGE");
				}
				$y++;
			}

			if($x+$y + 1 < $pages)
			{
				$page_str.= $this->parse("PAGE_SEP");
			}

			$this->vars(array(
				"PAGE" => $page_str,
				"PAGE_SEL" => " ",
			));
			return $this->parse("PAGER");
		}
		exit_function("products_show::end");
		exit_function("products_show::show");
		return "";
	}


}
?>
