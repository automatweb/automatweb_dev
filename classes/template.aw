<?php
class tpl {
	var $name;   // siia paigutame template nime
	var $source; // siia paigutame template source
	var $vars;   // siia paigutame leitud variabled
	var $subs;   // siia arraysse paigutame alamtemplated;
	function tpl($name) {
		$this->name = $name;
		$this->subs = array();
	}

	function sink($line) {
		$this->source .= $line;
	}

	function add_sub($object) {
		array_push($this->subs,$object);
	}

	function replace_sub($object) {
		array_pop($this->subs);
		array_push($this->subs,$object);
	}

};

class template {
        // compatibility muutujad
                var $template_dir; // millisest kataloomast templatesid loetakse (string)
                var $tplfile;      // template faili sisu (arr)
                var $templates;    // erinevate templatede sisu salvestatakse siia (arr)
                var $variables;    // siia paigutame imporditud muutujad
                var $ignored;      // siia paigutame muutujad, mille sisu asendatakse tühjusega
                var $expandsubs;   // kas <!-- SUB: blaa muutub {VAR:blaa}-ks?
		var $template_dir; // millisest kataloomast templatesid loetakse (string)

	// compatibility funktsioonid
	function tpl_init($basedir = "",$expandsubs = 1) {
                # @desc: konstruktor
                # @desc: selle peab välja kutsuma, enne kui templatesid
                # @desc: kasutada saab
                # kui basedir-il on väärtus, siis read_template otsib
                # templatet $tpldir/basedir kataloomast
                $this->set_root($basedir);
                $this->expandsubs = $expandsubs;
                $this->ignored = array();
                extract($GLOBALS);
		$this->vars(array("self" => $PHP_SELF,
			          "ext"  => $ext,
				  "baseurl" => $baseurl));
        }

	// sets the root directory to read templates from
        function set_root($path) {
                global $tpldir;
                $this->template_dir = $tpldir . "/$path";
        }
	
	function reset() {
                unset($this->tplfile);
                unset($this->templates);
                unset($this->vars);
	}

	function ignore($array) {
		// see funktsioon ei tee mitte midagi ja on siin ainult backwards
		// compatiblity jaoks
        }

	 // ma ei osanud seda mujale panna ;)
        // see on <SELECT> elementide jaoks ...
        // ette antakse array, millest produtseeritakse string, kus iga element on kujul
        // <option value=$key>$value</option>
        // ja see element, mille key on muutujas $active saab ka "selected" tagi
        function option_list($active,$array) {
                $res = "";
                if (is_array($array)) {
                while(list($k,$v) = each($array)) {
                        $selected = ($active == $k) ? " selected " : "";
                        $res .= sprintf("<option %s value='%s'>%s</option>\n",$selected,$k,$v);
                };
                };
                return $res;
        }

        // multiple <select> elementide jaox, $active on array aktiivsete asjadega
        function multiple_option_list($active,$array) {
                $res = "";
                while(list($k,$v) = each($array)) {
                        $selected = $active[$k] ? " selected " : "";
                        $res .= sprintf("<option %s value='%s'>%s</option>\n",$selected,$k,$v);
                };
                return $res;
        }

	// shortcut eelmisele
        function picker($active,$array) {
                return $this->option_list($active,$array);
        }
	
	// ja siit algavad siis no oiged funktsioonid
	function template() {
		global $PHP_SELF;
		$this->vars = array("self" => $PHP_SELF);
	}

	function read_template($filename) {
		// loeme faili sisse
		$filename = $this->template_dir . "/$filename";
		$fp = fopen($filename,"r");
		$this->tp = fread($fp,filesize($filename));
		fclose($fp);

		// paigutame arraysse 
		$tlines = explode("\n",$this->tp);

		// this is what we call a construct, we can load everything
		// we need here - variables, arrays, objects;
		$construct = array();

		// paigutame arraysse root elemendi, default nimega MAIN
		$tpl = new tpl("MAIN");
		array_push($construct,$tpl);
		
		// tsükkel üle faili ridade
		while(list($linenum,$line) = each($tlines)) {
			// kas see rida alustab subtemplatet?
			if (preg_match("/<!-- SUB: (.*) -->/",$line,$m)) {
				// jep, loome uue objekti selle nimega
				$tpl = new tpl($m[1]);
				// votame constructist aktiivse template
				$last = array_pop($construct);
		
				// compatibility jauks
				$this->templates[$last->name] .= $line;

				// ja lisame sinna sub-i asemele var-i	
				$last->sink("{VAR:$m[1]}\n");
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
			} elseif (preg_match("/<!-- END SUB: (.*) -->/",$line,$m)) {
				// unloadime viimase objekti constructist
				$last = array_pop($construct);
				if ($last->name != $m[1]) {
					printf("Broken template. Tried to close '%s' while '%s' was open",$m[1],$last->name);
					die;
				};
			} else {
				// votame constructist aktiivse template
				$last = array_pop($construct);
		
				// compatibility jauks
				$this->templates[$last->name] .= $line;

				// ja lisame sinna töödeldava rea 	
				$last->sink($line);

				// viga on siin, selles vahemikus
				// vaatame, kas constructis on veel midagi,
				$last1 = array_pop($construct);
				
				// kui on, siis 
				if (is_object($last1)) {
					$kala = array_pop($last1->subs);
					// votame sealsest sub-ide arrayst viimase
					// elemendi
					if ($kala) {
						array_push($last1->subs,$last);
						array_push($construct,$last1);
					};
				};
				array_push($construct,$last);
			};
		};
		$last = array_pop($construct);
		$this->construct = $last;
		return $last;
	}
	
