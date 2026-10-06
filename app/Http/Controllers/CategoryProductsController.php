<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryProductsController extends Controller
{
    public function show(Request $request, string $category): View
    {
        $validated = $request->validate([
            'min_abv' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_abv' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $minimumAbv = isset($validated['min_abv']) ? (float) $validated['min_abv'] : null;
        $maximumAbv = isset($validated['max_abv']) ? (float) $validated['max_abv'] : null;

        if ($minimumAbv !== null && $maximumAbv !== null && $minimumAbv > $maximumAbv) {
            throw ValidationException::withMessages([
                'min_abv' => 'The minimum alcohol percentage must not exceed the maximum.',
            ]);
        }

        $category = Category::query()
            ->where('slug', $category)
            ->when(
                ctype_digit($category),
                fn (Builder $query): Builder => $query->orWhereKey($category),
            )
            ->firstOrFail();

        $products = $category->products()
            ->with('category')
            ->withAvailableStock()
            ->withinAbvRange($minimumAbv, $maximumAbv)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('categories.show', compact('category', 'products', 'minimumAbv', 'maximumAbv'));
    }
}
