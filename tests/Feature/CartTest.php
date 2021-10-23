<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use \App\Models\Item;

class CartTest extends TestCase
{

    /**
     * Test the output of making a request with data that are not eligible for offers.
     *
     * @return void
     */
    public function testMakeCartRequestWithoutOffers()
    {
        $response = $this->postJson('/api/cart/new', json_decode(Storage::get("fixtures/test_data/cartRequest2_noOffers.json"), true));
        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'items')
            ->assertJson(json_decode(Storage::get("fixtures/test_data/cartResponse2_noOffers.json"), true));
    }

    /**
     * Test the output of making a request with data that are eligible for offers.
     *
     * @return void
     */
    public function testMakeCartRequestWithOffers()
    {
        $response = $this->postJson('/api/cart/new', json_decode(Storage::get("fixtures/test_data/cartRequest1_offers.json"), true));
        $response
            ->assertStatus(200)
            ->assertJsonCount(5, 'items')
            ->assertJson(json_decode(Storage::get("fixtures/test_data/cartResponse1_offers.json"), true));
    }

    /**
     * Test the output of making a request with data that are eligible for same offers multiple times.
     *
     * @return void
     */
    public function testMakeCartRequestWithOffersMultiple()
    {
        $response = $this->postJson('/api/cart/new', json_decode(Storage::get("fixtures/test_data/cartRequest3_offersMultiple.json"), true));
        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'items')
            ->assertJson(json_decode(Storage::get("fixtures/test_data/cartResponse3_offersMultiple.json"), true));
    }

    /**
     * Test the output of making a request with data that are eligible for same offers multiple times.
     *
     * @return void
     */
    public function testMakeCartRequestWithValidationErrors()
    {
        $response = $this->postJson('/api/cart/new', json_decode(Storage::get("fixtures/test_data/cartRequest_validationErrors.json"), true));
        $response->assertStatus(422);
    }
}
