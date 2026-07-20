<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view categories')
            ->only([
                'index',
                'show',
            ]);

        $this->middleware('permission:create categories')
            ->only([
                'create',
                'store',
            ]);

        $this->middleware('permission:edit categories')
            ->only([
                'edit',
                'update',
                'toggleStatus',
            ]);

        $this->middleware('permission:archive categories')
            ->only('destroy');

        $this->middleware('permission:restore categories')
            ->only([
                'archived',
                'restore',
            ]);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $categories = Category::query()
            ->withCount('items')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('category_code', 'like', "%{$search}%")
                        ->orWhere('asset_prefix', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, ['active', 'inactive'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact(
            'categories',
            'search',
            'status'
        ));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(
        StoreCategoryRequest $request
    ): RedirectResponse {
        $category = Category::create([
            'category_code' => null,
            'asset_prefix' => $request->asset_prefix,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $category->update([
            'category_code' => sprintf(
                'CAT-%04d',
                $category->id
            ),
        ]);

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category created successfully.'
            );
    }

    public function show(Category $category): View
    {
        $category->load([
            'creator',
            'updater',
            'items' => function ($query) {
                $query
                    ->latest()
                    ->limit(10);
            },
        ]);

        $category->loadCount('items');

        return view(
            'categories.show',
            compact('category')
        );
    }

    public function edit(Category $category): View
    {
        return view(
            'categories.edit',
            compact('category')
        );
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): RedirectResponse {
        $category->update([
            'asset_prefix' => $request->asset_prefix,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category updated successfully.'
            );
    }

    public function toggleStatus(
        Request $request,
        Category $category
    ): RedirectResponse {
        $category->update([
            'status' => $category->status === 'active'
                ? 'inactive'
                : 'active',

            'updated_by' => $request->user()->id,
        ]);

        return back()->with(
            'success',
            'Category status updated successfully.'
        );
    }

    public function destroy(
        Request $request,
        Category $category
    ): RedirectResponse {
        if ($category->items()->exists()) {
            return back()->with(
                'error',
                'This category cannot be archived because it contains items.'
            );
        }

        $category->update([
            'status' => 'inactive',
            'updated_by' => $request->user()->id,
        ]);

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category archived successfully.'
            );
    }

    public function archived(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $categories = Category::onlyTrashed()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('category_code', 'like', "%{$search}%")
                        ->orWhere('asset_prefix', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'categories.archived',
            compact(
                'categories',
                'search'
            )
        );
    }

    public function restore(
        Request $request,
        int $category
    ): RedirectResponse {
        $category = Category::onlyTrashed()
            ->findOrFail($category);

        $category->restore();

        $category->update([
            'status' => 'active',
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('categories.archived')
            ->with(
                'success',
                'Category restored successfully.'
            );
    }
}