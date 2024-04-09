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

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission as PMPermission;
use pocketmine\permission\PermissionManager as PMPermissionManager;
use pocketmine\utils\NotCloneable;
use pocketmine\utils\NotSerializable;
use pocketmine\utils\SingletonTrait;
use ReinfyTeam\ZuriLite\ZuriLiteAC;

class PermissionManager {
	use NotSerializable;
	use NotCloneable;
	use SingletonTrait;

	/** @var string[] */
	private array $perm = [];

	public const USER = 0;
	public const OPERATOR = 1;
	public const CONSOLE = 3;
	public const NONE = -1;

	public function register(string $permission, int $permAccess, array $childPermission = []) : void {
		$this->perm[] = $permission;
		$perm = new PMPermission($permission, "ZuriLite Anticheat Custom Permission", $childPermission);
		$permManager = PMPermissionManager::getInstance();
		switch($permAccess) {
			case PermissionManager::USER:
				$p = PMPermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER);
				$p->addChild($perm->getName(), true);
				break;
			case PermissionManager::OPERATOR:
				$p = PMPermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
				$p->addChild($perm->getName(), true);
				break;
			case PermissionManager::CONSOLE:
				$p = PMPermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_CONSOLE);
				$p->addChild($perm->getName(), true);
				break;
			case PermissionManager::NONE:
				$p = PMPermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER);
				$p->addChild($perm->getName(), false);
				break;
		}
		$permManager->addPermission($perm);
	}

	public function addPlayerPermissions(Player $player, array $permissions) : void {
		if ($this->attachment === null) {
			$this->attachment = $player->addAttachment(ZuriLiteAC::getInstance());
		}
		$this->attachment->setPermissions($permissions);
		$player->getNetworkSession()->syncAvailableCommands();
	}

	public function resetPlayerPermissions() : void {
		if ($this->attachment === null) {
			return;
		}
		$this->attachment->clearPermissions();
	}

	public function getAllPermissions() : array {
		return $this->perm;
	}
}