<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
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

        User::create([
            'store_id' => $storeBandung->id,
            'name' => 'Kasir Satu',
            'username' => 'kasir1',
            'email' => 'kasir1@pos.test',
            'phone' => '081111111111',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        User::create([
            'store_id' => $storeBandung->id,
            'name' => 'Kasir Dua',
            'username' => 'kasir2',
            'email' => 'kasir2@pos.test',
            'phone' => '082222222222',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => true,
        ]);

        /*
|--------------------------------------------------------------------------
| VENDORS
|--------------------------------------------------------------------------
*/

        $vendorsBandung = [
            [
                'name' => 'PT Sumber Makmur',
                'phone' => '081300000001',
                'email' => 'sales@sumbermakmur.test',
                'address' => 'Bandung, Jawa Barat',
                'contact_name' => 'Budi Santoso',
                'contact_phone' => '082100000001',
                'is_active' => true,
            ],
            [
                'name' => 'CV Berkah Jaya',
                'phone' => '081300000002',
                'email' => 'berkahjaya@test.com',
                'address' => 'Cimahi, Jawa Barat',
                'contact_name' => 'Rudi Hartono',
                'contact_phone' => '082100000002',
                'is_active' => true,
            ],
            [
                'name' => 'PT Nusantara Sejahtera',
                'phone' => '081300000003',
                'email' => 'sales@nusantarasejahtera.test',
                'address' => 'Bandung, Jawa Barat',
                'contact_name' => 'Andi Wijaya',
                'contact_phone' => '082100000003',
                'is_active' => true,
            ],
            [
                'name' => 'CV Mitra Pangan',
                'phone' => '081300000004',
                'email' => 'mitrapangan@test.com',
                'address' => 'Kabupaten Bandung, Jawa Barat',
                'contact_name' => 'Siti Rahma',
                'contact_phone' => '082100000004',
                'is_active' => true,
            ],
            [
                'name' => 'PT Sentosa Distribusi',
                'phone' => '081300000005',
                'email' => null,
                'address' => 'Bandung, Jawa Barat',
                'contact_name' => 'Dedi Kurniawan',
                'contact_phone' => '082100000005',
                'is_active' => false,
            ],
        ];

        foreach ($vendorsBandung as $vendor) {
            Vendor::create([
                'store_id' => $storeBandung->id,
                ...$vendor,
            ]);
        }

        $vendorsGarut = [
            [
                'name' => 'CV Garut Makmur',
                'phone' => '081400000001',
                'email' => 'garutmakmur@test.com',
                'address' => 'Garut, Jawa Barat',
                'contact_name' => 'Agus Setiawan',
                'contact_phone' => '083100000001',
                'is_active' => true,
            ],
            [
                'name' => 'PT Priangan Distribusi',
                'phone' => '081400000002',
                'email' => null,
                'address' => 'Garut, Jawa Barat',
                'contact_name' => 'Yudi Permana',
                'contact_phone' => '083100000002',
                'is_active' => true,
            ],
        ];

        foreach ($vendorsGarut as $vendor) {
            Vendor::create([
                'store_id' => $storeGarut->id,
                ...$vendor,
            ]);
        }

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

        $productsData = [
            // Minuman
            ['Air Mineral 600ml', 0],
            ['Air Mineral 1.5L', 0],
            ['Teh Botol Original', 0],
            ['Teh Kotak Jasmine', 0],
            ['Kopi Susu Botol', 0],
            ['Kopi Bubuk Original', 0],
            ['Susu UHT Cokelat', 0],
            ['Susu UHT Vanilla', 0],
            ['Jus Jeruk', 0],
            ['Minuman Isotonik', 0],

            // Makanan
            ['Mie Instan Goreng', 1],
            ['Mie Instan Ayam Bawang', 1],
            ['Sarden Kaleng', 1],
            ['Kornet Sapi', 1],
            ['Roti Cokelat', 1],
            ['Roti Keju', 1],

            // Snack
            ['Keripik Kentang', 2],
            ['Keripik Singkong', 2],
            ['Biskuit Cokelat', 2],
            ['Wafer Vanilla', 2],
            ['Kacang Panggang', 2],
            ['Permen Mint', 2],
            ['Cokelat Batang', 2],
            ['Cookies Butter', 2],

            // Sembako
            ['Beras Premium 5kg', 3],
            ['Gula Pasir 1kg', 3],
            ['Minyak Goreng 1L', 3],
            ['Tepung Terigu 1kg', 3],
            ['Kecap Manis', 3],
            ['Saus Sambal', 3],

            // Kebutuhan Rumah
            ['Deterjen 800gr', 4],
            ['Pewangi Pakaian', 4],
            ['Tisu Wajah', 4],
            ['Tisu Toilet', 4],
            ['Pembersih Lantai', 4],
            ['Sabun Cuci Piring', 4],

            // Perawatan Diri
            ['Sabun Mandi', 5],
            ['Shampoo 170ml', 5],
            ['Pasta Gigi', 5],
            ['Sikat Gigi', 5],

            // Pakaian
            ['Kaos Polos Hitam M', 6],
            ['Kaos Polos Putih L', 6],
            ['Kemeja Casual', 6],
            ['Celana Pendek', 6],
            ['Kaos Kaki', 6],
            ['Topi Casual', 6],

            // Elektronik
            ['Kabel USB Type-C', 7],
            ['Charger USB 20W', 7],
            ['Earphone Kabel', 7],
            ['Lampu LED 10W', 7],
            ['Baterai AA', 7],
            ['Stop Kontak 3 Lubang', 7],
            ['Mouse Wireless', 7],
            ['Keyboard USB', 7],
        ];

        $products = collect();

        foreach ($productsData as $index => [$name, $categoryIndex]) {
            $costPrice = 3000 + (($index % 12) * 2500);
            $sellingNormal = $costPrice + 5000;
            $sellingGrocier = $sellingNormal - 2000;

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
