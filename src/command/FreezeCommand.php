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
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class FreezeCommand extends Command implements PluginOwned
{
    public function __construct()
    {
        parent::__construct("freeze", "Freezes someone.", "/freeze <player name>", ["ss"]);
        $this->setPermission("staffcore.staff");
    }

    public function getOwningPlugin(): Plugin
    {
        return Main::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (($args[0] ?? null) == null) {
            $sender->sendMessage($this->getUsage());
            return;
        }
        $session = SessionManager::get($sender);
        $target = SessionManager::get(Server::getInstance()->getPlayerExact($args[0]));

        if ($target == null){
            $sender->sendMessage("Could not find player.");
            return;
        }

        if ($target->frozen) {
            $target->frozen = false;
            $target->frozenBy = null;
            $target->player->setNoClientPredictions(false);
            $target->player->sendMessage(TextFormat::DARK_PURPLE . "You were unfrozen. Don't get in any trouble, have a good day!");

            $sender->sendMessage("Target was unfrozen.");
        } else {
            $target->frozen = true;
            $target->frozenBy = $session;
            $target->player->setNoClientPredictions();
            $target->player->sendMessage(
                TextFormat::GOLD . TextFormat::BOLD . "YOU HAVE BEEN FROZEN." . "\n" .
                TextFormat::RESET . TextFormat::YELLOW . "Please follow carefully the staff's instructions. They might require you to install Discord or AnyDesk."
            );
            

            $sender->sendMessage("Target was frozen.");
        }
    }
}
