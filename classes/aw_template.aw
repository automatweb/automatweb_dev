<?php
// $Header: /home/cvs/automatweb_dev/classes/aw_template.aw,v 2.11 2001/07/12 23:21:05 duke Exp $
// aw_template.aw - Templatemootor
class tpl
{
	var $name;   // siia paigutame template nime
	var $source; // siia paigutame template source
	var $vars;   // siia paigutame leitud variabled
	var $subs;   // siia arraysse paigutame alamtemplated;
	function tpl($name)
	{
		$this->name = $name;
    $this->subs = array();
		$this->source = "";
			lc_load("definition");
	}
	
	function sink($line)
	{
		$this->source .= $line . "\n";
	}

	function add_sub($object)
	{
		array_push($this->subs,$object);
	}

	function replace_sub($object)
	{
		array_pop($this->subs);
		array_push($this->subs,$object);
	}
};

include_once("$classdir/acl_base.$ext");
class aw_template extends acl_base
{
	// compatibility muutujad
	var $template_dir; // millisest kataloomast templatesid loetakse (string)
	var $tplfile;      // template faili sisu (arr)
	var $templates;    // erinevate templatede sisu salvestatakse siia (arr)
	var $variables;    // siia paigutame imporditud muutujad
	var $ignored;      // siia paigutame muutujad, mille sisu asendatakse tühjusega
	var $expandsubs;   // kas <!-- SUB: blaa muutub {VAR:blaa}-ks?

	// compatibility funktsioonid
	function tpl_init($basedir = "",$expandsubs = 1)
	{
		# kui basedir-il on väärtus, siis read_template otsib
		# templatet $tpldir/basedir kataloomast
		$this->set_root($basedir);
		$this->expandsubs = $expandsubs;
		$this->ignored = array();
		$this->templates = array();
		$this->t_tree = array();
		$this->_init_vars();
		$this->sub_merge = 0;
		global $basedir;
		$this->basedir = $basedir;
	}

	function _init_vars()
	{
		extract($GLOBALS);
		global $status_msg;
		// edaspidi kui on vaja basedir-i kasutada, siis ei pea seda globaalsest skoobist importima
		$this->basedir = $basedir;
		$this->vars(array(
			"self" => $PHP_SELF,
			"ext"  => $ext,
			"rand" => time(),
			"status_msg" => $status_msg,
			"baseurl" => $baseurl
		));
	}

	////
	// !sets the root directory to read templates from
	function set_root($path)
	{
		global $tpldir;
		$this->template_dir = $tpldir . "/$path";
	}

	////
	// !resets all templates and variables
	function reset()
	{
		return $this->tpl_reset();
	}

	////
	// !resets all templates and variables
	function tpl_reset()
	{
		unset($this->tplfile);
		unset($this->templates);
		unset($this->vars);
		$this->_init_vars();
	}

	function ignore($array)
	{
		// see funktsioon ei tee mitte midagi ja on siin ainult backwards
		// compatiblity jaoks
	}

	// ma ei osanud seda mujale panna ;)
	// see on <SELECT> elementide jaoks ...
	// ette antakse array, millest produtseeritakse string, kus iga element on kujul
	// <option value=$key>$value</option>
	// ja see element, mille key on muutujas $active saab ka "selected" tagi
	// TODO: move to defs.aw
	function option_list($active,$array)
	{
		$res = "";
		if (is_array($array))
		{
			while(list($k,$v) = each($array))
			{
				$selected = ($active == $k) ? " selected " : "";
				$res .= sprintf("<option %s value='%s'>%s</option>\n",$selected,$k,$v);
			};
		};
		return $res;
	}

	// multiple <select> elementide jaox, $active on array aktiivsete asjadega
	// TODO: move to defs.aw
	function multiple_option_list($active,$array)
	{
		$res = "";
		while(list($k,$v) = each($array))
		{
			$selected = $active[$k] ? " selected " : "";
			$res .= sprintf("<option %s value='%s'>%s</option>\n",$selected,$k,$v);
		};
		return $res;
	}
        
	// shortcut eelmisele
	// TODO: move to defs.aw
	function picker($active,$array)
	{
		return $this->option_list($active,$array);
	}

	////
	// !Loeb template failist
	function read_template($filename,$dbg = 0)
	{
		global $awt;

		// loeme faili sisse
		$filename = $this->template_dir . "/$filename";
		if (!($source = $this->get_file(array("file" => $filename))))
		{
			global $tpldir;
			$name = substr($filename,strlen($tpldir) + 1);
			$this->raise_error("Template '$name' not found",true);
		};
		return $this->use_template($source);
	}
	