	function get_tpl_by_name($name,$c = array()) {
		$this->result = "";
		return $this->_get_tpl_by_name($name,$c);
	}

	function _get_tpl_by_name($name,$c = array()) {
	// see on rekusiivne funktsioon
		$obj = array();
		reset($c);
		while(list($k,$v) = each($c)) {
			if (is_object($v)) {
				if ($v->name == $name) {
					$this->result = $v;
					return $v;
				} elseif ((sizeof($v->subs) > 0) && (!$this->result)) {
					$this->get_tpl_by_name($name,$v->subs);
				};
			};
		};
		if ($this->result) {
			return $this->result;
		} else {
			return false;
		};
	}

	// kirjutab muutujate väärtused üle
	function vars($params) {
		$this->vars = array_merge($this->vars,$params);
	}

	// mergib muutujate väärtused
	function vars_merge($params) {
		while(list($k,$v) = each($params)) {
			$this->vars[$k] .= $v;
		};
	}

	// impordime andmestruktuuri mingi template juurde
	function define_data($tpl,$branches) {
		if (!is_array($branches)) {
			return false;
		} else {
			$this->branches[$tpl] = $branches;
		};
	}

	// parsib
	function parse($object) {
		$src = $object->source;
		reset($this->vars);
		while(list($k,$v) = each($this->vars)) {
			while(preg_match("/{VAR:(.+?)}/",$src,$m)) {
				$src = preg_replace("/{VAR:$m[1]}/",$this->vars[$m[1]],$src);
			};
		};
		#$this->vars[$object->name] .= $src;
		return $src;
	}

	// joonistab sektsiooni
	function draw_section($params) {
		$section_id = $params[section_id];  // millist sektsiooni joonistame
		$parent     = $params[parent];	    // millisest sektsioonist joonistamist alustame
		$use_tpl    = $params[use_tpl];	    // millist templatet selleks kasutame (obj)
		$main_tpl   = $params[main_tpl];    // millisest templatest joonistamist alustame (obj)

		if ((!is_object($use_tpl)) || (!is_object($main_tpl))) {
			print "unknown template";
			die;
		};

		$current = $this->branches[$main_tpl->name][$section_id];
		if (!(is_array($current) && sizeof($current) > 0) ) {
			return;
		};
		reset($current);
		while(list($k,$v) = each($current)) {
			$this->vars($v);
			$this->vars_merge(array($main_tpl->name => $this->parse($use_tpl)));
			if (sizeof($use_tpl->subs) > 0) {
				$new = $use_tpl->subs[0]->name;
				$newtpl = $this->get_tpl_by_name($new,array("0" => $this->construct));
				$this->draw_section(array("section_id" => $v[oid],
						          "parent"     => $parent,
							  "use_tpl"    => $newtpl,
							  "main_tpl"   => $main_tpl));
			};
		};
	}

			
};
// seda funktsiooni ei ole voimalik lyhidalt seletada
function split_array($field,$array) {
	// parameetritena antakse sisse andmed tyypi "array of arrays"
	// ning välja nimi (field) selles teise taseme arrays

	// tulemuseks on samuti andmed tyypi "array of arrays", kuid seekord
	// on array indeksiteks erinevad $field-i väärtused
	$result = array();
	while(list($k,$v) = each($array)) {
		if (is_array($result[$v[$field]])) {
			$result[$v[$field]][] = $v;
		} else {
			$result[$v[$field]] = array($v);
		};
	};
	return $result;
};
$objects = array(
	array("oid"  	=> 1,
	      "name" 	=> "esimene",
	      "parent" 	=> 0),
	      
	array("oid"  	=> 2,
	      "name" 	=> "teine",
	      "parent" 	=> 1),
	      
	array("oid"  	=> 3,
	      "name" 	=> "kolmas",
	      "parent" 	=> 1),
	      
	array("oid"  	=> 4,
	      "name" 	=> "neljas",
	      "parent" 	=> 0),
	      
	array("oid"  	=> 5,
	      "name" 	=> "viies",
	      "parent" 	=> 0),
	      
	array("oid"  	=> 6,
	      "name" 	=> "kuues",
	      "parent" 	=> 5),

	array("oid"  	=> 7,
	      "name" 	=> "seitsmes",
	      "parent" 	=> 5),

	array("oid"  	=> 8,
	      "name" 	=> "kaheksas",
	      "parent" 	=> 5),
);
$filename = "/www/tpl/test.tpl";
$tpl = new template();
$submenu = array(array(
		array("name" => "File"),
		array("name" => "Edit"),
		array("name" => "View"),
		array("name" => "Help")
		));
