<?php

/*
 *
 *  ____           _            __           _____
 * |  _ \    ___  (_)  _ __    / _|  _   _  |_   _|   ___    __ _   _ __ ___
 * | |_) |  / _ \ | | | '_ \  | |_  | | | |   | |    / _ \  / _` | | '_ ` _ \
 * |  _ <  |  __/ | | | | | | |  _| | |_| |   | |   |  __/ | (_| | | | | | | |
 * |_| \_\  \___| |_| |_| |_| |_|    \__, |   |_|    \___|  \__,_| |_| |_| |_|
 *                                   |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ReinfyTeam
 * @link https://github.com/ReinfyTeam/
 *
 *
 */

declare(strict_types=1);

namespace ReinfyTeam\ZuriLite\utils;

use function array_rand;
use function rand;
use function str_split;
use function strtoupper;

class CharUtil {
	public static function generatorCode(int $count) : string {
		$code = "";
		$keys = str_split("qwertyuiopasdfghjklzxcvbnm1234567890");
		for ($i = 0; $i <= $count; $i++) {
			if (rand(1, 100) < 40) {
				$code .= strtoupper($keys[array_rand($keys, 1)]);
			} else {
				$code .= $keys[array_rand($keys, 1)];
			}
		}
		return $code;
	}
}