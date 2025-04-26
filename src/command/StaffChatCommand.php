<?php

declare(strict_types=1);

namespace ilai\StaffCore\command;

use ilai\StaffCore\Main;
use ilai\StaffCore\session\SessionManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class StaffChatCommand extends Command implements PluginOwned {
    public function __construct(){
        parent::__construct("staffchat", "Toggles staff chat.", null, ["sc"]);
        $this->setPermission("staffcore.staff");
    }

    public function getOwningPlugin(): Plugin{
        return Main::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if ($sender instanceof Player){
            $session = SessionManager::get($sender);
            
            $session->staffchat = !$session->staffchat;
            $sender->sendMessage("Staff chat has been " . ($session->staffchat ? "enabled." : "disabled."));
        } else {
            $sender->sendMessage("r u trolling?");
        }
    }
}