$submenu2 = array(array(
		array("name" => "Hvail"),
		array("name" => "Jeedit"),
		array("name" => "Vjuuv"),
		array("name" => "Hjälp")
		));

$stories = array(array(
		array("title" => "esimene stoori",
		      "content" => "asda asdad asdsad sadsa dsad sadsa dsad sadsa dsa
		      sadsad dsa sdsad sadsa dsa dsa dsad sad sada sdsad sad sadsa sadsa
		      sad asdasd sadsa dsad sa dsa dsa d sad sad sa dsa d sad sad sadsad
		      sad sad sad sad sad sad sad sad sa dsa d sad sad sadsa dsa dsad asd
		      sad sad sad sad sa dsa d sad"),
		array("title" => "teine stoori",
		      "content" => "bfdbfdbf bfdb fb fdb fdb fbfd b fdbfd bfd b fdb fdb fdb
		      fdb fdbfdb fdb fdb fdb fdb fdb fdb fd bfd b fdb fdb fdb fd bfd b fdb fd
		      fdbfb fdb fdb fdb fd bfd bfd bfd b fdb fd bfd b fdb fdb fdb fdb fd bfd
		      fdb fdb fdb fdb fdb fd b fdb fdb fd b fd b"),
		array("title" => "kolmas stoori",
		      "content" => "iuoui iouioui ioui ouio uioui oui ouio uioiu oui o uiouio ui
		      uiouio uioui ouio iuoui ouio uio uioui uioui oui ouio uio ui oui o uio ui
		      uiouio uio uioui io uou uiou ioiu uio uio uio uio uio uio uio uio uio u
		       uiouiouio uio uio uio uio uio uio uiuo")
		 ));
$t = $tpl->read_template($filename);

if (!$usetpl) {
	$usetpl = "level1b";
};

if (!$storytpl) {
	$storytpl = "paremstory";
};

$mainmenu = $tpl->get_tpl_by_name($usetpl,array("0" => $t));

// impordime data

$tpl->define_data($mainmenu->name,split_array("parent",$objects));

if (!$mainmenu) {
	print "No such template - $usetpl<br>";
} else {
	$tpl->draw_section(array("section_id" => 0,
				 "parent"     => 0,
				 "main_tpl"   => $mainmenu,
				 "use_tpl"    => $mainmenu));
};
if (!$lang) {
	$lang = "submenu";
};
$topmenu = $tpl->get_tpl_by_name("submenu",array("0" => $t));
	
$tpl->define_data($topmenu->name,$$lang);
$tpl->draw_section(array("section_id" => 0,
			 "parent"     => 0,
			 "main_tpl"   => $topmenu,
			 "use_tpl"    => $topmenu));
$st = $tpl->get_tpl_by_name($storytpl,array("0" => $t));
$tpl->define_data($st->name,$stories);
$tpl->draw_section(array("section_id" => 0,
			 "parent"     => 0,
			 "main_tpl"   => $st,
			 "use_tpl"    => $st));
$main = $tpl->get_tpl_by_name("MAIN",array("0" => $t));
$tpl->vars(array("usetpl" => $tpl->picker($usetpl,array("level1b" => "Tavaline",
						        "fancy"   => "Fancy")),
	         "storytpl" =>     $tpl->picker($storytpl,array("paremstory" => "stiil 1",
			                                  "paremstory2" => "stiil 2",
							  "paremstory3" => "stiil 3")),
		 "lang"   => $tpl->picker($lang,array("submenu" => "Inglise",
		 				      "submenu2" => "Jeesti"))));
print $tpl->parse($main);
?>
