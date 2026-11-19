<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case OPERATOR = 'OPERATOR';
    case CUSTOMER = 'CUSTOMER';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::OPERATOR => 'Logistics Operator',
            self::CUSTOMER => 'Customer',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::ADMIN => 'bg-purple-100 text-purple-800 border-purple-200',
            self::OPERATOR => 'bg-blue-100 text-blue-800 border-blue-200',
            self::CUSTOMER => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        };
    }
}
