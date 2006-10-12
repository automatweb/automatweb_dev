<?php
// $Header: /home/cvs/automatweb_dev/classes/core/util/image_verification/image_verification.aw,v 1.5 2006/10/12 14:52:08 dragut Exp $
// image_verification.aw - Kontrollpilt 
/*

@classinfo syslog_type=ST_IMAGE_VERIFICATION relationmgr=yes no_comment=1 no_status=1 prop_cb=1
@tableinfo image_verification index=oid master_table=objects master_index=oid

@default table=objects
@default group=general

@property width type=textbox size=5 table=image_verification form=+emb
@caption Laius

@property height type=textbox size=5 table=image_verification form=+emb
@caption K&otilde;rgus

@property text_color type=colorpicker table=image_verification form=+emb
@caption Teksti v&auml;rv

@property background_color type=colorpicker table=image_verification form=+emb
@caption Tausta v&auml;rv

@property font_size type=textbox size=5 table=image_verification form=+emb
@caption Kirja suurus

@property image_preview type=text store=no form=+emb
@caption Eelvaade

*/

class image_verification extends class_base
{
	function image_verification()
	{
		$this->init(array(
			"tpldir" => "core/util/image_verification",
			"clid" => CL_IMAGE_VERIFICATION
		));
	}

	function get_property($arr)
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'width':
				if ( empty($prop['value']) )
				{
					$prop['value'] = 250;
				}
				break;
			case 'height':
				if ( empty($prop['value']) )
				{
					$prop['value'] = 60;
				}
				break;
			case 'text_color':
				if ( empty($prop['value']) )
				{
					$prop['value'] = '000000';
				}
				break;
			case 'background_color':
				if ( empty($prop['value']) )
				{
					$prop['value'] = 'FFFFFF';
				}
				break;
			case 'font_size':
				if ( empty($prop['value']) )
				{
					$prop['value'] = '10';
				}
				break;
			case 'image_preview':
				if ($arr['new'] != 1)
				{
					$prop['value'] = html::img(array(
						'url' => aw_ini_get('baseurl').'/'.$arr['obj_inst']->id(),
						'width' => $arr['obj_inst']->prop('width'),
						'height' => $arr['obj_inst']->prop('height'),
					));
				} 
		};
		return $retval;
	}

	function set_property($arr = array())
	{
		$prop = &$arr["prop"];
		$retval = PROP_OK;
		switch($prop["name"])
		{
			case 'text_color':
			case 'background_color':
				$prop['value'] = str_replace('#', '', $prop['value']);
				break;
		}
		return $retval;
	}	

	function callback_mod_reforb($arr)
	{
		$arr["post_ru"] = post_ru();
	}

	////////////////////////////////////
	// the next functions are optional - delete them if not needed
	////////////////////////////////////

	function request_execute($o)
	{
		$this->draw_image(array(
			'obj_inst' => $o
		));
	}

	/** this will get called whenever this object needs to get shown in the website, via alias in document **/
	function show($arr)
	{
		$ob = new object($arr["id"]);
		$this->read_template("show.tpl");
		$this->vars(array(
			"name" => $ob->prop("name"),
		));
		return $this->parse();
	}

	function draw_image($arr)
	{
		$im_width = $arr['obj_inst']->prop('width');
		$im_height = $arr['obj_inst']->prop('height');

		$im = imagecreatetruecolor($im_width, $im_height);

		$bg_color = $this->convert_color( $arr['obj_inst']->prop('background_color') );
		$bg_color = imagecolorallocate($im, $bg_color['red'], $bg_color['green'], $bg_color['blue']);
		imagefill($im, 0, 0, $bg_color);

		$text_color = $this->convert_color( $arr['obj_inst']->prop('text_color') );
		$text_color = imagecolorallocate($im, $text_color['red'], $text_color['green'], $text_color['blue']);

		putenv('GDFONTPATH=' . aw_ini_get('basedir').'/classes/core/util/image_verification');
		$font_file = 'Vera.ttf';

		$font_size = $arr['obj_inst']->prop('font_size');
		$angle = 0;

		$codes = array(
			'left' => array('str' => t('Sisesta vasakpoolsed neli numbrit'), 'code' => rand(1000, 9999)),
			'right' => array('str' => t('Sisesta parempoolsed neli numbrit'), 'code' => rand(1000, 9999)),
		);

		$random_key = array_rand($codes);
		$question_str = $codes[$random_key]['str'];
		$code = $codes[$random_key]['code'];

	// for debug:
	//	$codes[$random_key]['code'] = '_'.$codes[$random_key]['code'].'_';

		/**
			Ok, lets put this additional string thingie here
			Maybe i should implement several versions of which 
			picture is shown and how the code will be generated
		**/

		$numbers = array(
			0 => t('null'),
			1 => t('üks'),
			2 => t('kaks'),
			3 => t('kolm'),
			4 => t('neli'),
			5 => t('viis'),
			6 => t('kuus'),
			7 => t('seitse'),
			8 => t('kaheksa'),
			9 => t('üheksa'),
		);

		$random_nr = array_rand($numbers);
		$adds = array(
			'start' => sprintf(t('Lisa algusesse %s'), $numbers[$random_nr]),
			'end' => sprintf(t('Lisa lõppu %s'), $numbers[$random_nr]),
		);

		$random_add = array_rand($adds);
		if ($random_add == 'start')
		{
			$code = (string)$random_nr.(string)$code;
		}
		else
		{
			$code = (string)$code.(string)$random_nr;
		}

		$add_str = $adds[$random_add];
		

		/****/

		$code_str = $codes['left']['code'].$codes['right']['code'];
		
		$line_height = $im_height / 4;

		$text_box = imagettfbbox($font_size, $angle, $font_file, $question_str);
		$start_x = ($im_width / 2) - (abs($text_box[4] - $text_box[6]) / 2);
		$start_y = $line_height + ($line_height / 4);

		imagettftext($im, $font_size, $angle, $start_x, $start_y, $text_color, $font_file, $question_str);


		// additional question:
		$text_box = imagettfbbox($font_size, $angle, $font_file, $add_str);
		$start_x = ($im_width / 2) - (abs($text_box[4] - $text_box[6]) / 2);
		$start_y = (2 * $line_height) + ($line_height / 4);

		imagettftext($im, $font_size, $angle, $start_x, $start_y, $text_color, $font_file, $add_str);

		$text_box = imagettfbbox($font_size, $angle, $font_file, $code_str);
		$start_x = ($im_width / 2) - (abs($text_box[4] - $text_box[6]) / 2);
		$start_y = (3 * $line_height) + ($line_height / 4);

		imagettftext($im, $font_size, $angle, $start_x, $start_y, $text_color, $font_file, $code_str);

		// register the code in session:
		$_SESSION['verification_code'] = $code;

		// output the image
		header('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
	}

	/** Validates the code
		@attrib name=validate api=1 params=pos 

		@param code required type=string acl=view
			Code which is checked against the one which is in session.
		@returns
			true, if the code matches
			false, if the code doesn't match
		
	**/
	function validate($code)
	{
		$correct_code = $_SESSION['verification_code'];

		// XXX when the code is validated, then lets remove the code from session
		// with this it should be possible to get only one code from an image and
		// and try to validate with it - it is not possible to get the picture, parse several
		// variants and then try them all
		// maybe there should be separate method for that in the future --dragut
		unset($_SESSION['verification_code']);

		if (!empty($correct_code) && !empty($code) && $code == $correct_code)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function convert_color($color)
	{
		return array(
			'red' => hexdec( substr($color, 0, 2) ),
			'green' => hexdec( substr($color, 2, 2) ),
			'blue' => hexdec( substr($color, 4, 2) ),
		);
	}

	function do_db_upgrade($table, $field, $query, $error)
	{
		if (empty($field))
		{
			$this->db_query('CREATE TABLE '.$table.' (oid INT PRIMARY KEY NOT NULL)');
			return true;
		}

		switch ($field)
		{
			case 'width':
			case 'height':
			case 'font_size':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'int'
				));
                                return true;
			case 'text_color':
			case 'background_color':
				$this->db_add_col($table, array(
					'name' => $field,
					'type' => 'varchar(255)'
				));
				return true;
                }

		return false;
	}

}
?>
