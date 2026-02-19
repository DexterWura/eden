<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::with(['user', 'startup'])->latest('updated_at')->paginate(20);

        return view('admin.blog.index', ['posts' => $posts]);
    }
}
