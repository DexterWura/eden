<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Traits\MasksSensitiveData;

class StaffController extends Controller
{
    use MasksSensitiveData;
    /**
     * List all admins (staff and super admins).
     */
    public function index()
    {
        $pageTitle = 'Manage Staff';
        $admins = Admin::orderBy('is_super_admin', 'desc')->orderBy('id')->paginate(getPaginate());
        
        // Mask sensitive data if demo user
        if ($this->shouldMaskData()) {
            $admins->getCollection()->transform(function ($admin) {
                $admin->email = $this->maskEmail($admin->email);
                return $admin;
            });
        }
        
        $modules = $this->getAssignableModules();
        return $this->dashboardPage('Staff & RBAC', 'staff.index', compact('admins', 'modules'));
    }

    /**
     * Module keys that can be assigned to staff (exclude manage_staff so only super admin has it).
     */
    protected function getAssignableModules(): array
    {
        $all = config('admin_modules.modules', []);
        return array_values(array_diff($all, ['manage_staff']));
    }

    public function create()
    {
        $pageTitle = 'Add Staff';
        $modules = $this->getAssignableModules();
        return $this->dashboardPage('Add staff', 'staff.form', [
            'staff' => new Admin(),
            'modules' => $modules,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'username' => 'required|string|max:255|unique:admins,username',
            'password' => 'required|min:8|confirmed',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'modules' => 'required|array|min:1',
            'modules.*' => 'string|in:' . implode(',', $this->getAssignableModules()),
        ], [
            'modules.required' => 'At least one module (role) must be assigned to staff.',
            'modules.min' => 'At least one module (role) must be assigned to staff.',
        ]);

        $admin = new Admin();
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->username = $request->username;
        $admin->password = Hash::make($request->password);
        $admin->is_super_admin = false;
        $admin->allowed_modules = array_values($request->input('modules', []));
        $admin->status = Admin::STATUS_ENABLED;

        if ($request->hasFile('image')) {
            try {
                $admin->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'));
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload image.'];
                return back()->withNotify($notify)->withInput();
            }
        }

        $admin->save();

        admin_audit_log('staff.created', "Staff created: {$admin->username}", $admin, [], [
            'username' => $admin->username,
            'email' => $admin->email,
            'modules' => $admin->allowed_modules,
        ]);

        $notify[] = ['success', 'Staff created successfully.'];
        return to_route('admin.staff.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        $pageTitle = 'Edit Staff - ' . $admin->username;
        
        // Mask sensitive data if demo user
        if ($this->shouldMaskData()) {
            $admin->email = $this->maskEmail($admin->email);
        }
        
        $modules = $this->getAssignableModules();
        return $this->dashboardPage('Edit staff', 'staff.form', [
            'staff' => $admin,
            'modules' => $modules,
        ]);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
        $current = auth('admin')->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('admins', 'email')->ignore($admin->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('admins', 'username')->ignore($admin->id)],
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'status' => 'required|in:0,1',
            'modules' => $admin->is_super_admin ? ['array'] : ['required', 'array', 'min:1'],
            'modules.*' => 'string|in:' . implode(',', $this->getAssignableModules()),
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'required|min:8|confirmed';
        }
        $request->validate($rules, [
            'modules.required' => 'At least one module (role) must be assigned to staff.',
            'modules.min' => 'At least one module (role) must be assigned to staff.',
        ]);

        // Prevent disabling the only super admin
        if ($admin->id === $current->id && (int) $request->status === Admin::STATUS_DISABLED) {
            $notify[] = ['error', 'You cannot disable your own account.'];
            return back()->withNotify($notify);
        }
        $oldValues = [
            'name' => $admin->name,
            'email' => $admin->email,
            'username' => $admin->username,
            'status' => $admin->status,
            'allowed_modules' => $admin->allowed_modules,
        ];

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->username = $request->username;
        $admin->status = (int) $request->status;
        $admin->allowed_modules = array_values($request->input('modules', []));

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            try {
                $old = $admin->image;
                $admin->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Could not upload image.'];
                return back()->withNotify($notify)->withInput();
            }
        }

        $admin->save();

        $newValues = [
            'name' => $admin->name,
            'email' => $admin->email,
            'username' => $admin->username,
            'status' => $admin->status,
            'allowed_modules' => $admin->allowed_modules,
        ];
        admin_audit_log('staff.updated', "Staff updated: {$admin->username}", $admin, $oldValues, $newValues);

        $notify[] = ['success', 'Staff updated successfully.'];
        return to_route('admin.staff.index')->withNotify($notify);
    }

    /**
     * Toggle staff status (enable/disable).
     */
    public function status($id)
    {
        $admin = Admin::findOrFail($id);
        if ($admin->id === auth('admin')->id()) {
            $notify[] = ['error', 'You cannot change your own status.'];
            return back()->withNotify($notify);
        }

        $oldStatus = $admin->status;
        $admin->status = $admin->status === Admin::STATUS_ENABLED ? Admin::STATUS_DISABLED : Admin::STATUS_ENABLED;
        $admin->save();

        admin_audit_log(
            'staff.status_toggled',
            "Staff status changed: {$admin->username} to " . ($admin->status === Admin::STATUS_ENABLED ? 'enabled' : 'disabled'),
            $admin,
            ['status' => $oldStatus],
            ['status' => $admin->status]
        );

        $notify[] = ['success', 'Status updated successfully.'];
        return back()->withNotify($notify);
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        if ($admin->id === auth('admin')->id()) {
            $notify[] = ['error', 'You cannot delete your own account.'];
            return back()->withNotify($notify);
        }

        $username = $admin->username;
        $admin->delete();

        admin_audit_log('staff.deleted', "Staff deleted: {$username}", null, ['username' => $username], []);

        $notify[] = ['success', 'Staff deleted successfully.'];
        return to_route('admin.staff.index')->withNotify($notify);
    }

    private function dashboardPage(string $title, string $view, array $data)
    {
        $admin = auth('admin')->user();
        $content = view("eden.admin-operations.{$view}", $data)->render();

        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => 'staff',
            'dashboardLogo' => (function_exists('gs') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => $admin->name,
            'avatarLetter' => strtoupper(substr($admin->name, 0, 1)),
            'content' => $content,
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }
}
