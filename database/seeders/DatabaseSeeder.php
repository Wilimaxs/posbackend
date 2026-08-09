<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | STORES
        |--------------------------------------------------------------------------
        */

        $storeBandung = Store::create([
            'name' => 'POS Bandung',
            'address' => 'Bandung, Jawa Barat',
            'phone' => '0221234567',
            'is_active' => true,
        ]);

        $storeGarut = Store::create([
            'name' => 'POS Garut',
            'address' => 'Garut, Jawa Barat',
            'phone' => '0262234567',
            'is_active' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */

        User::create([
            'store_id' => $storeBandung->id,
            'name' => 'Owner POS',
            'username' => 'owner',
            'email' => 'owner@pos.test',
            'phone' => '081111111111',
            'password' => 'password',
            'role' => 'owner',
            'is_active' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES
        |--------------------------------------------------------------------------
        */

        $categoryNames = [
            'Minuman',
            'Makanan',
            'Snack',
            'Sembako',
            'Kebutuhan Rumah',
            'Perawatan Diri',
            'Pakaian',
            'Elektronik',
        ];

        $categories = collect();

        foreach ($categoryNames as $categoryName) {
            $categories->push(
                Category::create([
                    'name' => $categoryName,
                    'description' => "Kategori {$categoryName}.",
                    'is_active' => true,
                ])
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CUSTOMERS
        |--------------------------------------------------------------------------
        */

        $customerNames = [
            'Ahmad Fauzan',
            'Budi Santoso',
            'Citra Lestari',
            'Deni Kurniawan',
            'Eka Putri',
            'Fajar Nugraha',
            'Gina Amelia',
            'Hendra Wijaya',
            'Intan Permata',
            'Joko Susanto',
            'Kevin Ramadhan',
            'Lina Marlina',
            'Muhammad Rizky',
            'Nadia Putri',
            'Oscar Pratama',
            'Putri Anjani',
            'Rian Firmansyah',
            'Siti Nurhaliza',
            'Taufik Hidayat',
            'Vina Maharani',
        ];

        foreach ($customerNames as $index => $name) {
            Customer::create([
                'store_id' => $storeBandung->id,
                'customer_code' => sprintf('MBR-%04d', $index + 1),
                'name' => $name,
                'phone' => '0812'.str_pad(
                        (string) ($index + 1),
                        8,
                        '0',
                        STR_PAD_LEFT
                    ),
                'address' => 'Bandung, Jawa Barat',
                'notes' => null,
                'is_active' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DISCOUNTS
        |--------------------------------------------------------------------------
        */

        $discount5k = Discount::create([
            'store_id' => $storeBandung->id,
            'name' => 'Diskon Hemat 5K',
            'description' => 'Potongan Rp5.000 per item.',
            'discount_value' => 5000,
            'minimum_purchase' => 0,
            'customer_scope' => 'all',
            'starts_date' => now()->subDays(10),
            'ends_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        $discount7kMember = Discount::create([
            'store_id' => $storeBandung->id,
            'name' => 'Diskon Member 7K',
            'description' => 'Potongan Rp7.000 khusus member.',
            'discount_value' => 7000,
            'minimum_purchase' => 0,
            'customer_scope' => 'member',
            'starts_date' => now()->subDays(5),
            'ends_date' => now()->addDays(20),
            'is_active' => true,
        ]);

        $discount10k = Discount::create([
            'store_id' => $storeBandung->id,
            'name' => 'Promo 10K',
            'description' => 'Potongan Rp10.000 per item.',
            'discount_value' => 10000,
            'minimum_purchase' => 0,
            'customer_scope' => 'all',
            'starts_date' => now()->subDay(),
            'ends_date' => now()->addDays(14),
            'is_active' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */

        $productNames = [
            'Air Mineral 600ml',
            'Air Mineral 1.5L',
            'Teh Botol Original',
            'Teh Kotak Jasmine',
            'Kopi Susu Botol',
            'Kopi Bubuk Original',
            'Susu UHT Cokelat',
            'Susu UHT Vanilla',
            'Jus Jeruk',
            'Minuman Isotonik',

            'Mie Instan Goreng',
            'Mie Instan Ayam Bawang',
            'Beras Premium 5kg',
            'Gula Pasir 1kg',
            'Minyak Goreng 1L',
            'Tepung Terigu 1kg',
            'Kecap Manis',
            'Saus Sambal',
            'Sarden Kaleng',
            'Kornet Sapi',

            'Keripik Kentang',
            'Keripik Singkong',
            'Biskuit Cokelat',
            'Wafer Vanilla',
            'Kacang Panggang',
            'Permen Mint',
            'Cokelat Batang',
            'Roti Cokelat',
            'Roti Keju',
            'Cookies Butter',

            'Sabun Mandi',
            'Shampoo 170ml',
            'Pasta Gigi',
            'Sikat Gigi',
            'Deterjen 800gr',
            'Pewangi Pakaian',
            'Tisu Wajah',
            'Tisu Toilet',
            'Pembersih Lantai',
            'Sabun Cuci Piring',

            'Kaos Polos Hitam M',
            'Kaos Polos Putih L',
            'Kemeja Casual',
            'Celana Pendek',
            'Kaos Kaki',
            'Topi Casual',

            'Kabel USB Type-C',
            'Charger USB 20W',
            'Earphone Kabel',
            'Lampu LED 10W',
            'Baterai AA',
            'Stop Kontak 3 Lubang',
            'Mouse Wireless',
            'Keyboard USB',
        ];

        $products = collect();

        foreach ($productNames as $index => $name) {
            $categoryIndex = match (true) {
                $index < 10 => 0,
                $index < 20 => 1,
                $index < 30 => 2,
                $index < 40 => 5,
                $index < 46 => 6,
                default => 7,
            };

            $costPrice = 3000 + (($index % 12) * 2500);
            $sellingNormal = $costPrice + 5000;
            $sellingGrocier = $sellingNormal - 2000;

            /*
             * Seed gambar berdasarkan kategori.
             * Tujuannya hanya untuk testing mobile.
             */
            $imageSeed = match ($categoryIndex) {
                0 => 'drink',
                1 => 'food',
                2 => 'snack',
                3 => 'groceries',
                4 => 'household',
                5 => 'personal-care',
                6 => 'clothing',
                7 => 'electronics',
                default => 'product',
            };

            $products->push(
                Product::create([
                    'category_id' => $categories[$categoryIndex]->id,

                    'sku' => sprintf('PRD-%04d', $index + 1),

                    'barcode' => '899'.str_pad(
                            (string) ($index + 1),
                            10,
                            '0',
                            STR_PAD_LEFT
                        ),

                    'name' => $name,

                    /*
                     * Gambar dummy online.
                     * Tiap produk mendapat URL berbeda.
                     */
                    'img_url' =>
                        "https://picsum.photos/seed/{$imageSeed}-{$index}/500/500",

                    'description' => "Produk dummy {$name}.",

                    'cost_price' => $costPrice,
                    'selling_price_normal' => $sellingNormal,
                    'selling_price_grocier' => $sellingGrocier,

                    'unit' => 'pcs',
                    'is_active' => true,
                ])
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCT STOCKS - BANDUNG
        |--------------------------------------------------------------------------
        */

        foreach ($products as $index => $product) {
            $stock = match (true) {
                $index % 17 === 0 => 0,
                $index % 9 === 0 => 3,
                $index % 7 === 0 => 8,
                default => 20 + (($index * 7) % 100),
            };

            $discountId = match (true) {
                $index % 8 === 0 => $discount10k->id,
                $index % 5 === 0 => $discount7kMember->id,
                $index % 3 === 0 => $discount5k->id,
                default => null,
            };

            ProductStock::create([
                'store_id' => $storeBandung->id,
                'product_id' => $product->id,
                'discount_id' => $discountId,
                'stock' => $stock,
                'minimum_stock' => 10,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCT STOCKS - GARUT
        |--------------------------------------------------------------------------
        */

        foreach ($products->take(30) as $index => $product) {
            ProductStock::create([
                'store_id' => $storeGarut->id,
                'product_id' => $product->id,
                'discount_id' => null,
                'stock' => 15 + (($index * 5) % 60),
                'minimum_stock' => 10,
            ]);
        }
    }
}
