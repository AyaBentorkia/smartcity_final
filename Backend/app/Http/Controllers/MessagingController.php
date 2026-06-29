<?php

namespace App\Http\Controllers;

use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Messaging', description: 'Messagerie entre citoyens et responsables municipaux')]
class MessagingController extends Controller
{
    public function __construct(
        private MessagingService $messagingService
    ) {}

    #[OA\Get(
        path: '/conversations',
        summary: 'Mes conversations',
        tags: ['Messaging'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des conversations'),
        ]
    )]
    public function index(): JsonResponse
    {
        $conversations = $this->messagingService->getMyConversations(
            auth('api')->id(),
            auth('api')->user()->role
        );

        return response()->json(['data' => $conversations]);
    }

    #[OA\Get(
        path: '/conversations/{id}',
        summary: 'Voir une conversation avec ses messages',
        tags: ['Messaging'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Conversation avec messages'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Conversation non trouvée'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $conversation = $this->messagingService->getConversation($id, auth('api')->id());

            return response()->json([
                'data' => $conversation->load('messages.sender')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }

    #[OA\Post(
        path: '/conversations',
        summary: 'Démarrer une conversation (Citizen)',
        tags: ['Messaging'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['admin_id', 'message'],
                properties: [
                    new OA\Property(property: 'admin_id',    type: 'integer', description: 'ID du responsable municipal'),
                    new OA\Property(property: 'message',     type: 'string',  example: 'Bonjour, je souhaite signaler un problème'),
                    new OA\Property(property: 'incident_id', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Conversation démarrée'),
            new OA\Response(response: 404, description: 'Responsable municipal non trouvé'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'admin_id'    => 'required|integer|exists:users,id',
                'message'     => 'required|string|max:2000',
                'incident_id' => 'nullable|integer|exists:incidents,id',
            ]);

            $conversation = $this->messagingService->startConversation(
                citizenId:    auth('api')->id(),
                adminId:      $data['admin_id'],
                incidentId:   $data['incident_id'] ?? null,
                firstMessage: $data['message'],
            );

            return response()->json([
                'message' => 'Conversation démarrée avec succès',
                'data'    => $conversation,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }

    #[OA\Post(
        path: '/conversations/{id}/messages',
        summary: 'Envoyer un message',
        tags: ['Messaging'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Merci pour votre retour'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Message envoyé'),
            new OA\Response(response: 400, description: 'Conversation fermée'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'content' => 'required|string|max:2000',
            ]);

            $message = $this->messagingService->sendMessage(
                $id,
                auth('api')->id(),
                $data['content']
            );

            return response()->json([
                'message' => 'Message envoyé avec succès',
                'data'    => $message,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }

    #[OA\Patch(
        path: '/conversations/{id}/close',
        summary: 'Fermer une conversation (Admin municipal)',
        tags: ['Messaging'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Conversation fermée'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Conversation non trouvée'),
        ]
    )]
    public function close(int $id): JsonResponse
    {
        try {
            $conversation = $this->messagingService->closeConversation($id, auth('api')->id());

            return response()->json([
                'message' => 'Conversation fermée avec succès',
                'data'    => $conversation,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }

    #[OA\Delete(
        path: '/messages/{id}',
        summary: 'Supprimer un message',
        tags: ['Messaging'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Message supprimé'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Message non trouvé'),
        ]
    )]
    public function destroyMessage(int $id): JsonResponse
    {
        try {
            $this->messagingService->deleteMessage($id, auth('api')->id());

            return response()->json(['message' => 'Message supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }
}