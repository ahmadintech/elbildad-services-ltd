<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $roleFilter = $request->input('role', '');
        $perPage = 15;

        $query = User::with(['roles', 'categories'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, function ($q) use ($roleFilter) {
                $q->role($roleFilter);
            })
            ->orderBy('name');

        $paginator = $query->paginate($perPage)->withQueryString();

        $data = $paginator->map(function ($user) {
            return [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->getRoleNames()->first() ?? 'No Role',
                'category_ids'   => $user->categories->pluck('id')->values(),
                'category_names' => $user->categories->pluck('name')->join(', '),
                'zoho_contact_id'=> $user->zoho_contact_id,
                'avatar'         => $user->avatar,
            ];
        })->values();

        $roles = Role::all()->pluck('name');
        $categories = \App\Models\Category::all(['id', 'name']);

        return Inertia::render('Admin/Users/Index', [
            'users' => [
                'data' => $data,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'from'         => $paginator->firstItem() ?? 0,
                    'to'           => $paginator->lastItem() ?? 0,
                ],
            ],
            'availableRoles'      => $roles,
            'availableCategories' => $categories,
            'filters'             => [
                'search' => $search,
                'role'   => $roleFilter,
            ],
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        if ($request->role === 'agent' && $request->has('category_ids')) {
            $user->categories()->sync($request->category_ids);
        }

        return Redirect::route('admin.users')->with('success', 'User created successfully.');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|exists:roles,name',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Sync role
        $user->syncRoles([$request->role]);

        if ($request->role === 'agent') {
            $user->categories()->sync($request->category_ids ?? []);
        } else {
            $user->categories()->detach();
        }

        return Redirect::route('admin.users')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return Redirect::route('admin.users')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return Redirect::route('admin.users')->with('success', 'User deleted successfully.');
    }
}
