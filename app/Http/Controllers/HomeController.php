<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Support\Renderable;

class HomeController extends Controller
{
    /**
     * Show the application landing page / dashboard.
     */
    public function index(): Renderable
    {
        $categories = Category::withCount('products')->get();
        $bestSellers = Product::with('category')->withAvailableStock()->where('is_best_seller', true)->take(8)->get();
        $newArrivals = Product::with('category')->withAvailableStock()->where('is_new_arrival', true)->latest()->take(8)->get();
        $hotDeals = Product::with('category')->withAvailableStock()->where('is_hot_deal', true)->take(8)->get();

        return view('home', compact('categories', 'bestSellers', 'newArrivals', 'hotDeals'));
    }
}
