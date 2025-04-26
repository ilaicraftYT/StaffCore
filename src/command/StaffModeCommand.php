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

class StaffModeCommand extends Command implements PluginOwned
{
    public function __construct()
    {
        parent::__construct("staffmode", "Toggles staff mode.", null, ["sm"]);
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

            $session->staffmode = !$session->staffmode;
            $sender->sendMessage("Staff mode has been " . ($session->staffmode ? "enabled." : "disabled."));

            if ($session->staffmode) {
                $sender->setGamemode(GameMode::CREATIVE());
                $session->previousInventory = $sender->getInventory()->getContents();
                $sender->getInventory()->clearAll();
                $items = [
                    VanillaItems::AIR(),
                    VanillaBlocks::ICE()->asItem()
                        ->setCustomName(TextFormat::AQUA . "Freeze"),
                    VanillaItems::AIR(),
                    VanillaItems::COMPASS()
                        ->setCustomName(TextFormat::RED . "Compass"),
                    VanillaItems::AIR(),
                    VanillaItems::CLOCK()
                        ->setCustomName(TextFormat::YELLOW . "Random Player"),
                    VanillaItems::AIR(),
                    VanillaItems::DYE()
                        ->setColor(DyeColor::LIME())
                        ->setCustomName(TextFormat::GREEN . "Vanish"),
                    VanillaItems::AIR(),
                ];
                $sender->getInventory()->setContents($items);
            } else {
                $sender->setGamemode(GameMode::SURVIVAL());
                $sender->getInventory()->setContents($session->previousInventory);
            }
        } else {
            $sender->sendMessage("r u serious?");
        }
    }
}
