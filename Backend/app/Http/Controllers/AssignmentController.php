<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Incident;
use App\Services\AssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Log;

#[OA\Tag(name: 'Assignments', description: "Gestion des affectations d'incidents aux agents")]
class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentService $assignmentService
    ) {}

    /**
 * List all assignments (paginated).
 *
 * Endpoint: GET /admin/assignments
 * Allowed users: super admin
 * @param Request $request
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin/assignments',
        summary: 'Lister tous les assignments',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des assignments'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $result = $this->assignmentService->getAllPaginated(
            perPage: $request->integer('per_page', 10),
            page:    $request->integer('page', 1),
        );

        return response()->json([
            'message' => 'Assignments retrieved successfully',
            'data'    => $result['data'],
            'meta'    => $result['meta'],
        ]);
    }

   /**
 * Show a single assignment.
 *
 * Endpoint: GET /admin_manager/assignment/{assignment_id}
 * Allowed users: admin municipal / super admin
 * @param Assignment $assignment
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin_manager/assignment/{assignment_id}',
        summary: 'Voir un assignment spécifique',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'assignment_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: "ID de l'assignment"),
        ],
        responses: [
            new OA\Response(response: 200, description: "Détails de l'assignment"),
            new OA\Response(response: 404, description: 'Assignment non trouvé'),
        ]
    )]
    public function show(Assignment $assignment): JsonResponse
    {
        try {
            $assignment = $this->assignmentService->findById($assignment->id);

            return response()->json([
                'message' => 'Assignment retrieved successfully',
                'data'    => $assignment,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

   /**
 * Get the authenticated agent's own assignments (paginated).
 *
 * Endpoint: GET /agent/my-assignments
 * Allowed users: agent
 * @param Request $request
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/agent/my-assignments',
        summary: 'Récupérer mes assignments (Agent)',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des assignments de l'agent"),
        ]
    )]
    public function getMyAssignmentsByAgent(Request $request): JsonResponse
    {
        $result = $this->assignmentService->getMyAssignmentsByAgentPaginated(
            auth('api')->id(),
            perPage: $request->integer('per_page', 10),
            page:    $request->integer('page', 1),
        );

        return response()->json([
            'message' => 'Assignments retrieved successfully',
            'data'    => $result['data'],
            'meta'    => $result['meta'],
        ]);
    }

   /**
 * Get assignments created by the authenticated admin (paginated).
 *
 * Endpoint: GET /admin_manager/my-assignments
 * Allowed users: admin municipal
 * @param Request $request
 * @return JsonResponse
 */
    #[OA\Get(
        path: '/admin_manager/my-assignments',
        summary: 'Récupérer mes assignments (Admin Municipal)',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: "Liste des assignments créés par l'admin"),
        ]
    )]
    public function getMyAssignmentsByAdmin(Request $request): JsonResponse
    {
        $result = $this->assignmentService->getMyAssignmentsByAdminPaginated(
            auth('api')->id(),
            perPage: $request->integer('per_page', 10),
            page:    $request->integer('page', 1),
        );

        return response()->json([
            'message' => 'Assignments retrieved successfully',
            'data'    => $result['data'],
            'meta'    => $result['meta'],
        ]);
    }

    /**
 * Assign an incident to an agent.
 *
 * Endpoint: POST /admin_manager/incidents/{incident}/assign
 * Allowed users: admin municipal
 * @param Request $request
 * @param Incident $incident
 * @return JsonResponse
 */
    #[OA\Post(
        path: '/admin_manager/incidents/{incident}/assign',
        summary: 'Assigner un incident à un agent',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'incident', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: "ID de l'incident"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['agent'],
                properties: [
                    new OA\Property(property: 'agent', type: 'integer', description: "ID de l'agent"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Assignment créé avec succès'),
            new OA\Response(response: 404, description: 'Agent ou incident non trouvé'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function assign(Request $request, Incident $incident): JsonResponse
    {
        try {
            $validated = $request->validate([
                'agent' => 'required|integer|exists:users,id',
            ]);

            $assignment = $this->assignmentService->assign(
                $incident,
                $validated['agent'],
                auth('api')->id()
            );

            return response()->json([
                'message' => 'Assignment created sucessfully ',
                'data'    => $assignment,
            ], 201);
        } catch (\Exception $e) {
             Log::error('Erreur dans update status', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
            return response()->json(['message' => $e->getMessage()], (int) $e->getCode() ?: 500);
        }
    }

    /**
 * Reassign an existing assignment to a different agent.
 *
 * Endpoint: PUT /admin_manager/assignments/{assignment_id}
 * Allowed users: admin municipal
 * @param Request $request
 * @param Assignment $assignment
 * @return JsonResponse
 */
    #[OA\Put(
        path: '/admin_manager/assignments/{assignment_id}',
        summary: 'Mettre à jour un assignment (réassigner à un autre agent)',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'assignment_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['agent'],
                properties: [
                    new OA\Property(property: 'agent', type: 'integer', description: 'ID du nouvel agent'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Assignment mis à jour avec succès'),
            new OA\Response(response: 403, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Agent non trouvé'),
        ]
    )]
    public function update(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'agent' => 'required|integer|exists:users,id',
            ]);

            $updated = $this->assignmentService->update(
                $assignment,
                $validated['agent'],
                auth('api')->user()
            );

            return response()->json([
                'message' => 'Assignment updated successfully',
                'data'    => $updated,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

   /**
 * Close an assignment (mark as finished by the agent).
 *
 * Endpoint: PATCH /agent/assignments/{assignment_id}
 * Allowed users: agent
 * @param Assignment $assignment
 * @return JsonResponse
 */
    #[OA\Patch(
        path: '/agent/assignments/{assignment_id}',
        summary: 'Clôturer un assignment (Agent)',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'assignment_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: "ID de l'assignment"),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Assignment clôturé avec succès'),
            new OA\Response(response: 400, description: 'Assignment déjà clôturé'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function close(Assignment $assignment): JsonResponse
    {
        try {
            $closed = $this->assignmentService->close($assignment, auth('api')->id());

            return response()->json([
                'message' => 'Assignment finished',
                'data'    => $closed,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Delete an assignment.
 *
 * Endpoint: DELETE /admin_manager/assignment/{assignment_id}
 * Allowed users: admin municipal
 * @param Assignment $assignment
 * @return JsonResponse
 */
    #[OA\Delete(
        path: '/admin_manager/assignment/{assignment_id}',
        summary: 'Supprimer un assignment',
        tags: ['Assignments'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'assignment_id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Assignment supprimé avec succès'),
            new OA\Response(response: 400, description: 'Impossible de supprimer un assignment clôturé'),
            new OA\Response(response: 403, description: 'Non autorisé'),
        ]
    )]
    public function destroy(Assignment $assignment): JsonResponse
    {
        try {
            $this->assignmentService->delete($assignment, auth('api')->user());

            return response()->json(['message' => 'Assignment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}