	////
	// !Selle abil saab sisse lugeda kusagilt mujalt (mitte failist) voetud template
	function use_template($source)
	{
		if (isset($awt) && is_object($awt))
		{
			$awt->start("read_template");
		};

		$this->tp = $source;
		
		// paigutame arraysse 
		$this->tlist = array();
		$tlines = explode("\n",$this->tp);
                
		// this is what we call a construct, we can load everything
		// we need here - variables, arrays, objects;
		$construct = array();
                
		// paigutame arraysse root elemendi, default nimega MAIN
		$tpl = new tpl("MAIN");
		$level = 0;
		array_push($construct,$tpl);

		// tsükkel üle faili ridade
		while(list($linenum,$line) = each($tlines))
		{
			// kas see rida alustab subtemplatet?
      if (preg_match("/<!-- SUB: (.*) -->/",$line,$m))
			{
				$level++;
        // jep, loome uue objekti selle nimega
        $tpl = new tpl($m[1]);

				$this->names[] = $m[1];

        // votame constructist aktiivse template
				$last = array_pop($construct);

				$this->tlist[$level][] = $m[1];

        // compatibility jauks
        isset($this->templates[$last->name]) ? $this->templates[$last->name].= $line : $this->templates[$last->name] = $line;

				$this->relations[$m[1]] = $last->name;

        // ja lisame sinna sub-i asemele var-i  
        $last->sink("{VAR:$m[1]}");
        array_push($construct,$last);

        // viga on siin, selles vahemikus
        // vaatame, kas constructis on veel midagi,
        $last1 = array_pop($construct);
        // lisame selle subi kohta info master template sisse
        $last1->add_sub($tpl);
        //$construct[sizeof($construct)-1]->add_sub($tpl);
        array_push($construct,$last1);
        // ja laadime selle objekti constructi sisse
        array_push($construct,$tpl);
        // kas see rida lopetab subtemplate?
      }
			else
			if (preg_match("/<!-- END SUB: (.*) -->/",$line,$m))
			{
				$level--;
        // unloadime viimase objekti constructist
        $last = array_pop($construct);
        if ($last->name != $m[1])
				{
          printf("Broken template. Tried to close '%s' while '%s' was open",$m[1],$last->name);
          die;
        };
      }
			else
			{
        // votame constructist aktiivse template
        $last = array_pop($construct);

        // compatibility jauks
        isset($this->templates[$last->name]) ?  $this->templates[$last->name].= $line : $this->templates[$last->name] = $line;

        // ja lisame sinna töödeldava rea       
        $last->sink($line);

        // viga on siin, selles vahemikus
        // vaatame, kas constructis on veel midagi,
        $last1 = array_pop($construct);
        // kui on, siis 
        if (is_object($last1))
				{
          $kala = array_pop($last1->subs);
          // votame sealsest sub-ide arrayst viimase
          // elemendi
          if ($kala)
					{
            array_push($last1->subs,$last);
            array_push($construct,$last1);
          };
        };
        array_push($construct,$last);
      };
    };
    $last = array_pop($construct);
    $this->construct = $last;
		if (isset($awt) && is_object($awt))
		{
			$awt->stop("read_template");
		};
    return $last;
  }

	////
	// !Saab kysida, kas sellise nimega template on registreeritud
	function is_template($name)
	{
		// wrapper backwards compatibility jaoks
    $retval = $this->get_tpl_by_name($name,array("0"=> $this->construct));
		return $retval;
  }

	function is_parent_tpl($tpl,$parent)
	{
		if (isset($this->relations[$tpl]) && $this->relations[$tpl] == $parent)
		{
			return true;
		} 
		else 
		{
			return false;
		};
	}
       
	////
	// !Tagastab template nime jargi
	function get_tpl_by_name($name,$c = array())
	{
	  $this->result = "";
		$this->t_tree = array();
		return $this->_get_tpl_by_name($name,$c);
  }

