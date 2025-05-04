<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'username' => 'admin', 'password' => Hash::make('admin123'), 'role' => 'admin'],
            ['name' => 'Kasir', 'username' => 'kasir', 'password' => Hash::make('kasir123'), 'role' => 'kasir'],
        ];
        DB::table('users')->insert($users);

        $categories = [
            ['name' => 'Makanan'],
            ['name' => 'Minuman'],
            ['name' => 'Snack'],
        ];
        DB::table('categories')->insert($categories);

        $products = [
            ['name' => 'Ayam Goreng', 'price' => 15000, 'category_id' => 1],
            ['name' => 'Nasi Goreng', 'price' => 10000, 'category_id' => 1],
            ['name' => 'Es Teh', 'price' => 5000, 'category_id' => 2],
            ['name' => 'Es Jeruk', 'price' => 5000, 'category_id' => 2],
            ['name' => 'Kentang Goreng', 'price' => 10000, 'category_id' => 3],
            ['name' => 'Pisang Goreng', 'price' => 10000, 'category_id' => 3],
        ];
        DB::table('products')->insert($products);
    }
}
