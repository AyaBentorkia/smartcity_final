<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Log;

/**
 * List all comments for an incident.
 *
 * Endpoint: GET /incidents/{incident_id}/comments
 * Allowed users: authenticated
 * @param int $incident_id
 * @return JsonResponse
 */

#[OA\Tag(name: 'Comments', description: 'Gestion des commentaires sur les incidents')]
class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService
    ) {}

    #[OA\Get(
        path: '/incidents/{incident_id}/comments',
        summary: "Lister les commentaires d'un incident",
        tags: ['Comments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'incident_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Liste des commentaires'),
            new OA\Response(response: 404, description: 'Incident non trouvé'),
        ]
    )]
    public function index(int $incident_id): JsonResponse
    {
        try {
            $comments = $this->commentService->getByIncident($incident_id);
Log::info('incident id : ',['incident'=>$incident_id]);
            return response()->json([
                        'message' => 'Comments retrieved successfully',    
            'data' => $comments]);
        } catch (\Exception $e) {
            Log::error('Error fetching incidents per municipality', [
        'message' => $e.message,
        'file'    => $e.file,
        'line'    => $e.line,
        'trace'   => $e.trace,
    ]);
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }
/**
 * Create a new comment on an incident.
 *
 * Endpoint: POST /incidents/{incident_id}/comments
 * Allowed users: authenticated
 * @param Request $request
 * @param int $incident_id
 * @return JsonResponse
 */
    #[OA\Post(
        path: '/incidents/{incident_id}/comments',
        summary: 'Créer un commentaire',
        tags: ['Comments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'incident_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string', example: 'Cet incident doit être résolu rapidement'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Commentaire créé'),
            new OA\Response(response: 404, description: 'Incident non trouvé'),
        ]
    )]
    public function store(Request $request, int $incident_id): JsonResponse
    {
        try {
            $data = $request->validate([
                'content' => 'required|string|max:2000',
            ]);

            $comment = $this->commentService->create(
                $incident_id,
                auth('api')->id(),
                $data
            );

            return response()->json([
                'message' => 'Comment created successfully',
                'data'    => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }
/**
 * Update a comment.
 *
 * Endpoint: PUT /comments/{id}
 * Allowed users: comment owner
 * @param Request $request
 * @param int $id
 * @return JsonResponse
 */
    #[OA\Put(
        path: '/comments/{id}',
        summary: 'Mettre à jour un commentaire',
        tags: ['Comments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [
                    new OA\Property(property: 'content', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Commentaire mis à jour'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Commentaire non trouvé'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'content' => 'required|string|max:2000',
            ]);

            $comment = $this->commentService->update($id, $data['content']);

            return response()->json([
                'message' => 'Commentaire updated successfully',
                'data'    => $comment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }
/**
 * Delete a comment.
 *
 * Endpoint: DELETE /comments/{id}
 * Allowed users: comment owner / admin
 * @param int $id
 * @return JsonResponse
 */
    #[OA\Delete(
        path: '/comments/{id}',
        summary: 'Supprimer un commentaire',
        tags: ['Comments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Commentaire supprimé'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Commentaire non trouvé'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->commentService->delete($id, auth('api')->id());

            return response()->json(['message' => 'Commentaire deleted successfully']);
        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }
}