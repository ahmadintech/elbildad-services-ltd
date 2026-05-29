<?php

namespace App\Observers;

use App\Models\Item;
use App\Services\Zoho\ZohoItemService;
use Illuminate\Support\Facades\Log;

class ItemObserver
{
    protected ZohoItemService $itemService;

    public function __construct(ZohoItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * Handle the Item "created" event.
     */
    public function created(Item $item): void
    {
        try {
            $zohoItem = $this->itemService->createItem($item->toArray());
            if (isset($zohoItem['item_id'])) {
                $item->updateQuietly(['zoho_item_id' => $zohoItem['item_id']]);
                
                if ($item->image_path) {
                    $this->itemService->uploadImage($zohoItem['item_id'], $item->image_path);
                }
            }
        } catch (\Exception $e) {
            Log::error("Observer failed to create Item {$item->id} in Zoho: " . $e->getMessage());
        }
    }

    /**
     * Handle the Item "updated" event.
     */
    public function updated(Item $item): void
    {
        if ($item->zoho_item_id && $item->wasChanged(['name', 'description', 'rate', 'unit', 'sku', 'item_type'])) {
            try {
                $this->itemService->updateItem($item->zoho_item_id, $item->toArray());
            } catch (\Exception $e) {
                Log::error("Observer failed to update Item {$item->id} in Zoho: " . $e->getMessage());
            }
        }

        if ($item->zoho_item_id && $item->wasChanged('image_path') && $item->image_path) {
            try {
                $this->itemService->uploadImage($item->zoho_item_id, $item->image_path);
            } catch (\Exception $e) {
                Log::error("Observer failed to upload image for Item {$item->id} to Zoho: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Item "deleted" event.
     */
    public function deleted(Item $item): void
    {
        if ($item->zoho_item_id) {
            try {
                $this->itemService->deleteItem($item->zoho_item_id);
            } catch (\Exception $e) {
                Log::error("Observer failed to delete Item {$item->id} in Zoho: " . $e->getMessage());
            }
        }
    }
}
