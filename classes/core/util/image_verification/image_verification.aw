<?php
// $Header: /home/cvs/automatweb_dev/classes/core/util/image_verification/image_verification.aw,v 1.2 2006/10/03 09:26:41 dragut Exp $
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
					$prop['value'] = 100;
				}
				break;
			case 'height':
				if ( empty($prop['value']) )
				{
					$prop['value'] = 50;
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
			'left' => array('str' => t('Sisesta vasakpoolsed 4 numbrit:'), 'code' => rand(1000, 9999)),
			'center' => array('str' => t('Sisesta keskmised 4 numbrit:'), 'code' => rand(1000, 9999)),
			'right' => array('str' => t('Sisesta parempoolsed 4 numbrit:'), 'code' => rand(1000, 9999)),
		);

		$random_key = array_rand($codes);
		$question_str = $codes[$random_key]['str'];
		$code = $codes[$random_key]['code'];

	// for debug:
	//	$codes[$random_key]['code'] = '_'.$codes[$random_key]['code'].'_';

		$code_str = $codes['left']['code'].'   '.$codes['center']['code'].'   '.$codes['right']['code'];
		
		$start_x = 10;
		$start_y = $im_height / 3; 
		
		imagettftext($im, $font_size, $angle, $start_x, $start_y, $text_color, $font_file, $question_str);

		$start_y = (2 * ($im_height / 3)) + (($im_height / 3) / 2); 

		imagettftext($im, $font_size, $angle, $start_x, $start_y, $text_color, $font_file, $code_str);

		// use of non-ttf fonts 
	//	$font = 5;
	//	$x = (int)(($im_width - (imagefontwidth($font) * strlen($code))) / 2);
	//	$y = (int)(($im_height - imagefontheight($font)) / 2);

	//	imagestring($im, $font, $x, $y, $code, $text_color);
	/*
		// draw lines:
		for ($i=0; $i < rand(4,5); $i++) {
			$linecolor = ImageColorAllocate($im, rand(111,255), rand(111,255), rand(111,255));
			//$x1, $y1, $x2, $y2
			imageline($im, rand(1,33), rand(1,33), rand(33,88), rand(1,44), $linecolor);
		}
	*/
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
		if ($code == $_SESSION['verification_code'])
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
