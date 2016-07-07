<?php
	/**
		EXIF parsing tools.
	*/
	
	/**
		Takes exif data straight out of exif_read_data(<path>), and converts
		to string key pairs.
	*/
	function exif_reformat($data) {
		//change case of the keys for matching reasons.
		$data = array_change_key_case($data, CASE_LOWER);
		foreach ($data as $key => &$value) {
			if (is_array($value)) {
				$value = array_change_key_case($value, CASE_LOWER);
				switch($key) {
					// GPS values
					case 'gps_latitude':
					case 'gps_longitude':
					case 'gpslatitude':
					case 'gpslongitude':
					$value = exif_reformat_DMS2D($value, $data[$key . 'ref']);
					break;
					default:
					//we flatten the array.
					$value = exif_flatten($value);
				}
			} else {
				if (is_string($value)) {
					$value = trim($value);
				}
				if (!exif_validate_utf8($value)) {
					$value = utf8_encode($value);
				}
				switch ($key) {
					// String values.
					case 'usercomment':
					if (exif_startswith($value,'UNICODE')) {
						$value = substr($value,8);
					}
					break;
					// Date values.
					case 'filedatetime':
					$value=date('c',$value);
					break;
					case 'datetimeoriginal':
					case 'datetime':
					case 'datetimedigitized':
					// Reformat date to iso
					$date_time = explode(" ", $value);
					$date_time[0] = str_replace(":", "-", $date_time[0]);
					$value = implode("T", $date_time);
					break;
					// GPS values.
					case 'gpsaltitude':
					case 'gpsimgdirection':
					if (!isset($data[$key . 'ref'])) {
						$data[$key . 'ref'] = 0;
					}
					$value = exif_reformat_DMS2D($value, $data[$key . 'ref']);
					break;
					// Flash values.
					case 'flash':
					$flash_descriptions = exif_getFlashDescriptions();
					if (isset($flash_descriptions[$value])) {
						$value = $flash_descriptions[$value];
					}
					break;
					// Exposure values.
					case 'exposuretime':
					if (strpos($value, '/') !== FALSE) {
						$value = exif_normalise_fraction($value) . 's';
					}
					break;
					// Focal Length values.
					case 'focallength':
					if (strpos($value, '/') !== FALSE) {
						$value = exif_normalise_fraction($value) . 'mm';
					}
					break;
				}
			}
		}
		return $data;
	}
	
	/**
		Helper for gps coords to dec.
	*/
	function exif_reformat_DMS2D($value, $ref) {
		if (!is_array($value)) {
			$value = array($value);
		}
		$dec = 0;
		$granularity = 0;
		foreach ($value as $element) {
			$parts = explode('/', $element);
			$dec += (float) (((float) $parts[0] / (float) $parts[1]) / pow(60, $granularity));
			$granularity++;
		}
		if ($ref == 'S' || $ref == 'W') {
			$dec *= -1;
		}
		return $dec;
	}
	
	/**
		Helper.
	*/
	function exif_validate_utf8($text) {
		if (strlen($text) == 0) {
			return true;
		}
		return (preg_match('/^./us', $text) == 1);
	}
	
	/**
		Helper.
	*/
	function exif_startswith($hay, $needle) {
		return substr($hay, 0, strlen($needle)) === $needle;
	}
	
	/**
		Helper.
	*/
	function exif_getFlashDescriptions() {
		return array(
		'0' => 'Flash did not fire.',
		'1' => 'Flash fired.',
		'5' => 'Strobe return light not detected.',
		'7' => 'Strobe return light detected.',
		'9' => 'Flash fired, compulsory flash mode',
		'13' => 'Flash fired, compulsory flash mode, return light not detected',
		'15' => 'Flash fired, compulsory flash mode, return light detected',
		'16' => 'Flash did not fire, compulsory flash mode',
		'24' => 'Flash did not fire, auto mode',
		'25' => 'Flash fired, auto mode',
		'29' => 'Flash fired, auto mode, return light not detected',
		'31' => 'Flash fired, auto mode, return light detected',
		'32' => 'No flash function',
		'65' => 'Flash fired, red-eye reduction mode',
		'69' => 'Flash fired, red-eye reduction mode, return light not detected',
		'71' => 'Flash fired, red-eye reduction mode, return light detected',
		'73' => 'Flash fired, compulsory flash mode, red-eye reduction mode',
		'77' => 'Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected',
		'79' => 'Flash fired, compulsory flash mode, red-eye reduction mode, return light detected',
		'89' => 'Flash fired, auto mode, red-eye reduction mode',
		'93' => 'Flash fired, auto mode, return light not detected, red-eye reduction mode',
		'95' => 'Flash fired, auto mode, return light detected, red-eye reduction mode'
		);
	}
	
	/**
		Helper.
	*/
	function exif_normalise_fraction($fraction) {
		$parts = explode('/', $fraction);
		$top = $parts[0];
		$bottom = $parts[1];
		
		if ($top > $bottom) {
			// Value > 1
			if (($top % $bottom) == 0) {
				$value = ($top / $bottom);
			}
			else {
				$value = round(($top / $bottom), 2);
			}
		} else {
			if ($top == $bottom) {
				// Value = 1
				$value = '1';
			}
			else {
				// Value < 1
				if ($top == 1) {
					$value = '1/' . $bottom;
				}
				else {
					if ($top != 0) {
						$value = '1/' . round(($bottom / $top), 0);
					}
					else {
						$value = '0';
					}
				}
			}
		}
		return $value;
	}
	
	/**
		Helper.
	*/
	function exif_flatten($value) {
		$temp = '';
		if (is_array($value)) {
			//for every value flatten it further
			foreach ($value as $key=>$val) {
				if (!empty($temp))
					$temp.=', ';
				$temp.=$key.' : '.exif_flatten($val);
			}
			$temp = '( '.$temp.' )';
		} else
			$temp.=$value;
		return $temp;
	}
?>