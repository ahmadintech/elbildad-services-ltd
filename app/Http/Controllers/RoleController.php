<?php
 
namespace App\Http\Controllers;
 
use App\Models\Role;
use App\Services\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;
 
class RoleController extends Controller
{
    /**
     * Display a listing of roles with their assigned menu and action permissions.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Roles/Index', [
            'roles' => Role::with('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get(),
            'menuDefinitions' => PermissionRegistry::getMenuDefinitions(),
            'actionDefinitions' => PermissionRegistry::getActionDefinitions(),
            'presets' => PermissionRegistry::getPresets(),
        ]);
    }

    /**
     * Store a newly created role along with specified menu visibility and action permissions.
     */
    public function store(Request $request): RedirectResponse
    {
        $normalizedName = strtolower(trim(str_replace(' ', '_', $request->name ?? '')));
        $request->merge(['name' => $normalizedName]);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $normalizedName,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions', []));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return Redirect::route('admin.roles')->with('success', "Role '{$role->name}' created successfully with specified menu and action permissions.");
    }

    /**
     * Update the specified role and sync updated menu visibility and action permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $normalizedName = strtolower(trim(str_replace(' ', '_', $request->name ?? '')));
        $request->merge(['name' => $normalizedName]);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id . ',id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Preserve name for core protected roles
        if (!in_array($role->name, ['admin', 'owner', 'super_admin'])) {
            $role->update(['name' => $normalizedName]);
        }

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions', []));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return Redirect::route('admin.roles')->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['admin', 'owner', 'super_admin'])) {
            return Redirect::route('admin.roles')->with('error', "Cannot delete core system role '{$role->name}'.");
        }

        if ($role->users()->count() > 0) {
            return Redirect::route('admin.roles')->with('error', "Cannot delete role '{$role->name}' because it is assigned to {$role->users()->count()} user(s).");
        }

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return Redirect::route('admin.roles')->with('success', "Role '{$role->name}' deleted successfully.");
    }
}
