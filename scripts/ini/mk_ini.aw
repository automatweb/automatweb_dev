<?php
// what this script does, is take the starting file from the command line, reads that file
// and replaces all include commands in that file with the contents of the file that is included

$stderr = fopen('php://stderr', 'w');

function aw_ini_get($var)
{
	if (($pos = strpos($var,".")) !== false)
	{
		$class = substr($var,0,$pos);
		$var = substr($var,$pos+1);
	}
	else
	{
		$class = "__default";
	}
	return $GLOBALS["cfg"][$class][$var];
}

function parse_config($file)
{
	// put result lines in here
	$res = array();

	$linenum = 0;
	$fd = file($file);
	foreach($fd as $line)
	{
		$linenum++;
		$oline = $line;
		$add = true;
		// ok, parse line
		// 1st, strip comments
		if (($pos = strpos($line,"#")) !== false)
		{
			$line = substr($line,0,$pos);
		}
		// now, strip all whitespace
		$line = trim($line);

		if ($line != "")
		{
			if (substr($line, 0, strlen("include")) == "include")
			{
				// process include 
				$line = preg_replace('/\$\{(.*)\}/e',"aw_ini_get(\"\\1\")",$line);
				$ifile = trim(substr($line, strlen("include")));
				if (!file_exists($ifile) || !is_readable($ifile))
				{
					fwrite($GLOBALS["stderr"], "Failed to open include file on line $linenum in file $file \n");
					return false;
				}
				$in = parse_config($ifile);
				if ($in === false)
				{
					fwrite($GLOBALS["stderr"], "\tthat was included from line $linenum in file $file \n");
					return false;
				}
				else
				{
					foreach($in as $iline)
					{
						$res[] = $iline;
					}
					$add = false;
				}
			}
			else
			{
				// now, config opts are class.variable = value
				$eqpos = strpos($line," = ");
				if ($eqpos !== false)
				{
					$var = trim(substr($line,0,$eqpos));
					$varvalue = trim(substr($line,$eqpos+3));
					
					// now, replace all variables in varvalue
					$varvalue = preg_replace('/\$\{(.*)\}/e',"aw_ini_get(\"\\1\")",$varvalue);
					$var = preg_replace('/\$\{(.*)\}/e',"aw_ini_get(\"\\1\")",$var);

					// if the varname contains . split it into class and variable parts
					// if not, class will be __default
					if (($dotpos = strpos($var,".")) !== false)
					{
						$varclass = substr($var,0,$dotpos);
						$varname = substr($var,$dotpos+1);
					}
					else
					{
						$varclass = "__default";
						$varname = $var;
					}

					// check if variable is an array 
					if (($bpos = strpos($varname,"[")) !== false)
					{
						// ok, do the bad eval version
						$arrparams = substr($varname,$bpos);
						$arrname = substr($varname,0,$bpos);
						if (!is_array($GLOBALS["cfg"][$varclass][$arrname]))
						{
							$GLOBALS["cfg"][$varclass][$arrname] = array();
						}
						$code = "\$GLOBALS[cfg][\$varclass][\$arrname]".$arrparams." = \"".$varvalue."\";";
	//					echo "evaling $code <br>";
						eval($code);
					}
					else
					{
						// and stuff the thing in the array
						$GLOBALS["cfg"][$varclass][$varname] = $varvalue;
						//echo "setting [$varclass][$varname] to $varvalue <br>";
					}
				}
			}
		}
		if ($add)
		{
			$res[] = $oline;
		}
	}
	return $res;
}


if ($_SERVER["argc"] < 1 || !file_exists($_SERVER["argv"][1]))
{
	echo "usage: php -q mk_ini.aw aw.ini.root \n\n";
	echo "\toutputs the ini file with the include directives replaced with the file contents\n\n";
	exit(1);
}

$res = parse_config($_SERVER["argv"][1]);
if ($res === false)
{
	exit(1);
}
else
{
	echo "######################################################################\n";
	echo "# THIS IS AN AUTOMATICALLY GENERATED FILE!!!                         #\n";
	echo "# DO NOT EDIT THIS!!                                                 #\n";
	echo "#                                                                    #\n";     
	echo "# Instead, edit aw.ini.root and/or the files included from it.       #\n";
	echo "# after editing, to regenerate this file execute cd \$AWROOT;make ini #\n";
	echo "######################################################################\n\n\n";
	echo join("", $res);
}
?>