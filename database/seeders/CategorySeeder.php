<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::role('super_admin')->first();

        $categories = [
            [
                'prefix' => 'LAP',
                'name' => 'Laptop',
                'description' => 'Portable computers and laptop devices.',
            ],
            [
                'prefix' => 'PRJ',
                'name' => 'Projector',
                'description' => 'Projectors and presentation equipment.',
            ],
            [
                'prefix' => 'PER',
                'name' => 'Computer Peripheral',
                'description' => 'Mouse, keyboard, webcam, and related devices.',
            ],
            [
                'prefix' => 'CAB',
                'name' => 'Cable and Adapter',
                'description' => 'HDMI, VGA, LAN, USB, and power cables.',
            ],
            [
                'prefix' => 'NET',
                'name' => 'Networking Equipment',
                'description' => 'Routers, switches, testers, and network tools.',
            ],
            [
                'prefix' => 'AUD',
                'name' => 'Audio Equipment',
                'description' => 'Speakers, microphones, and audio accessories.',
            ],
            [
                'prefix' => 'LAB',
                'name' => 'Laboratory Tool',
                'description' => 'Tools and equipment used inside laboratories.',
            ],
        ];

        foreach ($categories as $index => $category) {
            Category::updateOrCreate(
                [
                    'name' => $category['name'],
                ],
                [
                    'category_code' => sprintf(
                        'CAT-%04d',
                        $index + 1
                    ),

                    'asset_prefix' => $category['prefix'],
                    'description' => $category['description'],
                    'status' => 'active',
                    'created_by' => $superAdmin?->id,
                    'updated_by' => $superAdmin?->id,
                ]
            );
        }
    }
}