<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(), // Assuming UserFactory exists
            'platform' => $this->faker->randomElement(['shopify', 'woocommerce']),
            'name' => $this->faker->company,
            'store_url' => $this->faker->url,
            'api_key' => $this->faker->uuid,
            'api_secret' => $this->faker->uuid,
            'access_token' => $this->faker->uuid,
        ];
    }

    /**
     * Indicate that the store is a Shopify store.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function shopify()
    {
        return $this->state(function (array $attributes) {
            return [
                'platform' => 'shopify',
                'api_key' => null,
                'api_secret' => null,
                'access_token' => $this->faker->uuid,
            ];
        });
    }

    /**
     * Indicate that the store is a WooCommerce store.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function woocommerce()
    {
        return $this->state(function (array $attributes) {
            return [
                'platform' => 'woocommerce',
                'api_key' => $this->faker->uuid,
                'api_secret' => $this->faker->uuid,
                'access_token' => null,
            ];
        });
    }
}
