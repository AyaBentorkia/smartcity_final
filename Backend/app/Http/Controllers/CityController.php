<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreCityRequest;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;

/**
 * Controller handling city endpoints.
 */
class CityController extends Controller
{
    public function __construct(private CityService $cityService) {}

    /**
     * Return all cities.
     *
     * Endpoint: GET /cities
     * Allowed users: public
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Cities retrieved successfully',
            'data'    => $this->cityService->getAll(),
        ]);
    }

    /**
     * Return one city detail.
     *
     * Endpoint: GET /cities/{city}
     * Allowed users: public
     * @param City $city
     * @return JsonResponse
     */
    public function show(City $city): JsonResponse
    {
        return response()->json([
            'message' => 'City retrieved successfully',
            'data'    => $city->load('governorate.country'),
        ]);
    }

    /**
     * Return cities by governorate.
     *
     * Endpoint: GET /governorates/{governorateId}/cities
     * Allowed users: public
     * @param int $governorateId
     * @return JsonResponse
     */
    public function getByGovernorate(int $governorateId): JsonResponse
    {
        return response()->json([
            'message' => 'Cities retrieved successfully',
            'data'    => $this->cityService->getByGovernorate($governorateId),
        ]);
    }

    /**
     * Create a new city.
     *
     * Endpoint: POST /admin/cities
     * Allowed users: admin
     * @param StoreCityRequest $request
     * @return JsonResponse
     */
    public function store(StoreCityRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'City created successfully',
                'data'    => $this->cityService->create($request->validated()),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Update a city.
     *
     * Endpoint: PUT /admin/cities/{city}
     * Allowed users: admin
     * @param StoreCityRequest $request
     * @param City $city
     * @return JsonResponse
     */
    public function update(StoreCityRequest $request, City $city): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'City updated successfully',
                'data'    => $this->cityService->update($city, $request->validated()),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Delete a city.
     *
     * Endpoint: DELETE /admin/cities/{city}
     * Allowed users: admin
     * @param City $city
     * @return JsonResponse
     */
    public function destroy(City $city): JsonResponse
    {
        try {
            $this->cityService->delete($city);
            return response()->json(['message' => 'City deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}