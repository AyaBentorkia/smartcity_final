<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Category business logic service.
 *
 * Manages category retrieval, creation, update, deletion, and cache invalidation.
 */
class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Retrieve all categories.
     *
     * Allowed users: public
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Cache::remember('categories.all', now()->addHours(24), function () {
            return $this->categoryRepository->getAll();
        });
    }

    /**
     * Create a new category.
     *
     * Allowed users: admin
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category
    {
        $category = $this->categoryRepository->create($data);
        Cache::forget('categories.all'); // ← invalider le cache
        return $category;
    }

    /**
     * Update an existing category.
     *
     * Allowed users: admin
     * @param Category $category
     * @param array $data
     * @return Category
     */
    public function update(Category $category, array $data): Category
    {
        $updated = $this->categoryRepository->update($category, $data);
        Cache::forget('categories.all');
        return $updated;
    }

    /**
     * Delete a category.
     *
     * Allowed users: admin
     * @param Category $category
     * @return void
     */
    public function delete(Category $category): void
    {
        $this->categoryRepository->delete($category);
        Cache::forget('categories.all'); // ← invalider le cache
    }
}