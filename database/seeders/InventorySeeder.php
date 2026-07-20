<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('super_admin')->firstOrFail();

        $laptopCategory = Category::where(
            'asset_prefix',
            'LAP'
        )->firstOrFail();

        $projectorCategory = Category::where(
            'asset_prefix',
            'PRJ'
        )->firstOrFail();

        $inventoryService = app(
            InventoryService::class
        );

        $inventoryService->createItem(
            [
                'category_id' => $laptopCategory->id,
                'name' => 'Acer Aspire 3 Laptop',
                'brand' => 'Acer',
                'model' => 'Aspire 3',
                'description' => 'Laptop available for laboratory and academic use.',
                'minimum_stock' => 1,
                'location' => 'LabTech Office',
                'status' => 'active',
            ],
            [
                [
                    'serial_number' => 'ACER-A3-0001',
                    'condition' => 'good',
                    'availability_status' => 'available',
                ],
                [
                    'serial_number' => 'ACER-A3-0002',
                    'condition' => 'good',
                    'availability_status' => 'available',
                ],
            ],
            $admin
        );

        $inventoryService->createItem(
            [
                'category_id' => $projectorCategory->id,
                'name' => 'Epson LCD Projector',
                'brand' => 'Epson',
                'model' => 'EB-X06',
                'description' => 'Projector for classrooms and presentations.',
                'minimum_stock' => 1,
                'location' => 'LabTech Office',
                'status' => 'active',
            ],
            [
                [
                    'serial_number' => 'EPSON-X06-0001',
                    'condition' => 'excellent',
                    'availability_status' => 'available',
                ],
            ],
            $admin
        );
    }
}