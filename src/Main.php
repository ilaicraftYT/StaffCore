<?php

declare(strict_types=1);

namespace ilai\StaffCore;

use ilai\StaffCore\command\FreezeCommand;
use ilai\StaffCore\listener\StaffListener;
use ilai\StaffCore\command\StaffChatCommand;
use ilai\StaffCore\command\StaffModeCommand;
use ilai\StaffCore\command\VanishCommand;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{
    protected $instance;

    public function onEnable(): void{
        $this->instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new StaffListener(), $this);

        $commands = [
            new StaffChatCommand(),
            new StaffModeCommand(),
            new FreezeCommand(),
            new VanishCommand(),
        ];
        $this->getServer()->getCommandMap()->registerAll($this->getName(), $commands);
    }

    public static function getInstance(){
        return self::$instance;
    }
}
