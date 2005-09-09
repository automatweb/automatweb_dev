<?php
// $Header: /home/cvs/automatweb_dev/classes/applications/ecard/ecard_manager.aw,v 1.1 2005/09/09 22:08:34 ekke Exp $
// ecard_manager.aw - E-kaardi haldur 
// Use this class as alias in a document. CL_ECARD is for internal use
// Make sure you attach the folders and a mini_gallery object
//
// 
/*

@classinfo syslog_type=ST_ECARD_MANAGER relationmgr=yes no_comment=1 no_status=1

@default table=objects
@default group=general
@default method=serialize

@property dir_images type=relpicker reltype=RELTYPE_DIR_IMAGES field=meta method=serialize
@caption Piltide kataloog

@property dir_ecards type=relpicker reltype=RELTYPE_DIR_ECARDS field=meta method=serialize
@caption E-kaartide salvestamise kataloog

@property gallery type=relpicker reltype=RELTYPE_GALLERY field=meta method=serialize automatic=1
@caption Minigalerii

@reltype DIR_IMAGES value=1 clid=CL_MENU
@caption Piltide kataloog

@reltype DIR_ECARDS value=2 clid=CL_MENU
@caption E-kaartide salvestamise kataloog

@reltype GALLERY value=3 clid=CL_MINI_GALLERY
@caption Minigalerii objekt 

*/

class ecard_manager extends class_base
{
	function ecard_manager()
	{
		// change this to the folder under the templates folder, where this classes templates will be, 
		// if they exist at all. Or delete it, if this class does not use templates
		$this->init(array(
			"tpldir" => "applications/ecard/ecard_manager",
			"clid" => CL_ECARD_MANAGER
		));
	}
/*
	//////
	// class_base classes usually need those, uncomment them if you want to use them
	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- get_property --//
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			//-- set_property --//

		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

*/
	////
	// !this will be called if the object is put in a document by an alias and the document is being shown
	// parameters
	//    alias - array of alias data, the important bit is $alias[target] which is the id of the object to show
	function parse_alias($arr)
	{
		return $this->show(array("id" => $arr["alias"]["target"], "doc_id" => $arr['alias']['from.parent']));
	}

	////
	// !this shows the object. not strictly necessary, but you'll probably need it, it is used by parse_alias
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->sub_merge = 1;
		$card = null;
		$this->vars(array(
			"name" => $ob->prop("name"),
		));

		$formdata = array( // Form fields etc
			'from_name'	=> array(type => 'textbox', 'caption' => t("Teie nimi"), 'optional' => false),
			'from_mail'	=> array(type => 'textbox', 'caption' => t("Teie e-post"), 'optional' => false),
			'to_name'	=> array(type => 'textbox', 'caption' => t("Saaja nimi"), 'optional' => false),
			'to_mail'	=> array(type => 'textbox', 'caption' => t("Saaja e-post"), 'optional' => false),
			'comment'	=> array(type => 'textarea', 'caption' => t("Tekst kaardil"), 'optional' => true),
			'send_date'	=> array(type => 'date_select', 'caption' => t("Millal saata"), 'year_from' => date('Y'), 'optional' => false),
			'position'	=> array(type => 'select', 'caption' => t("Paigutus"), 'options' => array ('1' => t("Tekst all"), '2' => t("Tekst kõrval")), 'optional' => false),
			'spy'	=> array(type => 'checkbox', 'value' => 1, 'caption' => t("Teata vaatamisest meiliga")),
			'submit'	=> array(type => 'submit', 'value' => t("Edasi eelvaatele"), 'caption' => ''),
			'doc_id'	=> array(type => 'hidden', 'value' => $arr["doc_id"]),
			'id'	=> array(type => 'hidden', 'value' => $arr["id"]),
			'action'	=> array(type => 'hidden', 'value' => 'form_submit'),
			'class'	=> array(type => 'hidden', 'value' => 'ecard_manager'), 
			'card'	=> array(type => 'hidden', 'value' => null, 'optional' => false), 
		);

