<?php

namespace App\Http\Controllers\Api\Branch;

use App\Http\Controllers\Controller;
use App\Models\TenantBranch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of branches for the current tenant.
     * GET /api/v1/branches
     */
    public function index()
    {
        $branches = TenantBranch::all(); // TenantScope automatically applies
        return response()->json(['branches' => $branches]);
    }

    /**
     * Store a newly created branch in storage.
     * POST /api/v1/branches
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'lat' => 'required|numeric|min:-90|max:90',
            'lng' => 'required|numeric|min:-180|max:180',
            'radius_meter' => 'required|numeric|min:1',
            'timezone' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['tenant_id'] = currentTenantId();

        $branch = TenantBranch::create($validated);

        return response()->json([
            'message' => 'Branch created successfully',
            'branch' => $branch
        ], 201);
    }

    /**
     * Display the specified branch.
     * GET /api/v1/branches/{id}
     */
    public function show($id)
    {
        $branch = TenantBranch::findOrFail($id);
        return response()->json(['branch' => $branch]);
    }

    /**
     * Update the specified branch in storage.
     * PUT /api/v1/branches/{id}
     */
    public function update(Request $request, $id)
    {
        $branch = TenantBranch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'lat' => 'sometimes|required|numeric|min:-90|max:90',
            'lng' => 'sometimes|required|numeric|min:-180|max:180',
            'radius_meter' => 'sometimes|required|numeric|min:1',
            'timezone' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $branch->update($validated);

        return response()->json([
            'message' => 'Branch updated successfully',
            'branch' => $branch
        ]);
    }

    /**
     * Remove the specified branch from storage.
     * DELETE /api/v1/branches/{id}
     */
    public function destroy($id)
    {
        $branch = TenantBranch::findOrFail($id);

        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully']);
    }
}
