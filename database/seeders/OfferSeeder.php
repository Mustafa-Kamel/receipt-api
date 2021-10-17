<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use \App\Models\Offer;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Storage::exists("fixtures/offers.json"))
            return;
        $offers = json_decode(Storage::get("fixtures/offers.json"), true);
        if ($offers['name'] != 'offers')
            return;
        foreach ($offers['data'] as $offer) {
            Offer::create($offer);
        }
    }
}
