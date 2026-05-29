<?php
 
namespace App\Http\Controllers;
 
use App\Models\SourcingCompany;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
 
class SourcingCompanyController extends Controller
{
    /**
     * Display a listing of the sourcing companies.
     */
    public function index()
    {
        $companies = SourcingCompany::all();
 
        if (request()->wantsJson()) {
            return response()->json([
                'data' => $companies,
            ]);
        }

        return $companies;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        SourcingCompany::create($request->all());

        return Redirect::route('admin.sourcing-companies')->with('success', 'Company created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SourcingCompany $sourcingCompany): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $sourcingCompany->update($request->all());

        return Redirect::route('admin.sourcing-companies')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SourcingCompany $sourcingCompany): RedirectResponse
    {
        $sourcingCompany->delete();

        return Redirect::route('admin.sourcing-companies')->with('success', 'Company deleted successfully.');
    }

    /**
     * Display the specified sourcing company.
     */
    public function show(SourcingCompany $sourcingCompany)
    {
        if (request()->wantsJson()) {
            return response()->json([
                'data' => $sourcingCompany,
            ]);
        }

        return $sourcingCompany;
    }
}
