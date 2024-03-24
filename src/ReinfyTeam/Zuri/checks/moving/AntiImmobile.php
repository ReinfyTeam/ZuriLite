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

namespace ReinfyTeam\Zuri\checks\moving;

use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use ReinfyTeam\Zuri\checks\Check;
use ReinfyTeam\Zuri\player\PlayerAPI;

class AntiImmobile extends Check {
	public function getName() : string {
		return "AntiImmobile";
	}

	public function getSubType() : string {
		return "A";
	}

	public function ban() : bool {
		return false;
	}

	public function kick() : bool {
		return true;
	}

	public function flag() : bool {
		return false;
	}

	public function captcha() : bool {
		return false;
	}

	public function maxViolations() : int {
		return 1;
	}

	public function checkEvent(Event $event, PlayerAPI $playerAPI) : void {
		if ($event instanceof PlayerMoveEvent) {
			$player = $playerAPI->getPlayer();
			if ($player === null) {
				return;
			}
			if ($player->hasNoClientPredictions()) {
				if ($event->getFrom()->getX() !== $event->getTo()->getX() || $event->getFrom()->getY() !== $event->getTo()->getY() || $event->getFrom()->getZ() !== $event->getTo()->getZ()) {
					$this->failed($playerAPI);
				}
				$this->debug($playerAPI, "lastX=" . $event->getFrom()->getX() . ", lastY=" . $event->getFrom()->getY() . ", lastZ=" . $event->getFrom()->getZ() . ", newX=" . $event->getTo()->getX() . ", newY=" . $event->getTo()->getY() . ", newZ=" . $event->getTo()->getZ());
			}
		}
	}
}