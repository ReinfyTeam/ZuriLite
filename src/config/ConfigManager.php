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

namespace ReinfyTeam\ZuriLite\config;

use pocketmine\utils\TextFormat;
use ReinfyTeam\ZuriLite\ZuriLiteAC;
use function fclose;
use function file_exists;
use function rename;
use function stream_get_contents;
use function yaml_parse;

class ConfigManager extends ConfigPaths {
	public static function getData(string $path) {
		return ZuriLiteAC::getInstance()->getConfig()->getNested($path);
	}

	public static function setData(string $path, $data, bool $reverseColors = false) {
		ZuriLiteAC::getInstance()->getConfig()->setNested($path, $data);
		ZuriLiteAC::getInstance()->getConfig()->save();
	}

	public static function checkConfig() : void {
		if (!file_exists(ZuriLiteAC::getInstance()->getDataFolder() . "config.yml")) {
			ZuriLiteAC::getInstance()->saveResource("config.yml");
		}

		if (!file_exists(ZuriLiteAC::getInstance()->getDataFolder() . "webhook.yml")) {
			ZuriLiteAC::getInstance()->saveResource("webhook.yml");
		}

		$pluginConfigResource = ZuriLiteAC::getInstance()->getResource("config.yml");
		$pluginConfig = yaml_parse(stream_get_contents($pluginConfigResource));
		fclose($pluginConfigResource);
		$config = ZuriLiteAC::getInstance()->getConfig();
		$log = ZuriLiteAC::getInstance()->getServer()->getLogger();
		if ($pluginConfig == false) {
			$log->critical(self::getData(self::PREFIX) . TextFormat::RED . " Invalid syntax. Currupted config.yml!");
			ZuriLiteAC::getInstance()->getServer()->getPluginManager()->disablePlugin(ZuriLiteAC::getInstance());
			return;
		}
		if ($config->getNested(self::VERSION) === $pluginConfig["zuri"]["version"]) {
			return;
		}
		@rename(ZuriLiteAC::getInstance()->getDataFolder() . "config.yml", ZuriLiteAC::getInstance()->getDataFolder() . "old-config.yml");
		ZuriLiteAC::getInstance()->saveResource("config.yml");
		$log->notice(self::getData(self::PREFIX) . TextFormat::RED . " Outdated configuration! Your config will be renamed as old-config.yml to backup your data.");
	}
}