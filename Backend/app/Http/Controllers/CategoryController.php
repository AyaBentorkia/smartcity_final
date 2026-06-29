<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Log;


#[OA\Tag(name: 'Categories', description: "Gestion des catégories (services) d'incidents")]
/**
 * Controller for category endpoints.
 */
class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    /**
     * List all categories.
     *
     * Endpoint: GET /categories
     * Allowed users: public
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getAll();

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data'    => $categories,
        ]);
    }

    /**
     * Show a single category.
     *
     * Endpoint: GET /categories/{category}
     * Allowed users: public
     * @param Category $category
     * @return JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'message' => 'Category retrieved successfully',
            'data'    => $category,
        ]);
    }

    /**
     * Create a new category.
     *
     * Endpoint: POST /admin/categories
     * Allowed users: admin
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'        => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
                'color'       => 'nullable|string',
            ]);

            $category = $this->categoryService->create($data);

            return response()->json([
                'message' => 'Category created successfully',
                'data'    => $category,
            ], 201);

        } catch (ValidationException $e) {
            // Erreurs de validation Laravel (y compris unique) → 422
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);

        } catch (QueryException $e) {
            // Violation de contrainte DB (ex: duplicate entry au niveau SQL) → 409
            // SQLSTATE 23000 = integrity constraint violation
            // if ($e->getCode() === '23000') {
            //     return response()->json([
            //         'message' => 'Une catégorie avec ce nom existe déjà.',
            //     ], 409);
            // }

            return response()->json([
                'message' => 'Erreur base de données.',
            ], 500);

        } catch (\Exception $e) {
            // Toute autre erreur — on n'utilise JAMAIS $e->getCode() comme status HTTP
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a category.
     *
     * Endpoint: PUT /admin/categories/{category}
     * Allowed users: admin
     * @param Request $request
     * @param Category $category
     * @return JsonResponse
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        try {
Log::info('update category in controller (debut) ');

            $data = $request->validate([
                'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string',
                'color'       => 'nullable|string',
            ]);

            $updated = $this->categoryService->update($category, $data);
Log::info('update category in controller (fin) ');

            return response()->json([
                'message' => 'Category updated successfully',
                'data'    => $updated,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);

        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Une catégorie avec ce nom existe déjà.',
                ], 409);
            }

            return response()->json([
                'message' => 'Erreur base de données.',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a category.
     *
     * Endpoint: DELETE /admin/categories/{category}
     * Allowed users: admin
     * @param Category $category
     * @return JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        try {
            $this->categoryService->delete($category);

            return response()->json([
                'message' => 'Category deleted successfully',
            ]);

        } catch (QueryException $e) {
            // Ex: catégorie liée à des incidents (FK constraint)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Impossible de supprimer : cette catégorie est liée à des incidents existants.',
                ], 409);
            }

            return response()->json([
                'message' => 'Erreur base de données.',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}