<?php

define ("PID_GENDER_FEMALE", 2);
define ("PID_GENDER_MALE", 3);

define ("PID_ERROR_LENGTH", 1);
define ("PID_ERROR_CHECKSUM", 2);
define ("PID_ERROR_INVALID_DATE", 3);
define ("PID_ERROR_INVALID_COUNTRY", 4);

/*

Class for working with personal identification numbers (PID-s). Loading and common usage:

	$pid = get_instance ("common/pid");
	$pid->country_code ("ee");
	$pid->set (45503034582);
	$gender = $pid->gender ();

*/

class pid extends core
{
	var $pid;
	var $gender;
	var $birth_date;
	var $country;
	var $country_code;
	var $errors = array ();
	var $error_data = array ();
	var $is_valid;

	function pid ($arg = array ())
	{
		$this->init();
		$this->error_data = array (
			PID_ERROR_CHECKSUM => t("Isikukood ei vasta Eesti Vabariigi isikukoodi standardile."),
			PID_ERROR_INVALID_DATE => t("Isikukoodis leiduv sünnikuupäevateave ei vasta ühelegi kuupäevale Gregoriuse kalendris."),
			PID_ERROR_LENGTH => t("Isikukood vale pikkusega."),
			PID_ERROR_INVALID_COUNTRY => t("Riik sobimatus formaadis."),
		);
	}

/*
    @attrib name=errors
	@returns array (int error_code => string error_description) Errors that occurred during last method call. FALSE if none.
*/
	function errors ()
	{
		if (count ($this->errors))
		{
			$errors = array ();

			foreach ($this->errors as $err_id)
			{
				$errors[$err_id] = $this->error_data[$err_id];
			}

			return $errors;
		}
		else
		{
			return FALSE;
		}
	}

/*
    @attrib name=get
	@returns PID data. Void if not defined.
*/
	function get ()
	{
		unset ($this->errors);
		return $this->pid;
	}

/*
    @attrib name=set
	@param pid required type=var PID data
*/
	function set ($pid)
	{
		unset ($this->errors);
		$this->pid = $pid;
		unset ($this->is_valid);
	}

/*
    @attrib name=gender
	@returns PID_GENDER_FEMALE for female PID_GENDER_MALE for male, if applicable in this country. Void if not defined. FALSE on error.
*/
	function gender ()
	{
		unset ($this->errors);

		if ($this->is_valid and isset ($this->gender))
		{
			return $this->gender;
		}
		else
		{
			$this->_parse ();

			if ($this->is_valid)
			{
				return isset ($this->gender) ? $this->gender : NULL;
			}
			else
			{
				return FALSE;
			}
		}
	}

/*
    @attrib name=birth_date
	@returns UNIX timestamp birth date corresponding to PID if applicable in this country. Void if not defined. FALSE on error.
*/
	function birth_date ()
	{
		unset ($this->errors);

		if ($this->is_valid and isset ($this->birth_date))
		{
			return $this->birth_date;
		}
		else
		{
			$this->_parse ();

			if ($this->is_valid)
			{
				return isset ($this->birth_date) ? $this->birth_date : NULL;
			}
			else
			{
				return FALSE;
			}
		}
	}

/*
    @attrib name=country
	@param country optional type=object,aw_oid Set new country for this PID
	@returns boolean success if $country specified, currently defined country othewise (void if not defined).
*/
	function country ($country = NULL)
	{
		unset ($this->errors);

		if (isset ($country))
		{
			if (is_object ($country))
			{
				$this->country = $country;
			}
			elseif ($this->can ("view", $country))
			{
				$this->country = obj ($country);
			}
			else
			{
				$this->errors[] = PID_ERROR_INVALID_COUNTRY;
				return FALSE;
			}

			$this->country_code = $this->country->prop ("code");
			unset ($this->is_valid);
			return TRUE;
		}
		else
		{
			return $this->country;
		}
	}

/*
    @attrib name=country_code
	@param code optional type=string Set new country code (ISO 3166-1 two letter) for this PID
	@returns boolean success if $code specified, currently defined country code othewise (void if not defined).
*/
	function country_code ($code = NULL)
	{
		unset ($this->errors);

		if (isset ($code))
		{
			$this->country_code = (string) $code;
			unset ($this->is_valid);
		}
		else
		{
			return $this->country_code;
		}
	}

/*
    @attrib name=is_valid
	@returns TRUE if currently defined PID data corresponds to PID standart of specified country, FALSE othewise.
*/
	function is_valid ()
	{
		unset ($this->errors);

		return (isset ($this->is_valid) ? $this->is_valid : $this->_parse ());
	}

/* Private methods */

	function _parse ()
	{
		$parse_method = "_parse_" . strtolower ($this->country_code);
		$this->is_valid = $this->$parse_method ();
		return $this->is_valid;
	}

	## returns TRUE if pid complies to Estonian personal identification number standard EVS 1990:585.
	function _parse_ee ()
	{
		$pid = $this->pid;
		settype ($pid, "string");

		if (strlen ($pid) != 11)
		{
			$this->errors = PID_ERROR_LENGTH;
		}

		$quotient = 10;
		$step = 0;
		$check = FALSE;

		while (10 == $quotient and $step < 3 and !$check)
		{
			$order = 0;
			$multiplier = 1 + $step;
			$sum = NULL;

			while ($order < 10)
			{
				$sum += (int) $pid{$order} * $multiplier;
				$order++;
				$multiplier++;

				if (10 == $multiplier)
				{
					$multiplier = 1;
				}
			}

			$step += 2;
			$quotient = $sum%11;

			if ($quotient == (int) $pid{10})
			{
				$check = TRUE;
			}
		}

		if (!$check)
		{
			$this->errors[] = PID_ERROR_CHECKSUM;
		}

		$pid_1 = (int) substr ($pid, 0, 1);
		$pid_day = (int) substr ($pid, 5, 2);
		$pid_month = (int) substr ($pid, 3, 2);
		$pid_year = (int) substr ($pid, 1, 2);

		switch ($pid_1)
		{
			case 1: // 1800–1899  mees;
				$pid_year += 1800;
				$this->gender = PID_GENDER_MALE;
				break;

			case 2: // 1800–1899  naine;
				$pid_year += 1800;
				$this->gender = PID_GENDER_FEMALE;
				break;

			case 3: // 1900–1999  mees;
				$pid_year += 1900;
				$this->gender = PID_GENDER_MALE;
				break;

			case 4: // 1900–1999  naine;
				$pid_year += 1900;
				$this->gender = PID_GENDER_FEMALE;
				break;

			case 5: // 2000–2099  mees;
				$pid_year += 2000;
				$this->gender = PID_GENDER_MALE;
				break;

			case 6: // 2000–2099  naine;
				$pid_year += 2000;
				$this->gender = PID_GENDER_FEMALE;
				break;
		}

		if (checkdate ($pid_month, $pid_day, $pid_year))
		{
			$this->birth_date = mktime (0, 0, 0, $pid_month, $pid_day, $pid_year);
		}
		else
		{
			$this->errors[] = PID_ERROR_INVALID_DATE;
		}

		return (count ($this->errors) ? FALSE : TRUE);
	}

/* END Private methods */
}

?>
