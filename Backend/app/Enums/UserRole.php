<?php
namespace App\Enums;
enum UserRole: string
{
    case SUPER_ADMIN = 'super admin';
    case CITIZEN = 'citizen';
    case AGENT = 'agent';
    case ADMIN_MUNICIPAL = 'municipal admin';
}