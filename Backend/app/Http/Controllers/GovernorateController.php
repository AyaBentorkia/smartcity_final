<?php
namespace App\Http\Controllers;

use App\Models\Governorate;
use App\Services\GovernorateService;
use App\Http\Requests\StoreGovernorateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GovernorateController extends Controller
{
    public function __construct(private GovernorateService $governorateService) {}

    /**
 * List all governorates.
 * Endpoint: GET /governorates | Allowed users: public
 * @return JsonResponse
 */
    public function index(): JsonResponse
    {
        Log::info('Fetching all governorates from controller');
        return response()->json([
            'message' => 'Governorates retrieved successfully',
            'data'    => $this->governorateService->getAll(),
        ]);
    }
    /**
 * Show a single governorate.
 * Endpoint: GET /governorates/{governorate} | Allowed users: public
 * @param Governorate $governorate
 * @return JsonResponse
 */

    public function show(Governorate $governorate): JsonResponse
    {
        return response()->json([
            'message' => 'Governorate retrieved successfully',
            'data'    => $governorate,
        ]);
    }
/**
 * List governorates belonging to a country.
 * Endpoint: GET /countries/{countryId}/governorates | Allowed users: public
 * @param int $countryId
 * @return JsonResponse
 */
    public function getByCountry(int $countryId): JsonResponse
    {
        return response()->json([
            'message' => 'Governorates retrieved successfully',
            'data'    => $this->governorateService->getByCountry($countryId),
        ]);
    }
    /**
 * Create a new governorate.
 * Endpoint: POST /admin/governorates | Allowed users: super admin
 * @param StoreGovernorateRequest $request
 * @return JsonResponse
 */

    public function store(StoreGovernorateRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Governorate created successfully',
                'data'    => $this->governorateService->create($request->validated()),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Update a governorate.
 * Endpoint: PUT /admin/governorates/{governorate} | Allowed users: super admin
 * @param StoreGovernorateRequest $request
 * @param Governorate $governorate
 * @return JsonResponse
 */
    public function update(StoreGovernorateRequest $request, Governorate $governorate): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Governorate updated successfully',
                'data'    => $this->governorateService->update($governorate, $request->validated()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Delete a governorate.
 * Endpoint: DELETE /admin/governorates/{governorate} | Allowed users: super admin
 * @param Governorate $governorate
 * @return JsonResponse
 */
    public function destroy(Governorate $governorate): JsonResponse
    {
        try {
            $this->governorateService->delete($governorate);
            return response()->json(['message' => 'Governorate deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}