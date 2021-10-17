<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use \App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Storage::exists("fixtures/items.json"))
            return;
        $items = json_decode(Storage::get("fixtures/items.json"), true);
        if ($items['name'] != 'items')
            return;
        foreach ($items['data'] as $item) {
            Item::create($item);
        }
    }
}
