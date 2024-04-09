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

namespace ReinfyTeam\ZuriLite\checks\speed;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use ReinfyTeam\ZuriLite\checks\Check;
use ReinfyTeam\ZuriLite\player\PlayerAPI;
use function intval;

class SpeedB extends Check {
	public function getName() : string {
		return "Speed";
	}

	public function getSubType() : string {
		return "B";
	}

	public function maxViolations() : int {
		return 4;
	}

	public function check(DataPacket $packet, PlayerAPI $playerAPI) : void {
		$nLocation = $playerAPI->getNLocation();
		$player = $playerAPI->getPlayer();
		if ($player === null) {
			return;
		}

		if (
			$playerAPI->isOnStairs() ||
			$playerAPI->isOnIce() ||
			$playerAPI->getTeleportTicks() < 40 ||
			$player->isFlying() ||
			!$playerAPI->isOnGround() ||
			$playerAPI->getAttackTicks() < 40 ||
			$playerAPI->isOnAdhesion() ||
			$player->getAllowFlight() ||
			$player->isCreative()
		) {
			return;
		}

		if ($playerAPI->getOnlineTime() > 10 && !empty($nLocation) && $player->isSurvival()) {
			$recived = false;
			if ($packet instanceof MovePlayerPacket) {
				$recived = true;
			}
			if ($packet instanceof PlayerAuthInputPacket) {
				$limit = $player->getMovementSpeed() * 4.8;
				$distX = $nLocation["to"]->getX() - $nLocation["from"]->getX();
				$distZ = $nLocation["to"]->getZ() - $nLocation["from"]->getZ();
				$dist = ($distX * $distX) + ($distZ * $distZ);
				$lastDist = $dist;
				$shiftedLastDist = $lastDist * 0.91;
				$equalness = $dist - $shiftedLastDist;
				$scaledEqualness = $equalness * 138;
				$idBlockDown = $player->getWorld()->getBlockAt(intval($player->getLocation()->getX()), intval($player->getLocation()->getY() - 0.01), intval($player->getLocation()->getZ()))->getTypeId();
				$isFalling = $playerAPI->getLastGroundY() > $player->getLocation()->getY();
				$limit += $playerAPI->getJumpTicks() < 40 ? $limit : 0;
				if ($playerAPI->isOnAdhesion() && !$playerAPI->isOnIce() && $playerAPI->getAttackTicks() > 100 && $player->isSurvival() && !$recived && !$isFalling && $idBlockDown !== 0) {
					if ($scaledEqualness > $limit) {
						$this->debug($playerAPI, "limit=$limit, distX=$distX, distZ=$distZ, dist=$dist, lastDist=$dist, shiftedLastDist=$shiftedLastDist, equalness=$equalness, scaledEqualness=$scaledEqualness");
						$this->failed($playerAPI);
					}
				}
			}
		}
	}
}