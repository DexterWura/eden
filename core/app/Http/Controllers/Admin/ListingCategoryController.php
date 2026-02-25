<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingCategoryController extends Controller
{
    public function index()
    {
        $pageTitle = 'Listing Categories';
        $categories = ListingCategory::withCount('listings')
            ->orderBy('business_type')
            ->orderBy('sort_order')
            ->paginate(getPaginate());

        $businessTypes = $this->getBusinessTypes();

        return view('admin.listing_category.index', compact('pageTitle', 'categories', 'businessTypes'));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'business_type' => 'required|in:domain,website,social_media_account,mobile_app,desktop_app',
            'icon' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($id) {
            $category = ListingCategory::findOrFail($id);
            $message = 'Category updated successfully';
        } else {
            $category = new ListingCategory();
            $message = 'Category created successfully';
        }

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->business_type = $request->business_type;
        $category->icon = $request->icon;
        $category->description = $request->description;
        $category->sort_order = $request->sort_order ?? 0;
        $category->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $category = ListingCategory::findOrFail($id);
        $category->status = !$category->status;
        $category->save();

        $notify[] = ['success', 'Status updated successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $category = ListingCategory::withCount('listings')->findOrFail($id);

        if ($category->listings_count > 0) {
            $notify[] = ['error', 'Cannot delete category with existing listings'];
            return back()->withNotify($notify);
        }

        $category->delete();

        $notify[] = ['success', 'Category deleted successfully'];
        return back()->withNotify($notify);
    }

    private function getBusinessTypes()
    {
        return [
            'domain' => 'Domain Names',
            'website' => 'Websites',
            'social_media_account' => 'Social Media Accounts',
            'mobile_app' => 'Mobile Apps',
            'desktop_app' => 'Desktop Apps',
        ];
    }
}

