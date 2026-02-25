<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StartupController extends Controller
{
    public function index(Request $request)
    {
        $query = Startup::query()->orderByDesc('updated_at');
        $statusFilter = $request->get('status', '');
        if ($statusFilter === 'active') {
            $query->active();
        } elseif ($statusFilter === 'disabled') {
            $query->disabled();
        } elseif ($statusFilter === 'banned') {
            $query->banned();
        }
        $search = $request->get('q', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('founder_name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }
        $startups = $query->paginate(20)->withQueryString();

        $content = view('eden.startups.index', [
            'startups' => $startups,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'countActive' => Startup::active()->count(),
            'countDisabled' => Startup::disabled()->count(),
            'countBanned' => Startup::banned()->count(),
            'countFeatured' => Startup::featured()->count(),
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Startups',
            'sidebar' => 'admin',
            'activeNav' => 'startups',
            'dashboardLogo' => 'Eden Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All startups</button>',
            'searchPlaceholder' => "Try searching 'startups by category'",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function create()
    {
        $startup = new Startup(['status' => Startup::STATUS_ACTIVE]);
        return $this->formResponse('Add startup', $startup);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules(), $this->validationMessages());
        if ($validator->fails()) {
            return redirect()->route('admin.startups.create')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);
        if (Startup::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        $data['status'] = $data['status'] ?? Startup::STATUS_ACTIVE;
        $data['is_featured'] = $request->boolean('is_featured');
        Startup::create($data);
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', 'Startup created.']]);
    }

    public function edit(Startup $startup)
    {
        return $this->formResponse('Edit startup', $startup);
    }

    public function update(Request $request, Startup $startup): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules(), $this->validationMessages());
        if ($validator->fails()) {
            return redirect()->route('admin.startups.edit', $startup)
                ->withErrors($validator)
                ->withInput();
        }
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);
        $existing = Startup::where('slug', $data['slug'])->where('id', '!=', $startup->id)->exists();
        if ($existing) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        $data['is_featured'] = $request->boolean('is_featured');
        $startup->update($data);
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', 'Startup updated.']]);
    }

    public function disable(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_DISABLED]);
        return response()->json(['status' => 'success', 'message' => 'Startup disabled.']);
    }

    public function activate(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_ACTIVE]);
        return response()->json(['status' => 'success', 'message' => 'Startup activated.']);
    }

    public function ban(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_BANNED]);
        return response()->json(['status' => 'success', 'message' => 'Startup banned.']);
    }

    public function unban(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_ACTIVE]);
        return response()->json(['status' => 'success', 'message' => 'Startup unbanned.']);
    }

    public function toggleFeatured(Startup $startup)
    {
        $startup->update(['is_featured' => !$startup->is_featured]);
        $label = $startup->is_featured ? 'Featured' : 'Unfeatured';
        return response()->json(['status' => 'success', 'message' => "Startup {$label}.", 'is_featured' => $startup->is_featured]);
    }

    private function formResponse(string $title, Startup $startup)
    {
        $content = view('eden.startups.form', ['startup' => $startup])->render();
        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => 'startups',
            'dashboardLogo' => 'Eden Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All startups</button>',
            'searchPlaceholder' => "Try searching 'startups by category'",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    private function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:64'],
            'website' => ['nullable', 'string', 'max:500', 'url'],
            'location' => ['nullable', 'string', 'max:255'],
            'founder_name' => ['nullable', 'string', 'max:255'],
            'founder_email' => ['nullable', 'email', 'max:255'],
            'founder_twitter_url' => ['nullable', 'string', 'max:500', 'url'],
            'founder_linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'launch_date' => ['nullable', 'date'],
            'twitter_url' => ['nullable', 'string', 'max:500', 'url'],
            'linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'status' => ['nullable', 'in:active,disabled,banned'],
        ];
    }

    private function validationMessages(): array
    {
        return [];
    }
}
