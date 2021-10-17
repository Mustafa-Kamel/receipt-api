<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use \App\Models\ItemType;

class ItemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Storage::exists("fixtures/itemtypes.json"))
            return;
        $item_types = json_decode(Storage::get("fixtures/itemtypes.json"), true);
        if ($item_types['name'] != 'itemtypes')
            return;
        foreach ($item_types['data'] as $item_type)
            ItemType::create($item_type);
    }
}
