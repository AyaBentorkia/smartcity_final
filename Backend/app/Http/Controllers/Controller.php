<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;


#[OA\Info(
    version: '1.0.0',
    description: 'API complète pour la gestion des incidents municipaux',
    title: 'API Gestion des Incidents',
    contact: new OA\Contact(name: 'Support API', email: 'support@incidents.local')
)]
#[OA\Server(url: 'http://localhost:8000/api', description: 'Serveur local')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Entrez votre token Sanctum (Bearer <token>)'
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}