<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoriesData = [
            ['name' => 'Beer', 'image' => 'images/categories/beer.png'],
            ['name' => 'Whiskey', 'image' => 'images/categories/whiskey.png'],
            ['name' => 'Wine', 'image' => 'images/categories/wine.png'],
            ['name' => 'Vodka', 'image' => 'images/categories/vodka.png'],
            ['name' => 'Rum', 'image' => 'images/categories/rum.png'],
            ['name' => 'Tequila', 'image' => 'images/categories/tequila.png'],
            ['name' => 'Gin', 'image' => 'images/categories/gin.png'],
            ['name' => 'Brandy', 'image' => 'images/categories/brandy.png'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'image' => $cat['image']]
            );
        }

        $products = [
            ['name' => "Jack Daniel's", 'category' => 'Whiskey', 'price' => 3500, 'volume' => '750ml', 'abv' => '40%', 'image' => 'images/products/jack-daniels.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
            ['name' => 'Tuborg Beer', 'category' => 'Beer', 'price' => 180, 'volume' => '650ml', 'abv' => '5%', 'image' => 'images/products/tuborg.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
            ['name' => 'Red Wine', 'category' => 'Wine', 'price' => 1200, 'volume' => '750ml', 'abv' => '13%', 'image' => 'images/products/red-wine.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
            ['name' => 'Khukri Rum', 'category' => 'Rum', 'price' => 800, 'volume' => '750ml', 'abv' => '42.8%', 'image' => 'images/products/khukri-rum.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
            ['name' => 'Absolut Vodka', 'category' => 'Vodka', 'price' => 2200, 'volume' => '750ml', 'abv' => '40%', 'image' => 'images/products/absolut.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
            ['name' => 'Old Durbar', 'category' => 'Whiskey', 'price' => 1800, 'volume' => '750ml', 'abv' => '42.8%', 'image' => 'images/products/old-durbar.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
            ['name' => 'Gorkha Beer', 'category' => 'Beer', 'price' => 200, 'volume' => '650ml', 'abv' => '5.5%', 'image' => 'images/products/gorkha.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
            ['name' => 'Chivas Regal', 'category' => 'Whiskey', 'price' => 5500, 'volume' => '750ml', 'abv' => '40%', 'image' => 'images/products/chivas.png', 'is_best_seller' => true, 'is_new_arrival' => true, 'is_hot_deal' => true],
        ];

        foreach ($products as $p) {
            $catId = $categories[$p['category']]->id ?? null;
            Product::firstOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'category_id' => $catId,
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'volume' => $p['volume'],
                    'abv' => $p['abv'],
                    'image' => $p['image'],
                    'is_best_seller' => $p['is_best_seller'],
                    'is_new_arrival' => $p['is_new_arrival'],
                    'is_hot_deal' => $p['is_hot_deal'],
                ]
            );
        }
    }
}
