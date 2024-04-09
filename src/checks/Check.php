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

namespace ReinfyTeam\ZuriLite\checks;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\utils\TextFormat;
use ReinfyTeam\ZuriLite\config\ConfigManager;
use ReinfyTeam\ZuriLite\events\BanEvent;
use ReinfyTeam\ZuriLite\events\KickEvent;
use ReinfyTeam\ZuriLite\events\ServerLagEvent;
use ReinfyTeam\ZuriLite\player\PlayerAPI;
use ReinfyTeam\ZuriLite\task\ServerTickTask;
use ReinfyTeam\ZuriLite\utils\ReplaceText;
use ReinfyTeam\ZuriLite\ZuriLiteAC;
use function in_array;
use function microtime;
use function strtolower;

abstract class Check extends ConfigManager {
	public abstract function getName() : string;

	public abstract function getSubType() : string;

	public function enable() : bool {
		return self::getData(self::CHECK . "." . strtolower($this->getName()) . ".enable");
	}

	public function ban() : bool {
		return self::getData(self::CHECK . "." . strtolower($this->getName()) . ".ban");
	}

	public function kick() : bool {
		return self::getData(self::CHECK . "." . strtolower($this->getName()) . ".kick");
	}

	public function flag() : bool {
		return self::getData(self::CHECK . "." . strtolower($this->getName()) . ".flag");
	}

	public abstract function maxViolations() : int;

	public function check(DataPacket $packet, PlayerAPI $playerAPI) : void {
	}

	public function checkEvent(Event $event, PlayerAPI $playerAPI) : void {
	}

	public function checkJustEvent(Event $event) : void {
	}

	public function replaceText(PlayerAPI $player, string $text, string $reason = "", string $subType = "") : string {
		return ReplaceText::replace($player, $text, $reason, $subType);
	}

	/**
	 * When multiple attempts of violations is within limit of < 0.5s.
	 * @internal
	 */
	public function failed(PlayerAPI $playerAPI) : bool {
		if (($canCheck = self::getData(self::CHECK . "." . strtolower($this->getName()) . ".enable")) !== null) {
			if ($canCheck === false) {
				return false;
			}
		}

		if (ServerTickTask::getInstance()->isLagging(microtime(true)) === true) {
			(new ServerLagEvent($playerAPI))->isLagging();
			return false;
		}

		$player = $playerAPI->getPlayer();
		$notify = self::getData(self::ALERTS_ENABLE) === true;
		$detectionsAllowedToSend = self::getData(self::DETECTION_ENABLE) === true;
		$bypass = self::getData(self::PERMISSION_BYPASS_ENABLE) === true && $player->hasPermission(self::getData(self::PERMISSION_BYPASS_PERMISSION));
		$reachedMaxViolations = $playerAPI->getViolation($this->getName()) > $this->maxViolations();
		$maxViolations = self::getData(self::CHECK . "." . strtolower($this->getName()) . ".maxvl");
		$playerAPI->addViolation($this->getName());
		$reachedMaxRealViolations = $playerAPI->getRealViolation($this->getName()) > $maxViolations;
		$server = ZuriLiteAC::getInstance()->getServer();

		if (self::getData(self::WORLD_BYPASS_ENABLE) === true) {
			if (strtolower(self::getData(self::WORLD_BYPASS_MODE)) === "blacklist") {
				if (in_array($player->getWorld()->getFolderName(), self::getData(self::WORLD_BYPASS_LIST), true)) {
					return false;
				}
			} else {
				if (!in_array($player->getWorld()->getFolderName(), self::getData(self::WORLD_BYPASS_LIST), true)) {
					return false;
				}
			}
		}

		if ($reachedMaxViolations) {
			$playerAPI->addRealViolation($this->getName());
			ZuriLiteAC::getInstance()->getServer()->getLogger()->info(ReplaceText::replace($playerAPI, self::getData(self::ALERTS_MESSAGE), $this->getName(), $this->getSubType()));
			foreach (ZuriLiteAC::getInstance()->getServer()->getOnlinePlayers() as $p) {
				if ($p->hasPermission("zuri.admin")) {
					$p->sendMessage(ReplaceText::replace($playerAPI, self::getData(self::ALERTS_MESSAGE), $this->getName(), $this->getSubType()));
				}
			}
		} else {
			if ($detectionsAllowedToSend) {
				ZuriLiteAC::getInstance()->getServer()->getLogger()->info(ReplaceText::replace($playerAPI, self::getData(self::DETECTION_MESSAGE), $this->getName(), $this->getSubType()));
				foreach (ZuriLiteAC::getInstance()->getServer()->getOnlinePlayers() as $p) {
					if ($p->hasPermission("zuri.admin")) {
						$p->sendMessage(ReplaceText::replace($playerAPI, self::getData(self::DETECTION_MESSAGE), $this->getName(), $this->getSubType()));
					}
				}
			}
		}

		if ($bypass) {
			return false;
		}

		if ($playerAPI->isDebug()) {
			return false;
		}

		if ($this->flag()) {
			$playerAPI->setFlagged(true);
			return true;
		}

		if ($reachedMaxRealViolations && $reachedMaxViolations && $this->ban() && self::getData(self::BAN_ENABLE) === true) {
			(new BanEvent($playerAPI, $this->getName(), $this->getSubType()))->ban();
			ZuriLiteAC::getInstance()->getServer()->getLogger()->notice(ReplaceText::replace($playerAPI, self::getData(self::BAN_MESSAGE), $this->getName(), $this->getSubType()));
			foreach (ZuriLiteAC::getInstance()->getServer()->getOnlinePlayers() as $p) {
				if ($p->hasPermission("zuri.admin")) {
					$p->sendMessage(ReplaceText::replace($playerAPI, self::getData(self::BAN_MESSAGE), $this->getName(), $this->getSubType()));
				}
			}
			foreach (self::getData(self::BAN_COMMANDS) as $command) {
				$server->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), ReplaceText::replace($playerAPI, $command, $this->getName(), $this->getSubType()));
			}

