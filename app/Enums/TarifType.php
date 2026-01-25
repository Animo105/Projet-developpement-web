<?php

namespace App\Enums;

enum TarifType: string
{
    case Participant = "participant";
    case Accompagnateur= "accompagnateur";
    case Arme = "arme";
    case Costume = "costume";
    case Depot = "depot";
    case RabaisFratrie = "rfratrie";
    case RabaisEvent = "revent";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::Participant->value => "Participant.e",
            self::Accompagnateur->value => "Accompagnateur.trice",
            self::Arme->value => "Arme(s)",
            self::Costume->value => "Costume(s)",
            self::Depot->value => "Dépôt",
            self::RabaisFratrie->value => "Rabais de fratrie",
            self::RabaisEvent->value => "Rabais d'évènement",
        ];
    }
}
