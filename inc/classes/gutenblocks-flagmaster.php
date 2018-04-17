<?php
/**
 * GutenBlocks FlagMaster is a version of FLAG MASTER edited for this plugin needs.
 *
 * Original class can be Found on Peter's GitHub repo.
 * {@link https://github.com/peterkahl/country-code-to-emoji-flag/blob/master/src/flagMaster.php}}
 *
 * @version    0.9 (2017-04-28 01:31:00 GMT)
 * @author     Peter Kahl <peter.kahl@colossalmind.com>
 * @since      2017-01-05
 * @license    Apache License, Version 2.0
 *
 * Copyright 2017 Peter Kahl <peter.kahl@colossalmind.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class GutenBlocks_flagMaster {

	const VERSION = '0.9';

	#===================================================================

	private static function enclosedUnicode($char) {
		$arr = array(
			'a' => '1F1E6',
			'b' => '1F1E7',
			'c' => '1F1E8',
			'd' => '1F1E9',
			'e' => '1F1EA',
			'f' => '1F1EB',
			'g' => '1F1EC',
			'h' => '1F1ED',
			'i' => '1F1EE',
			'j' => '1F1EF',
			'k' => '1F1F0',
			'l' => '1F1F1',
			'm' => '1F1F2',
			'n' => '1F1F3',
			'o' => '1F1F4',
			'p' => '1F1F5',
			'q' => '1F1F6',
			'r' => '1F1F7',
			's' => '1F1F8',
			't' => '1F1F9',
			'u' => '1F1FA',
			'v' => '1F1FB',
			'w' => '1F1FC',
			'x' => '1F1FD',
			'y' => '1F1FE',
			'z' => '1F1FF',
		);
		$char = strtolower($char);
		if (array_key_exists($char, $arr)) {
			return mb_convert_encoding('&#x'.$arr[$char].';', 'UTF-8', 'HTML-ENTITIES');
		}
		throw new Exception('Invalid character '.$char);
	}

	#===================================================================

	/**
	 * Converts country code to emoji flag.
	 * @var string (2-letter code)
	 *
	 */
	private static function code2unicode($code) {
		$arr = str_split($code);
		$str = '';
		foreach ($arr as $char) {
			$str .= self::enclosedUnicode($char);
		}
		return $str;
	}

	#===================================================================

	/**
	 * Converts string of country codes to string of emoji flags.
	 * Makes correction for codes that have no corresponding flag.
	 * @var string (one or more 2-letter codes)
	 *
	 */
	public static function emojiFlag($code) {
		$code = strtolower($code);
		if (substr($code, 0, 1) == '_') {
			# Certain flags don't exist or countries are occupied or not widely recognised.
			$flag = array(
				'_tibet'           => '🇨🇳',
				'_basque-country'  => '🇪🇸',
				'_northern-cyprus' => '🇨🇾',
				'_south-ossetia'   => '🇷🇺',
				'_scotland'        => '🇬🇧',
				'_wales'           => '🇬🇧',
				'_england'         => '🇬🇧',
				'_commonwealth'    => '🇬🇧',
				'_british-antarctic-territory' => '🇬🇧',
			);
			if (array_key_exists($code, $flag)) {
				return $flag[$code];
			}
			return '🏴';
		}
		elseif ($code == 'unknown') {
			return '🏴';
		}
		$map = array(
			'uk' => 'gb',
			'an' => 'nl',
		);
		# break into pairs
		$arr = array();
		$str = '';
		while (strlen($code) > 0) {
			$arr[] = substr($code, 0, 2);
			$code  = substr($code, 2);
		}
		foreach ($arr as $k => $val) {
			if (array_key_exists($val, $map)) {
				$arr[$k] = $map[$val];
				$val = $map[$val];
			}
			if ($val == 'ap' || $val == 'un') {
				$str .= '🏴'; # black flag
			}
			else {
				$str .= self::code2unicode($val);
			}
		}
		return $str;
	}

	#===================================================================

}