			$playerAPI->resetViolation($this->getName());
			$playerAPI->resetRealViolation($this->getName());
			return true;
		}

		if ($reachedMaxRealViolations && $reachedMaxViolations && $this->kick() && self::getData(self::KICK_ENABLE) === true) {
			(new KickEvent($playerAPI, $this->getName(), $this->getSubType()))->kick();
			if (self::getData(self::KICK_COMMANDS_ENABLED) === true) {
				ZuriLiteAC::getInstance()->getServer()->getLogger()->notice(ReplaceText::replace($playerAPI, self::getData(self::KICK_MESSAGE), $this->getName(), $this->getSubType()));
				$playerAPI->resetViolation($this->getName());
				$playerAPI->resetRealViolation($this->getName());
				foreach (ZuriLiteAC::getInstance()->getServer()->getOnlinePlayers() as $p) {
					if ($p->hasPermission("zuri.admin")) {
						$p->sendMessage(ReplaceText::replace($playerAPI, self::getData(self::KICK_MESSAGE), $this->getName(), $this->getSubType()));
					}
				}
				foreach (self::getData(self::KICK_COMMANDS) as $command) {
					$server->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), ReplaceText::replace($playerAPI, $command, $this->getName(), $this->getSubType()));
				}
			} else {
				ZuriLiteAC::getInstance()->getServer()->getLogger()->notice(ReplaceText::replace($playerAPI, self::getData(self::KICK_MESSAGE), $this->getName(), $this->getSubType()));
				foreach (ZuriLiteAC::getInstance()->getServer()->getOnlinePlayers() as $p) {
					if ($p->hasPermission("zuri.admin")) {
						$p->sendMessage(ReplaceText::replace($playerAPI, self::getData(self::KICK_MESSAGE), $this->getName(), $this->getSubType()));
					}
				}
				$playerAPI->resetViolation($this->getName());
				$playerAPI->resetRealViolation($this->getName());
				$player->kick("Unfair Advantage: ZuriLite Anticheat" /** TODO: Customize logout message? */, null, ReplaceText::replace($playerAPI, self::getData(self::KICK_MESSAGE_UI), $this->getName(), $this->getSubType()));
			}
			return true;
		}
		return false;
	}

	/**
	 * Developers: Debugger for Anticheat
	 * @internal
	 */
	public function debug(PlayerAPI $playerAPI, string $text) : void {
		$player = $playerAPI->getPlayer();

		if (self::getData(self::DEBUG_ENABLE)) {
			if ($playerAPI->isDebug()) {
				$player->sendMessage(self::getData(self::PREFIX) . " " . TextFormat::GRAY . "[DEBUG] " . TextFormat::RED . $this->getName() . TextFormat::GRAY . " (" . TextFormat::YELLOW . $this->getSubType() . TextFormat::GRAY . ") " . TextFormat::AQUA . $text);

				if (self::getData(self::DEBUG_LOG_SERVER)) {
					ZuriLiteAC::getInstance()->getServer()->getLogger()->notice(self::getData(self::PREFIX) . " " . TextFormat::GRAY . "[DEBUG] " . TextFormat::YELLOW . $playerAPI->getPlayer()->getName() . ": " . TextFormat::RED . $this->getName() . TextFormat::GRAY . " (" . TextFormat::YELLOW . $this->getSubType() . TextFormat::GRAY . ") " . TextFormat::AQUA . $text);
				}

				if (self::getData(self::DEBUG_LOG_ADMIN)) {
					foreach (ZuriLiteAC::getInstance()->getServer()->getOnlinePlayers() as $p) {
						if ($p->getName() === $playerAPI->getPlayer()->getName()) {
							continue;
						} // Skip same player. Prevent spam in the chat history.
						if ($p->hasPermission("zuri.admin")) {
							$p->sendMessage(self::getData(self::PREFIX) . " " . TextFormat::GRAY . "[DEBUG] " . TextFormat::YELLOW . $playerAPI->getPlayer()->getName() . ": " . TextFormat::RED . $this->getName() . TextFormat::GRAY . " (" . TextFormat::YELLOW . $this->getSubType() . TextFormat::GRAY . ") " . TextFormat::AQUA . $text);
						}
					}
				}
			}
		}
	}
}
