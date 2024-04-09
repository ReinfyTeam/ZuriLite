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

namespace ReinfyTeam\ZuriLite\checks\behaivor;

use pocketmine\event\Event;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use ReinfyTeam\ZuriLite\checks\Check;
use function explode;
use function strtoupper;

class AntiBot extends Check {
	public function getName() : string {
		return "AntiBot";
	}

	public function getSubType() : string {
		return "A";
	}

	public function maxViolations() : int {
		return 0;
	}

	public function checkJustEvent(Event $event) : void {
		if ($event instanceof PlayerPreLoginEvent) {
			$extraData = $event->getPlayerInfo()->getExtraData();
			if ($extraData["DeviceOS"] === DeviceOS::ANDROID) {
				$model = explode(" ", $extraData["DeviceModel"], 2)[0];
				if ($model !== strtoupper($model) && $model !== "") {
					$event->setKickFlag(0, self::getData(self::ANTIBOT_MESSAGE));
				}
			}
		}
	}
}