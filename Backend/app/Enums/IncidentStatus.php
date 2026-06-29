<?php
namespace App\Enums;
enum IncidentStatus: string
{
    case VALIDATED = 'validated';
    case REPORTED = 'reported';
    case IN_PROGRESS = 'in progress';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
}