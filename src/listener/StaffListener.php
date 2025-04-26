<?php

declare(strict_types=1);

namespace ilai\StaffCore\listener;

use ilai\StaffCore\session\SessionManager;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMissSwingEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class StaffListener implements Listener
{
    public function onJoin(PlayerJoinEvent $ev)
    {
        $player = $ev->getPlayer();
        $session = SessionManager::get($player);

        if ($session->isStaff()) {
            $message = TextFormat::DARK_AQUA . "Staff | " . TextFormat::RESET . $player->getDisplayName() . TextFormat::DARK_AQUA . " connected.";

            // Notify staff connected
            foreach (Server::getInstance()->getOnlinePlayers() as $online) {
                if ($online->hasPermission("staffcore.staff")) {
                    $online->sendMessage($message);
                }
            }
        }
    }

    public function onLeave(PlayerQuitEvent $ev)
    {
        $player = $ev->getPlayer();
        $session = SessionManager::get($player);

        if ($session->isStaff()) {
            $message = TextFormat::DARK_AQUA . "Staff | " . TextFormat::RESET . $player->getDisplayName() . TextFormat::DARK_AQUA . " left.";

            // Notify staff connected
            foreach (Server::getInstance()->getOnlinePlayers() as $online) {
                if ($online->hasPermission("staffcore.staff")) {
                    $online->sendMessage($message);
                }
            }
        } else {
            if ($session->frozen) {
                $session->frozenBy->player->sendMessage(
                    TextFormat::DARK_AQUA . "Staff | Player " . TextFormat::RESET . $player->getDisplayName() . TextFormat::DARK_AQUA . " disconnected while frozen."
                );
            }
        }
    }

    public function onChat(PlayerChatEvent $ev)
    {
        $session = SessionManager::get($ev->getPlayer());

        if ($session->staffchat) {
            $ev->cancel();

            $message = TextFormat::DARK_AQUA . "Staff | " . TextFormat::RESET . $session->player->getDisplayName() . TextFormat::DARK_AQUA . ": " . TextFormat::RESET . $ev->getMessage();

            foreach (Server::getInstance()->getOnlinePlayers() as $online) {
                if ($online->hasPermission("staffcore.staff") && SessionManager::get($online)->staffchat) {
                    $online->sendMessage($message);
                }
            }
        }
    }

    public function onDropItem(PlayerDropItemEvent $ev)
    {
        $player = $ev->getPlayer();
        $session = SessionManager::get($player);

        if ($session->staffmode || $session->frozen) {
            $ev->cancel();
        }
    }

    public function onBreakBlock(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        $session = SessionManager::get($player);

        if ($session->staffmode || $session->frozen) {
            $ev->cancel();

            if (
                $session->isStaff() &&
                $player->getInventory()->getItemInHand() ==
                VanillaItems::COMPASS()->setCustomName(TextFormat::RED . "Compass")
            ) {
                $player->teleport(
                    $ev->getBlock()->getPosition()->addVector($player->getDirectionVector())
                );
            }
        }
    }

    public function onPlaceBlock(BlockPlaceEvent $ev){
        $player = $ev->getPlayer();
        $session = SessionManager::get($player);

        if ($session->staffmode || $session->frozen) {
            $ev->cancel();
        }
    }

    public function onEntityHit(EntityDamageByEntityEvent $ev)
    {
        if ($ev->getCause() == EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK) {
            $damager = $ev->getDamager();
            $victim = $ev->getEntity();

            if ($damager instanceof Player && $victim instanceof Player) {
                $damagerSession = SessionManager::get($damager);
                $victimSession = SessionManager::get($victim);

                if($victimSession->frozen || $damagerSession->frozen){
                    $ev->cancel();
                    return;
                }

                if ($damagerSession->staffmode) {
                    $ev->cancel();

                    if (
                        $damager->getInventory()->getItemInHand() ==
                        VanillaBlocks::ICE()->asItem()->setCustomName(TextFormat::AQUA . "Freeze")
                    ) {
                        if (!$victimSession->frozen) {
                            $victimSession->frozen = true;
                            $victimSession->frozenBy = $damagerSession;
                            $victim->setNoClientPredictions();
                            $victim->sendMessage(
                                TextFormat::GOLD . TextFormat::BOLD . "YOU HAVE BEEN FROZEN." . "\n" .
                                    TextFormat::RESET . TextFormat::YELLOW . "Please follow carefully the staff's instructions. They might require you to install Discord or AnyDesk."
                            );

                            $damager->sendMessage("Target was frozen.");
                        } else {
                            $victimSession->frozen = false;
                            $victimSession->frozenBy = null;
                            $victim->setNoClientPredictions(false);
                            $victim->sendMessage(TextFormat::DARK_PURPLE . "You were unfrozen. Don't get in any trouble, have a good day!");

                            $damager->sendMessage("Target was unfrozen.");
                        }
                    }
                }
            }
        }
    }

    public function onPlayerMove(PlayerMoveEvent $ev)
    {
        $player = $ev->getPlayer();
        $session = SessionManager::get($player);

        if (
            $session->frozen &&
            (
                $ev->getFrom()->getYaw() == $ev->getTo()->getYaw() &&
                $ev->getFrom()->getPitch() == $ev->getTo()->getPitch()
            )
        ) {
            $ev->cancel();
            $player->sendTitle(TextFormat::GOLD . "FROZEN", "", 0, -1, 0);
            $player->sendActionBarMessage(TextFormat::YELLOW . "Follow staff instructions.");
        }
    }

    public function onInteract(PlayerMissSwingEvent $ev){
        $player = $ev->getPlayer();
        $session = SessionManager::get($player);

        if ($session->staffmode){
            if ($player->getInventory()->getItemInHand()->getTypeId() == VanillaItems::DYE()->getTypeId()){
                if (!$session->getVanish()){
                    $player->getInventory()->setItemInHand(
                        VanillaItems::DYE()
                            ->setColor(DyeColor::RED())
                            ->setCustomName(TextFormat::RED . "Vanish")
                    );
                    $player->sendMessage("Vanished!");
                } else {
                    $player->getInventory()->setItemInHand(
                        VanillaItems::DYE()
                            ->setColor(DyeColor::LIME())
                            ->setCustomName(TextFormat::GREEN . "Vanish")  
                    );
                    $player->sendMessage("You're visible again.");
                }
    
                $session->toggleVanish();
            }

            if ($player->getInventory()->getItemInHand()->getTypeId() == VanillaItems::CLOCK()->getTypeId()){
                $players = Server::getInstance()->getOnlinePlayers();

                $player->teleport($players[array_rand($players)]->getPosition()->asVector3());
                $player->sendMessage("Teleported.");
            }
        }
    }
}
