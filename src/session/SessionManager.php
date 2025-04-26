<?php

declare(strict_types=1);

namespace ilai\StaffCore\session;

use pocketmine\player\Player;

final class SessionManager {
    private static \WeakMap $data;

    public static function get(Player $player): Session{
        if (!isset(self::$data)){
            $map = new \WeakMap();
            self::$data = $map;
        }

        return self::$data[$player] ??= new Session($player);
    }
}