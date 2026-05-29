<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\Zoho\ZohoItemService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ItemController extends Controller
{
    protected ZohoItemService $itemService;

    public function __construct(ZohoItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index()
    {
        $items = Item::orderBy('created_at', 'desc')->get();

        if (request()->wantsJson()) {
            return response()->json(['data' => $items]);
        }

        return Inertia::render('Admin/Finance/Items/Index', ['items' => $items]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rate' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:100',
            'item_type' => 'required|in:goods,service',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = $validated;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('items', 'public');
        }
        unset($data['image']);

        Item::create($data);

        return Redirect::back()->with('success', 'Item created and synced to Zoho.');
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rate' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:100',
            'item_type' => 'required|in:goods,service',
            'image' => 'nullable|image|max:5120',
        ]);

        $data = $validated;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('items', 'public');
        }
        unset($data['image']);

        $item->update($data);

        return Redirect::back()->with('success', 'Item updated and synced to Zoho.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        $item->delete();

        return Redirect::back()->with('success', 'Item deleted and removed from Zoho.');
    }

    public function markActive(Item $item): RedirectResponse
    {
        if ($item->zoho_item_id) {
            $this->itemService->markActive($item->zoho_item_id);
            $item->update(['is_active' => true]);
        }

        return Redirect::back()->with('success', 'Item marked active in Zoho.');
    }

    public function markInactive(Item $item): RedirectResponse
    {
        if ($item->zoho_item_id) {
            $this->itemService->markInactive($item->zoho_item_id);
            $item->update(['is_active' => false]);
        }

        return Redirect::back()->with('success', 'Item marked inactive in Zoho.');
    }

    public function sync(Item $item): RedirectResponse
    {
        try {
            if ($item->zoho_item_id) {
                return Redirect::back()->with('success', 'Item is already synced.');
            }

            $zohoItem = $this->itemService->createItem($item->toArray());
            if (isset($zohoItem['item_id'])) {
                $item->updateQuietly(['zoho_item_id' => $zohoItem['item_id']]);
                
                if ($item->image_path) {
                    $this->itemService->uploadImage($zohoItem['item_id'], $item->image_path);
                }
                
                return Redirect::back()->with('success', 'Item successfully synced to Zoho!');
            }
            
            return Redirect::back()->with('error', 'Failed to sync with Zoho. Response missing item_id.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Manual sync failed for Item {$item->id}: " . $e->getMessage());
            return Redirect::back()->with('error', 'Failed to sync with Zoho: ' . $e->getMessage());
        }
    }
}
