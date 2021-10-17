<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountrySeeder::class);
        $this->call(ItemTypeSeeder::class);
        $this->call(ItemSeeder::class);
        $this->call(OfferSeeder::class);
    }
}
