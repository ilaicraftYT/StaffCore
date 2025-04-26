<?php

declare(strict_types=1);

namespace ilai\StaffCore\command;

use ilai\StaffCore\Main;
use ilai\StaffCore\session\SessionManager;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class VanishCommand extends Command implements PluginOwned
{
    public function __construct()
    {
        parent::__construct("vanish", "Makes you invisible.", null, []);
        $this->setPermission("staffcore.staff");
    }

    public function getOwningPlugin(): Plugin
    {
        return Main::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            $session = SessionManager::get($sender);

            if (!$session->getVanish()) {
                $sender->sendMessage("Vanished!");
            } else {
                $sender->sendMessage("You're visible again.");
            }

            if ($session->staffmode) {
                if (!$session->getVanish()) {
                    $sender->getInventory()->setItem(
                        7,
                        VanillaItems::DYE()
                            ->setColor(DyeColor::RED())
                            ->setCustomName(TextFormat::RED . "Vanish")
                    );
                } else {
                    $sender->getInventory()->setItem(
                        7,
                        VanillaItems::DYE()
                            ->setColor(DyeColor::LIME())
                            ->setCustomName(TextFormat::GREEN . "Vanish")
                    );
                }
            }

            $session->toggleVanish();
        } else {
            $sender->sendMessage("lmfao");
        }
    }
}
