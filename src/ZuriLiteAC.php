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

namespace ReinfyTeam\ZuriLite;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use ReinfyTeam\ZuriLite\command\ZuriLiteCommand;
use ReinfyTeam\ZuriLite\config\ConfigManager;
use ReinfyTeam\ZuriLite\listener\PlayerListener;
use ReinfyTeam\ZuriLite\listener\ServerListener;
use ReinfyTeam\ZuriLite\network\ProxyUDPSocket;
use ReinfyTeam\ZuriLite\task\ServerTickTask;
use ReinfyTeam\ZuriLite\task\UpdateCheckerAsyncTask;
use ReinfyTeam\ZuriLite\utils\PermissionManager;

class ZuriLiteAC extends PluginBase {
	private static ZuriLiteAC $instance;
	private ProxyUDPSocket $proxyUDPSocket;

	private array $checks = [];

	public function onLoad() : void {
		self::$instance = $this;
		ConfigManager::checkConfig();

		if (!\Phar::running(true)) {
			$this->getServer()->getLogger()->notice(ConfigManager::getData(ConfigManager::PREFIX) . TextFormat::RED . " You are running source-code of the plugin, this might degrade ZuriLite checking performance. We recommended to download phar plugin from poggit builds or github. Instead of using source-code from github.");
		}
	}

	public static function getInstance() : ZuriLiteAC {
		return self::$instance;
	}

	public function onEnable() : void {
		$this->loadChecks();
		$this->getScheduler()->scheduleRepeatingTask(new ServerTickTask($this), 20);
		$this->getServer()->getAsyncPool()->submitTask(new UpdateCheckerAsyncTask($this->getDescription()->getVersion()));
		PermissionManager::getInstance()->register(ConfigManager::getData(ConfigManager::PERMISSION_BYPASS_PERMISSION), PermissionManager::OPERATOR);
		PermissionManager::getInstance()->register(ConfigManager::getData(ConfigManager::ALERTS_PERMISSION), PermissionManager::OPERATOR);
		$this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new ServerListener(), $this);
		$this->getServer()->getCommandMap()->register("ZuriLite", new ZuriLiteCommand());
	}

	/**
	 * Do not call internally, or do not call it double.
	 * @internal
	 */
	public function loadChecks() : void {
		if (!empty($this->checks)) {
			$this->checks = [];
		}
		// TODO: Add all working properly checks in original Zuri..
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\autoclick\AutoClickA();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\autoclick\AutoClickB();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\autoclick\AutoClickC();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\behaivor\InstaBreak();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\behaivor\FastEat();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\behaivor\FastThrow();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\behaivor\EditionFaker();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\behaivor\ProxyBot();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\behaivor\AntiBot();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\fly\FlyA();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\fly\FlyB();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\fly\FlyC();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\killaura\KillauraA();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\killaura\KillauraB();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\killaura\KillauraC();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\reach\ReachA();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\reach\ReachB();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\scaffold\ScaffoldA();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\scaffold\ScaffoldB();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\scaffold\ScaffoldC();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\speed\SpeedA();
		$this->checks[] = new \ReinfyTeam\ZuriLite\checks\speed\SpeedB();
	}

	public static function Checks() : array {
		return ZuriLiteAC::getInstance()->checks;
	}
}
