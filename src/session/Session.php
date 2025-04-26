<?php

declare(strict_types=1);

namespace ilai\StaffCore\session;

use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class Session {
    public Player $player;
    public array $previousInventory;
    public $staffmode = false;
    public $staffchat = false;
    public $frozen = false;
    public Session|null $frozenBy;

    public function __construct(Player $player){
        $this->player = $player;
    }

    public function isStaff(){
        return $this->player->hasPermission("staffcore.staff");
    }

    public function getVanish(){
        return $this->player->isInvisible();
    }

    public function toggleVanish(){
        return $this->player->setInvisible(!$this->getVanish());
    }
}