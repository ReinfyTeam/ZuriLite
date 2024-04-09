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

namespace ReinfyTeam\ZuriLite\checks\killaura;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use ReinfyTeam\ZuriLite\checks\Check;
use ReinfyTeam\ZuriLite\player\PlayerAPI;
use ReinfyTeam\ZuriLite\utils\MathUtil;
use function count;

class KillAuraB extends Check {
	public function getName() : string {
		return "KillAura";
	}

	public function getSubType() : string {
		return "E";
	}

	public function ban() : bool {
		return false;
	}

	public function maxViolations() : int {
		return 3;
	}

	public function checkJustEvent(Event $event) : void {
		if ($event instanceof EntityDamageByEntityEvent) {
			$entity = $event->getEntity();
			$damager = $event->getDamager();
			$locDamager = $damager->getLocation();
			if ($damager === null) {
				return;
			}
			if ($damager instanceof Player) {
				$playerAPI = PlayerAPI::getAPIPlayer($damager);
				$player = $playerAPI->getPlayer();
				if ($player === null) {
					return;
				}
				$delta = MathUtil::getDeltaDirectionVector($playerAPI, 3);
				$from = new Vector3($locDamager->getX(), $locDamager->getY() + $damager->getEyeHeight(), $locDamager->getZ());
				$to = $damager->getLocation()->add($delta->getX(), $delta->getY() + $damager->getEyeHeight(), $delta->getZ());
				$distance = MathUtil::distance($from, $to);
				$vector = $to->subtract($from->x, $from->y, $from->z)->normalize()->multiply(1);
				$entities = [];
				for ($i = 0; $i <= $distance; $i += 1) {
					$from = $from->add($vector->x, $vector->y, $vector->z);
					foreach ($damager->getWorld()->getEntities() as $target) {
						$distanceA = new Vector3($from->x, $from->y, $from->z);
						if ($target->getPosition()->distance($distanceA) <= 2.6) {
							$entities[$target->getId()] = $target;
						}
					}
				}
				if (!isset($entities[$entity->getId()])) {
					$this->failed($playerAPI);
				}
				$this->debug($playerAPI, "delta=$delta, distance=$distance, entities=" . count($entities));
			}
		}
	}
}