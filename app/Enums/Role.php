<?php

namespace App\Enums;

enum Role: int
{
    case Operator = 1;
    case LineLeader = 2;
    case Mechanic = 3;
    case FloorIncharge = 4;
    case MaintenanceHead = 5;
    case MaintenanceManager = 6;
    case IndustrialEngineer = 7;

    public function label(): string
    {
        return match ($this) {
            self::Operator => 'Operator',
            self::LineLeader => 'Line Leader',
            self::Mechanic => 'Mechanic',
            self::FloorIncharge => 'Floor In-charge',
            self::MaintenanceHead => 'Maintenance Head',
            self::MaintenanceManager => 'Maintenance Manager',
            self::IndustrialEngineer => 'Industrial Engineer',
        };
    }

    public static function getTargetRoleForEscalation(int $level): int
    {
        return match($level) {
            1 => self::FloorIncharge->value,
            2 => self::MaintenanceHead->value,
            default => self::IndustrialEngineer->value,
        };
    }
}
