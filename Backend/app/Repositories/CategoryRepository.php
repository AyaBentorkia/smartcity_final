<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Paginatable;

/**
 * Data access repository for Category model.
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    use Paginatable;

    /**
     * Retrieve all categories.
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Category::get();
    }

    /**
     * Retrieve categories paginated.
     * @param int $perPage
     * @param int $page
     * @return array{data: \Illuminate\Support\Collection, meta: array}
     */
    public function getAllPaginated(int $perPage = 15, int $page = 1): array
    {
        $query = Category::query();
 
        return $this->paginateQuery($query, $perPage, $page);
    }
 

    /**
     * Find a category by ID.
     * @param int $id
     * @return Category|null
     */
    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Create a new category.
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update an existing category.
     * @param Category $category
     * @param array $data
     * @return Category
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    /**
     * Delete a category.
     * @param Category $category
     * @return void
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }
}