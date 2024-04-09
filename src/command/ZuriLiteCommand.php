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

namespace ReinfyTeam\ZuriLite\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use ReinfyTeam\ZuriLite\config\ConfigManager;
use ReinfyTeam\ZuriLite\player\PlayerAPI;
use ReinfyTeam\ZuriLite\utils\forms\FormSender;
use ReinfyTeam\ZuriLite\ZuriLiteAC;
use function count;
use function strtolower;

class ZuriLiteCommand extends Command implements PluginOwned {
	public function __construct() {
		parent::__construct("zuri", "ZuriLite Anticheat", "/zuri <help|sub-command>", ["zuri", "anticheat", "ac"]);
		$this->setPermission("zuri.command");
	}

	public function getOwningPlugin() : ZuriLiteAC {
		return ZuriLiteAC::getInstance();
	}

	public function execute(CommandSender $sender, string $label, array $args) : void {
		$prefix = ConfigManager::getData(ConfigManager::PREFIX);
		$namecmd = $this->getName();
		if ($sender instanceof Player) {
			$playerAPI = PlayerAPI::getAPIPlayer($sender);
		}
		if (isset($args[0])) {
			switch(strtolower($args[0])) {
				case "about":
				case "info":
					$sender->sendMessage(TextFormat::AQUA . "Build: " . TextFormat::GRAY . ZuriLiteAC::getInstance()->getDescription()->getVersion() . TextFormat::AQUA . " Author: " . TextFormat::GRAY . ZuriLiteAC::getInstance()->getDescription()->getAuthors()[0]);
					$sender->sendMessage(TextFormat::AQUA . "Total Checks: " . TextFormat::GRAY . count(ZuriLiteAC::Checks()));
					break;
				case "notify":
				case "notification":
					if (isset($args[1])) {
						switch(strtolower($args[1])) {
							case "toggle":
								$data = ConfigManager::getData(ConfigManager::ALERTS_ENABLE) === true ? ConfigManager::setData(ConfigManager::ALERTS_ENABLE, false) : ConfigManager::setData(ConfigManager::ALERTS_ENABLE, true);
								$sender->sendMessage($prefix . TextFormat::GRAY . " Notify toggle is " . (ConfigManager::getData(ConfigManager::ALERTS_ENABLE) ? TextFormat::GREEN . "enable" : TextFormat::RED . "disable"));
								break;
							case "admin":
								$data = ConfigManager::getData(ConfigManager::ALERTS_ADMIN) === true ? ConfigManager::setData(ConfigManager::ALERTS_ADMIN, false) : ConfigManager::setData(ConfigManager::ALERTS_ADMIN, true);
								$sender->sendMessage($prefix . TextFormat::GRAY . " Notify admin mode is " . (ConfigManager::getData(ConfigManager::ALERTS_ADMIN) ? TextFormat::GREEN . "enable" : TextFormat::RED . "disable"));
								break;
							default:
								$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " notify (toggle/admin) - Use to on/off notify.");
								break;
						}
					} else {
						$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " notify" . TextFormat::RED . " (toggle/admin) - Use to on/off notify.");
					}
					break;
				case "banmode":
				case "ban":
					if (isset($args[1])) {
						switch($args[1]) {
							case "toggle":
								$data = ConfigManager::getData(ConfigManager::BAN_ENABLE) === true ? ConfigManager::setData(ConfigManager::BAN_ENABLE, false) : ConfigManager::setData(ConfigManager::BAN_ENABLE, true);
								$sender->sendMessage($prefix . TextFormat::GRAY . " Ban Mode is " . (ConfigManager::getData(ConfigManager::BAN_ENABLE) ? TextFormat::GREEN . "enable" : TextFormat::RED . "disable"));
								break;
							default: $sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " banmode (toggle/randomize) - Use to on/off ban mode.");
						}
					} else {
						$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " banmode " . TextFormat::RED . " (toggle) - Use to on/off ban mode.");
					}
					break;
				case "bypass":
					$data = ConfigManager::getData(ConfigManager::PERMISSION_BYPASS_ENABLE) === true ? ConfigManager::setData(ConfigManager::PERMISSION_BYPASS_ENABLE, false) : ConfigManager::setData(ConfigManager::PERMISSION_BYPASS_ENABLE, true);
					$sender->sendMessage($prefix . TextFormat::GRAY . " Bypass mode is " . (ConfigManager::getData(ConfigManager::PERMISSION_BYPASS_ENABLE) ? TextFormat::GREEN . "enable" : TextFormat::RED . "disable"));
					break;
				case "debug":
				case "analyze":
					if ($sender instanceof Player) {
						$data = $playerAPI->isDebug() === true ? $playerAPI->setDebug(false) : $playerAPI->setDebug(true);
						$sender->sendMessage($prefix . TextFormat::GRAY . " Debug mode is " . ($playerAPI->isDebug() ? TextFormat::GREEN . "enable" : TextFormat::RED . "disable"));
					} else {
						$sender->sendMessage($prefix . TextFormat::RED . " Please use this command at the game!");
					}
					break;
				case "list":
				case "modules":
				case "checks":
					$sender->sendMessage($prefix . TextFormat::GRAY . " -------------------------------");
					$sender->sendMessage($prefix . TextFormat::GRAY . " ZuriLite Modules/Check Information List:");
					foreach (ZuriLiteAC::Checks() as $check) {
						$sender->sendMessage($prefix . TextFormat::RESET . " " . TextFormat::AQUA . $check->getName() . TextFormat::DARK_GRAY . " (" . TextFormat::YELLOW . $check->getSubType() . TextFormat::DARK_GRAY . ") " . TextFormat::GRAY . "| " . TextFormat::AQUA . "Status: " . ($check->enable() ? TextFormat::GREEN . "Enabled" : TextFormat::RED . "Disabled") . TextFormat::GRAY . " | " . TextFormat::AQUA . "Max Internal Violation: " . TextFormat::YELLOW . $check->maxViolations() . TextFormat::GRAY . " | " . TextFormat::AQUA . "Max Violation: " . TextFormat::YELLOW . ConfigManager::getData(ConfigManager::CHECK . "." . strtolower($check->getName()) . ".maxvl"));
					}
					$sender->sendMessage($prefix . TextFormat::GRAY . " -------------------------------");
					break;
				case "ui":
					if ($sender instanceof Player) {
						FormSender::MainUI($sender);
					} else {
						$sender->sendMessage($prefix . TextFormat::RED . " Please use this command at the game!");
					}
					break;
				default:
				case "help":
					goto help; // redirect ..
					break;
			}
		} else {
			help:
			$sender->sendMessage(TextFormat::RED . "----- ZuriLite Anticheat -----");
			$sender->sendMessage(TextFormat::AQUA . "Build: " . TextFormat::GRAY . ZuriLiteAC::getInstance()->getDescription()->getVersion() . TextFormat::AQUA . " Author: " . TextFormat::GRAY . ZuriLiteAC::getInstance()->getDescription()->getAuthors()[0]);
			$sender->sendMessage("");
			$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " about" . TextFormat::GRAY . " - Show infomation the plugin.");
			$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " notify (toggle/admin)" . TextFormat::GRAY . " - Use to on/off notify.");
			$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " banmode (toggle)" . TextFormat::GRAY . " - Use to on/off ban mode.");
			$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " bypass" . TextFormat::GRAY . " - Use to on/off for bypass mode.");
			$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " debug" . TextFormat::GRAY . " - Use to on/off for debug mode.");
			$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " list" . TextFormat::GRAY . " - List of modules in ZuriLite.");
			$sender->sendMessage(TextFormat::RED . "/" . $namecmd . TextFormat::RESET . " ui" . TextFormat::GRAY . " - Sends the Admin Management UI");
			$sender->sendMessage(TextFormat::RED . "----------------------");
		}
	}
}