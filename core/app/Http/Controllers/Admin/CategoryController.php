<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Startup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();
        $content = view('eden.categories.index', ['categories' => $categories])->render();
        return response()->view('eden.layout-dashboard', $this->dashboardVars('Categories', 'categories', $content));
    }

    public function create()
    {
        $category = new Category(['sort_order' => (int) Category::max('sort_order') + 1]);
        $content = view('eden.categories.form', ['category' => $category])->render();
        return response()->view('eden.layout-dashboard', $this->dashboardVars('Add category', 'categories', $content));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:64',
            'icon' => 'nullable|string|max:64',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $name = trim($request->input('name'));
        $slug = Str::slug($name);
        if (Category::where('slug', $slug)->exists()) {
            return redirect()->route('admin.categories.create')->withInput()->with('notify', [['error', 'A category with that name already exists.']]);
        }
        Category::create([
            'name' => $name,
            'slug' => $slug,
            'icon' => trim($request->input('icon')) ?: null,
            'sort_order' => (int) ($request->input('sort_order') ?? Category::max('sort_order') + 1),
        ]);
        return redirect()->route('admin.categories.index')->with('notify', [['success', 'Category created.']]);
    }

    public function edit(Category $category)
    {
        $content = view('eden.categories.form', ['category' => $category])->render();
        return response()->view('eden.layout-dashboard', $this->dashboardVars('Edit category', 'categories', $content));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:64',
            'icon' => 'nullable|string|max:64',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $name = trim($request->input('name'));
        $slug = Str::slug($name);
        if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            return redirect()->route('admin.categories.edit', $category)->withInput()->with('notify', [['error', 'A category with that name already exists.']]);
        }
        $oldName = $category->name;
        $category->update([
            'name' => $name,
            'slug' => $slug,
            'icon' => trim($request->input('icon')) ?: null,
            'sort_order' => (int) ($request->input('sort_order') ?? $category->sort_order),
        ]);
        if ($oldName !== $name) {
            Startup::where('category', $oldName)->update(['category' => $name]);
        }
        return redirect()->route('admin.categories.index')->with('notify', [['success', 'Category updated.']]);
    }

    public function destroy(Category $category): RedirectResponse
    {
        $used = Startup::where('category', $category->name)->exists();
        if ($used) {
            return redirect()->route('admin.categories.index')->with('notify', [['error', 'Cannot delete: startups are using this category. Change their category first.']]);
        }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('notify', [['success', 'Category deleted.']]);
    }

    private function dashboardVars(string $title, string $activeNav, string $content): array
    {
        return [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ];
    }
}
