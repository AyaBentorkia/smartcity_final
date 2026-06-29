<?php
namespace App\Enums;
enum IncidentUrgencyLevel: string
{
    case LOW = 'faible';
    case MEDIUM = 'moyenne';
    case HIGH = 'élevée';
}