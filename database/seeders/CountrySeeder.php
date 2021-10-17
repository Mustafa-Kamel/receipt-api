<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use \App\Models\Country;

class CountrySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Storage::exists("fixtures/countries.json"))
            return;
        $countries = json_decode(Storage::get("fixtures/countries.json"), true);
        if ($countries['name'] != 'countries')
            return;
        foreach ($countries['data'] as $country)
            Country::create($country);
    }
}
