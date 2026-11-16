<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use App\Models\Brand;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $user = User::firstOrCreate(
                ['email' => 'asianpaints@apnifactory.com'],
                [
                    'name' => 'Asian Paints Seller',
                    'password' => Hash::make('AsianPaints@123'),
                ]
            );

            // Ensure password is set to AsianPaints@123
            $user->password = Hash::make('AsianPaints@123');
            $user->save();

            // Ensure Company exists
            $company = Company::where('user_id', $user->id)->first();
            if (!$company) {
                $company = Company::firstOrCreate(
                    ['name' => 'ASIAN PAINTS LIMITED'],
                    [
                        'user_id' => $user->id,
                        'city' => 'Mumbai',
                        'state' => 'Maharashtra',
                        'status' => '1',
                        'comission' => 0,
                    ]
                );
                if ($company->user_id != $user->id) {
                    $company->user_id = $user->id;
                    $company->save();
                }
            }

            // Ensure Brand exists
            $brand = Brand::where('user_id', $user->id)->first();
            if (!$brand) {
                $firstCat = Category::first();
                Brand::firstOrCreate(
                    ['name' => 'Asian Paints', 'user_id' => $user->id],
                    [
                        'company_id' => $company->id,
                        'category_id' => $firstCat ? $firstCat->id : 1,
                        'status' => '1',
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Log::warning('Asian Paints seed migration skipped: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Keep demo account or remove if needed
    }
};