		$id_dir = $ob->prop('dir_images');
		if (!is_oid($id_dir) || !($dir=obj($id_dir)) || $dir->class_id() != CL_MENU)
		{
			exit;
		}

		$id_dir_cards = $ob->prop('dir_ecards');
		if (!is_oid($id_dir_cards) || !($dir_cards=obj($id_dir_cards)) || $dir_cards->class_id() != CL_MENU)
		{
			exit;
		}

		$id_dir_cards = $ob->prop('dir_ecards');

		$hidelist = false;
		// show_card is id of CL_CARD object
		if ( isset($_GET['view_card']) && is_oid($_GET['view_card']) && ($c = obj($_GET['view_card'])) && $c->class_id() == CL_ECARD &&
			isset($_GET['hash']) && $_GET['hash'] == $c->prop('hash'))
		{	// VIEWING A CARD
			$hidelist = true;
			$this->sub_merge = 1;
			$img = obj($c->prop('image'));
			$file2 = basename($img->prop("file2")); // File for big image
			$this->vars(array(
				'imgurl' => $this->mk_my_orb("show", array('id'=>$img->id(), 'fastcall' => 1, 'file' => $file2 ), CL_IMAGE),
				'imgtext' => $c->prop('comment'),
				'from'	=> $c->prop('from_name'),
				'to'	=> $c->prop('to_name'),
			));
			$card_output = $this->parse('card_'.$c->prop('position')); // Different templates for different layouts

			// If needed, send notification mail to ecard sender
			if ($c->prop('spy'))
			{
				send_mail($c->prop('from_mail'), 'Kaarti vaadati', "Tere\n\nTeie ".$c->prop('to_name')."-le saadetud kaarti on Visittartu.com keskkonnas vaadatud!\n\nViljaõnne!");
				$c->set_prop('spy', 0);
				$c->save();
			}
			
			return $card_output;
		}


		$posted = $reviewed = false;
		if (isset($_POST['id']) && $_POST['id'] == $arr['id'] && $_POST['action'] == 'form_submit' && $_POST['class'] = 'ecard_manager')
		{	// INPUT VALIDATION ON FORM SUBMIT 
			$errors = 0;
			foreach ($formdata as $name => $vals)
			{
				if (isset($vals['optional']) && $vals['optional'] == false && empty($_POST[$name]))
				{
					$formdata[$name]['error'] = t("Väli peab olema täidetud");
					$errors++;
				}
				if ($vals['type'] == 'checkbox')
				{
					$formdata[$name]['checked'] = isset($_POST[$name]) ? 1 : 0;
				}
				else if ($vals['type'] != 'hidden' && $vals['type'] != 'submit')
				{
					$formdata[$name]['value'] = isset($_POST[$name]) ? str_replace("'", "&#039;", $_POST[$name]) : "";
				}	
			}	

			if (!is_email($_POST['from_mail']))
			{
				$formdata['from_mail']['error'] = t("Teie e-posti aadress vigane");
				$errors++;
			}
			if (!is_email($_POST['to_mail']))
			{
				$formdata['to_mail']['error'] = t("Saaja e-posti aadress vigane");
				$errors++;
			}
			$d = $_POST['send_date'];
			if (!is_date($d['day'].'-'.$d['month'].'-'.$d['year']) || date('Y') > $d['year'])
			{
				$formdata['send_date']['error'] = t("Kuupäev vigane");
				$errors++;
			}
			if ($errors === 0)
			{
				if (isset($_POST['reviewed']) && !isset($_POST['back']))
				{
					$reviewed = true;
				}	
				else if (!isset($_POST['back']))
				{
					$posted = true;
					$formdata['submit']['value'] = t("Saada");
					$formdata['reviewed']	= array(type => 'hidden', 'value' => '1'); 
					$formdata['back']	= array(type => 'submit', 'value' => t("Tagasi"), 'caption' => '');
					$formdata['send_date[day]'] = array(type => 'hidden', 'value' => $_POST['send_date']['day']);
					$formdata['send_date[month]'] = array(type => 'hidden', 'value' => $_POST['send_date']['month']);
					$formdata['send_date[year]'] = array(type => 'hidden', 'value' => $_POST['send_date']['year']);
					unset($formdata['send_date']);
				}	
			}
		}
	