  function _get_tpl_by_name($name,$c = array())
	{
		// see on rekusiivne funktsioon
		// esimesel labimisel oleme 0 taseme
    $obj = array();
    reset($c);
		// tsykkel yle esimesel tasemel olevate templatede (0 levelil ainult MAIN)
    while(list($k,$v) = each($c))
		{
			// kui leiti alamtemplate, siis...
      if (is_object($v))
			{
				array_push($this->t_tree,$v->name);
				// loikame $fqtn lopust valja name pikkuse tyki
				// ja vaatame, kas need on vordsed
        if ((".".$name) == substr(".".join(".",$this->t_tree),-(strlen($name)+1)))
				{
          $this->result = $v;
          return $v;
        }
				// vastasel korral, kui sellel templatel ka sub-e ja vastust ei ole veel kaes,
				// siis lahme selle template alamtemplatesid otsima
				elseif ((sizeof($v->subs) > 0) && (!$this->result))
				{
		      $this->_get_tpl_by_name($name,$v->subs);
        };
				array_pop($this->t_tree);
      };
    };
    if ($this->result)
		{
			return $this->result;
    }
		else
		{
			return false;
    };
   }

	////
  // !Impordib muutujad templatesse, seejuures kirjutatakse juba eksisteerivad
	// muutujad yle
  function vars($params)
	{
		reset($params);
		while(list($k,$v) = each($params))
		{
			$this->vars[$k] = $v;
		};
  }

	////
  // !Impordib muutujad, kui muutuja oli juba varem defineeritud, siis liidetakse
	// väärtus
  function vars_merge($params)
	{
		while(list($k,$v) = each($params))
		{
			$this->vars[$k] .= $v;
    };
  }

  // impordime andmestruktuuri mingi template juurde
  function define_data($tpl,$branches)
	{
    if (!is_array($branches))
		{
      return false;
    }
		else
		{
      $this->branches[$tpl] = $branches;
    };
  }

	////
	// !see on nüüd pisike häkk. Nimelt saab selle funktsiooni abil parsida kusagilt mujalt sissetoodud
	// templatekoodi (s.t. asendada selles olevad muutujanimed väärtustega).
	// tpledit vajab seda
	function localparse($src = "",$vars = array())
	{
		return preg_replace("/{VAR:(.+?)}/e","\$vars[\"\\1\"]",$src);
	}
		
	////
	// !This is where all the magic takes place
	function parse($object = "MAIN") 
	{
		// siia voib anda ette nii objekti nime, kui ka
		// viite objektile. Esimesel juhul leiab ta ise
		// objekti viite ja töötab selle kalllal edasi

		// defineerime yhe lokaalse funktsiooni, mis on vajalik uue parseri jaoks
		if (!is_object($object))
		{
			$name = $object;
			$new_object = $this->get_tpl_by_name($object,array("0" => $this->construct));
			$object = $new_object;
		};

		// kogu asendus tehakse ühe reaga
		// "e" regexpi lõpus tähendab seda, et teist parameetrit käsitletakse php koodina,
		// mis eval-ist läbi lastakse. 
		$src = preg_replace("/{VAR:(.+?)}/e","isset(\$this->vars[\"\\1\"]) ? \$this->vars[\"\\1\"] : \"\"",$object->source);

		// võtame selle maha ka
		global $status_msg;
		if ($status_msg)
		{
			session_unregister("status_msg");
		};

		if ($this->sub_merge == 1)
		{
	   	isset($this->vars[$object->name]) ? $this->vars[$object->name] .= $src : $this->vars[$object->name] = $src;
		}
		else
		{
			#$this->vars[$object->name] = $src;
		};
		return $src;
  }

  // joonistab sektsiooni
  function draw_section($params)
	{
		$section_id = $params["section_id"];  // millist sektsiooni joonistame
		$parent     = $params["parent"];      // millisest sektsioonist joonistamist alustame
		$use_tpl    = $params["use_tpl"];     // millist templatet selleks kasutame (obj)
		$main_tpl   = $params["main_tpl"];    // millisest templatest joonistamist alustame (obj)

		if ((!is_object($use_tpl)) || (!is_object($main_tpl)))
		{
			print "unknown template";
			die;
		};

		$current = $this->branches[$main_tpl->name][$section_id];
		if (!(is_array($current) && sizeof($current) > 0) )
		{
			return;
		};
		reset($current);
		while(list($k,$v) = each($current))
		{
      $this->vars($v);
      $this->vars_merge(array($main_tpl->name => $this->parse($use_tpl)));
      if (sizeof($use_tpl->subs) > 0)
			{
        $new = $use_tpl->subs[0]->name;
        $newtpl = $this->get_tpl_by_name($new,array("0" => $this->construct));
        $this->draw_section(array(
					"section_id" => $v[oid],
          "parent"     => $parent,
          "use_tpl"    => $newtpl,
          "main_tpl"   => $main_tpl
				));
      };
    };
  }
};
?>
