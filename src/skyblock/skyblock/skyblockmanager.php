<?php

/**
 * This is GiantQuartz property.
 *
 * Copyright (C) 2016 GiantQuartz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author GiantQuartz
 *
 */

namespace SkyBlock\skyblock;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\generator\Generator;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use SkyBlock\Main;

class SkyBlockManager {

    /** @var Main */
    private $plugin;

    /**
     * SkyBlockManager constructor.
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function generateIsland(Player $player, $generatorName = "basic") {
        $this->plugin->getIslandManager()->createIsland($player, $generatorName);
        $server = $this->plugin->getServer();
        $island = $this->getPlayerConfig($player)->get("island");
        $server->generateLevel($island, null, Generator::getGenerator($generatorName));
        $server->loadLevel($island);
        $this->spawnDefaultChest($island);
    }

    public function spawnDefaultChest($islandName) {
        $level = $this->plugin->getServer()->getLevelByName($islandName);
        $level->setBlock(new Vector3(10, 6, 4), new Block(0, 0));
        $level->loadChunk(10, 4, true);
        /** @var Chest $chest */
        $chest = Tile::createTile("Chest",$level->getChunk(10 >> 4, 4 >> 4), new CompoundTag(" ", [
            new ListTag("Items", []),
            new StringTag("id", Tile::CHEST),
            new IntTag("x", 10),
            new IntTag("y", 6),
            new IntTag("z", 4)
        ]));
        $level->setBlock(new Vector3(10, 6, 4), new Block(54, 0));
        $level->addTile($chest);
        $inventory = $chest->getInventory();
        }

    /**
     * Return player data
     *
     * @param Player $player
     * @return string
     */
    public function getPlayerDataPath(Player $player) {
        return $this->plugin->getDataFolder() . "users" . DIRECTORY_SEPARATOR . strtolower($player->getName()) . ".json";
    }

    /**
     * Register a user
     *
     * @param Player $player
     */
    public function registerUser(Player $player) {
        new Config($this->getPlayerDataPath($player), Config::JSON, [
            "island" => ""
        ]);
    }

    /**
     * Tries to register a player
     *
     * @param Player $player
     */
    public function tryRegisterUser(Player $player) {
        if(!is_file($this->getPlayerDataPath($player))) {
            $this->registerUser($player);
        }
    }

    /**
     * Return player config
     *
     * @param Player $player
     * @return Config
     */
    public function getPlayerConfig(Player $player) {
        return new Config($this->getPlayerDataPath($player), Config::JSON);
    }

}
