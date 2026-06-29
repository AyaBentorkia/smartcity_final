<?php
namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CountryService;
use App\Http\Requests\StoreCountryRequest;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    public function __construct(private CountryService $countryService) {}

    /**
 * List all zones.
 *
 * Endpoint: GET /zones
 * Allowed users: authenticated
 * @return JsonResponse
 */
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Countries retrieved successfully',
            'data'    => $this->countryService->getAll(),
        ]);
    }
/**
 * List zones belonging to the authenticated admin's municipality.
 *
 * Endpoint: GET /admin_manager/zones/nearby
 * Allowed users: admin municipal
 * @return JsonResponse
 */
    public function show(Country $country): JsonResponse
    {
        return response()->json([
            'message' => 'Country retrieved successfully',
            'data'    => $country,
        ]);
    }
/**
 * Create a new zone.
 *
 * Endpoint: POST /admin_manager/zones
 * Allowed users: admin municipal
 * @param StoreZoneRequest $request
 * @return JsonResponse
 */
    public function store(StoreCountryRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Country created successfully',
                'data'    => $this->countryService->create($request->validated()),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
/**
 * Update a zone.
 *
 * Endpoint: PUT /admin/zones/{id}
 * Allowed users: super admin
 * @param UpdateZoneRequest $request
 * @param int $id
 * @return JsonResponse
 */
    public function update(StoreCountryRequest $request, Country $country): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Country updated successfully',
                'data'    => $this->countryService->update($country, $request->validated()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
 * Delete a zone.
 *
 * Endpoint: DELETE /admin/zones/{id}
 * Allowed users: super admin
 * @param int $id
 * @return JsonResponse
 */
    public function destroy(Country $country): JsonResponse
    {
        try {
            $this->countryService->delete($country);
            return response()->json(['message' => 'Country deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}