		if (isset($_REQUEST['card']) && is_oid($_REQUEST['card']))
		{
			// SENDING CARD - PREVIEW 
			$card_output = "";
			$form_output = "";
			$tmp_sub_merge = $this->sub_merge;
			$this->sub_merge = 0;

			$card = obj($_REQUEST['card']);
			$parent = obj($card->parent());
			$gparent = $parent->parent();
			if ($card->class_id() != CL_IMAGE || ($parent->id() != $id_dir && $gparent != $id_dir))
			{
				exit;
			}

			// Create CL_ECARD object, send out e-mail
			if ($reviewed == true)
			{
				$o = new object(array(
					'class_id' => CL_ECARD,
					'parent' => $id_dir_cards,
				));
				$o->save();
				$o->set_name('Kaart ' . $o->id());
				$o->connect(array(
					'to' => $card,
					'type' => 1,
				));
				$o->set_prop('image', $card->id());
				$o->set_prop('from_name', $_POST['from_name']);
				$o->set_prop('comment', $_POST['comment']);
				$o->set_prop('from_mail', $_POST['from_mail']);
				$o->set_prop('to_name', $_POST['to_name']);
				$o->set_prop('to_mail', $_POST['to_mail']);
				$o->set_prop('senddate', $_POST['send_date']);
				$o->set_prop('position', $_POST['position']);
				$o->set_prop('hash',generate_password());
				$o->set_prop('spy', isset($_POST['spy']) ? 16 : 0);
				$o->save();


				if ($d['day'] == date('d') && $d['month'] == date('m') && $d['year'] == date('Y'))
				{ // Send mail now
					$this->send_card(array('card' => $o->id(), 'doc_id' => $arr["doc_id"]));
					$this->vars(array(
						'message' => t("Kaart saadetud"),
					));
				}
				else
				{ // Schedule for given date
					$url = $this->mk_my_orb("send_card", array('card' => $o->id(), 'id' => $ob->id, 'doc_id' => $arr["doc_id"]), CL_ECARD_MANAGER);
					$scheduler = get_instance("scheduler");
					$scheduler->add(array(
						'event' => $url,
						'time' => mktime(9, 0, 0, $d['month'], $d['day'], $d['year']),
					));
					$this->vars(array(
						'message' => t("Kaart salvestatud"),
					));
				}


			}

			$formdata['card']['value'] = $card->id();

			$file2 = basename($card->prop("file2")); // File for big image
			$this->vars(array(
				'imgurl' => $this->mk_my_orb("show", array('id'=>$card->id(), 'fastcall' => 1, 'file' => $file2 ), CL_IMAGE),
				'imgtext' => ($posted || $reviewed) && isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : "",
				'from' => $posted ? $_POST['from_name'] : "",
				'to' => $posted ? $_POST['to_name'] : "",
			));
			$position = 1; // Tekst pildi all.. 2-tekst korval
			if ($posted && isset($_POST['position']) && between($_POST['position'], 1, 2))
			{
				$position = $_POST['position'];
			}
			$card_output = $this->parse('card_'.$position); // Different templates for different layouts


			if ($reviewed == false)
			{ // SENDING CARD - FORM GENERATION 
				foreach ($formdata as $name => $vals)
				{
					$vals['name'] = $name;
					if ($posted == true && $vals['type'] != 'submit')
					{
						$vals['type'] = 'hidden';
					}
					$element = call_user_func(array('html',$vals['type']), $vals); // Create inputs
					if ($vals['type'] == 'hidden')
					{
						$form .= $element;
					}
					else
					{
						$error = "";
						if (isset($vals['error']))
						{
							$this->vars(array(
								'errormsg' => t("Viga").": ".$vals['error'],
							));
							$error = $this->parse('form_item_error');
						}
						$this->vars(array(
							'caption' => $vals['type'] == "checkbox" ? "" : $vals['caption'],
							'element' => $element,
							'error' => $error,
						));
						$form .= $this->parse('form_item');
					}
				}
				$form_output .= html::form(array(
					'action' => aw_ini_get('baseurl').'/'.$arr['doc_id'],
					'method' => "POST",
					'name' => "ecard",
					'content' => $form,
				));
			}
			$this->vars(array(
				'card' => $card_output,
				'form' => $form_output,
			));

			$this->sub_merge = $tmp_sub_merge;
			$this->parse('ecard_input');
		}
		else if (!$hidelist)
		{
			// BROWSING - DIRECTORY LIST 
			$ol = new object_list(array(
				'parent' => $id_dir,
				'class_id' => CL_MENU,
				'status' => 2,
			));
			$this->vars(array(
				'dirurl' => aw_ini_get('baseurl').'/'.$arr['doc_id'],
				'dirname' => t("Esimesed"),
			));
			$this->parse('dirlist_item');
			for ($o = $ol->begin(); !$ol->end(); $i = $ol->next())
			{
				$this->vars(array(
					'dirurl' => aw_ini_get('baseurl').'/'.$arr['doc_id'].'?card_dir='.$o->id(),
					'dirname' => $o->name(),
				));
				$this->parse('dirlist_item');
			}


			// BROWSING - IMAGES LIST
			
			// Choose correct directory
			$chosen_dir = $id_dir;
			if (isset($_GET['card_dir']) && is_oid($_GET['card_dir']) && ($d=obj($_GET['card_dir'])) && $d->class_id() == CL_MENU && $d->parent() == $id_dir)
			{
				$chosen_dir = $_GET['card_dir'];
			}
			
			$ol = new object_list(array(
				'parent' => $chosen_dir,
				'class_id' => CL_IMAGE,
				'status' => 2,
			));
			$linkbase = aw_ini_get('baseurl').'/'.$arr['doc_id']."?card=";
			for ($o = $ol->begin(); !$ol->end(); $o = $ol->next())
			{ 
				$link = $linkbase.$o->id();
				if ($o->prop('link') != $link)
				{
					$o->set_prop('link', $link);
					$o->save();
				}
			}

			$gal = obj($ob->prop('gallery')); // Uses premade mini_gallery object for listing images
			$gal->connect(array(
				'type'	=> 1,
				'to'	=> $chosen_dir,
			));
			$gal->set_prop('folder', $chosen_dir);
			$i_gal = $gal->instance();
			$gal_output = $i_gal->parse_alias(array('alias' => array('target' => $gal->id())));
		
			$this->vars(array(
				"list" => $gal_output,
			));
			$this->parse('images_list');
		}
		return $this->parse();
	}

