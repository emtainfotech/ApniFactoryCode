<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name'            => $this->faker->company,
            'user_id'         => User::factory(),
            'email'           => $this->faker->companyEmail,
            'mobile'          => $this->faker->numerify('98########'),
            'maincategory_id' => null,
            'gst'             => strtoupper($this->faker->bothify('##?????#####???')),
            'crn'             => $this->faker->numerify('CRN#####'),
            'minordervalue'   => $this->faker->numberBetween(100, 10000),
            'photo'           => null,
            'city'            => $this->faker->city,
            'state'           => $this->faker->state,
            'pincode'         => $this->faker->numerify('######'),
            'comission'       => $this->faker->numberBetween(1, 20),
            'status'          => 'active',
        ];
    }
}

