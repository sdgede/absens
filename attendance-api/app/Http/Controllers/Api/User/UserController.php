<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     * GET /api/v1/users
     */
    public function index(Request $request)
    {
        $query = User::with(['branch', 'department', 'roles']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('role')) {
            $query->role($request->role); // Spatie role scope
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        // Append has_face_registered flag
        $users->getCollection()->transform(function ($user) {
            $user->has_face_registered = $user->faceEmbedding()->exists();
            return $user;
        });

        return response()->json($users);
    }

    /**
     * Store a newly created user in storage.
     * POST /api/v1/users
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['tenant_id'] = currentTenantId();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            // Assign role
            if ($request->filled('role')) {
                // Ensure role assignment within tenant boundary if roles are tenant-aware
                $user->assignRole($request->role);
            }

            // Auto-create leave balances untuk current year
            $leaveTypes = LeaveType::where('tenant_id', currentTenantId())->get();
            $currentYear = date('Y');

            foreach ($leaveTypes as $type) {
                LeaveBalance::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $type->id,
                    'year' => $currentYear,
                    'total_days' => $type->max_days_per_year,
                    'used_days' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load(['branch', 'department', 'roles']),
            ], 201);

        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create user', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified user.
     * GET /api/v1/users/{id}
     */
    public function show($id)
    {
        $user = User::with(['branch', 'department', 'roles'])->findOrFail($id);
        $user->has_face_registered = $user->faceEmbedding()->exists();

        return response()->json(['user' => $user]);
    }

    /**
     * Update the specified user in storage.
     * PUT /api/v1/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|string',
            'branch_id' => 'nullable|integer|exists:tenant_branches,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'employee_id' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ];

        $validated = $request->validate($rules);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load(['branch', 'department', 'roles']),
        ]);
    }

    /**
     * Remove the specified user from storage.
     * DELETE /api/v1/users/{id} -> soft delete (is_active = false)
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->update(['is_active' => false]);

        return response()->json(['message' => 'User soft deleted successfully']);
    }
}