//-- methods --//
	/**  
			
		@attrib name=form_submit params=name nologin="1" 
		@param id required type=int	
	**/
	function form_submit($arr)
	{
		if(!(isset($_POST['id']) && is_oid($_POST['id']) && ($o = obj($_POST['id'])) && $o->class_id() == CL_ECARD_MANAGER))
		{
			// bugger off
			$_POST = array();
		}
	}
	
	/**
		@attrib name=send_card params=name nologin="1" 
		@param card required type=int	
		@param doc_id required type=int	
		@param id optional type=int	

		@desc sends e-mails
	**/
	function send_card($arr)
	{
		if(!(isset($arr['card']) && is_oid($arr['card']) && ($o = obj($arr['card'])) && $o->class_id() == CL_ECARD))
		{
			exit; // No card id given
		}

		$message = "Tere %s,\n\n%s on saatnud Teile e-kaardi!\n\nKaarti näete aadressil:\n%s\n\nKaart saadeti visittartu.com keskkonnast.\n\n"; 
		$url = aw_ini_get('baseurl').'/'.$arr['doc_id'].'?view_card='.$arr['card'].'&hash='.urlencode($o->prop('hash'));	
		send_mail($o->prop('to_name').' <'.$o->prop('to_mail').'>', 
			"Teile on e-kaart!", 
			sprintf($message, $o->prop('to_name'), $o->prop('from_name'), $url), 
			"From: ".$o->prop('from_name').' <'.$o->prop('from_mail').'>');
	}

}
?>
