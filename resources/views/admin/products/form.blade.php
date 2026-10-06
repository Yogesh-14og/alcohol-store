@extends('layouts.admin')

@section('title', $product->exists ? 'Edit product' : 'Add product')

@section('content')
<div class="mb-4"><a href="{{ route('admin.products.index') }}" class="text-decoration-none">← Products</a><h1 class="h2 fw-bold mt-2">{{ $product->exists ? 'Edit product' : 'Add product' }}</h1></div>
<form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" @unless ($product->exists) data-product-create onsubmit="event.preventDefault(); event.stopImmediatePropagation(); submitProductCreate(this); return false;" @endunless>
    @csrf
    @if ($product->exists) @method('PUT') @endif
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold" for="name">Name</label><input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="slug">Slug <span class="text-muted fw-normal">(optional; generated from name if blank)</span></label><input id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $product->slug) }}">@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="category_id">Category</label><select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror"><option value="">Uncategorized</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="price">Price (NPR)</label><input id="price" name="price" type="number" min="0" step="0.01" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>@error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="cost_price">Cost price (NPR)</label><input id="cost_price" name="cost_price" type="number" min="0" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $product->cost_price) }}" @required(! $product->exists)>@error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror<p class="form-text mb-0">Your purchase cost per item; used to calculate delivered-order gross profit.</p></div>
            <div class="col-md-6">
                @if ($product->exists)
                    <label class="form-label fw-semibold">Stock quantity (on hand)</label>
                    <div class="form-control-plaintext fw-semibold">{{ $product->stock_quantity }} units</div>
                    <a href="{{ route('admin.products.stock-adjustments.index', $product) }}" class="btn btn-sm btn-outline-primary mt-1">Adjust stock / view history</a>
                @else
                    <label class="form-label fw-semibold" for="stock_quantity">Starting stock quantity</label>
                    <input id="stock_quantity" name="stock_quantity" type="number" min="0" step="1" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', 0) }}" required>
                    @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @endif
            </div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="volume">Volume</label><input id="volume" name="volume" class="form-control" value="{{ old('volume', $product->volume) }}" placeholder="750ml"></div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="abv">ABV</label><input id="abv" name="abv" class="form-control" value="{{ old('abv', $product->abv) }}" placeholder="40%"></div>
            <div class="col-12"><label class="form-label fw-semibold" for="image">Image path or URL</label><input id="image" name="image" class="form-control @error('image') is-invalid @enderror" value="{{ old('image', $product->image) }}">@error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 d-flex flex-wrap gap-4">
                @foreach (['is_best_seller' => 'Best seller', 'is_new_arrival' => 'New arrival', 'is_hot_deal' => 'Hot deal'] as $field => $label)
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" @checked(old($field, $product->{$field}))><label class="form-check-label" for="{{ $field }}">{{ $label }}</label></div>
                @endforeach
            </div>
        </div>
    </div></div>
    <div class="d-flex gap-2 mt-3"><button class="btn btn-primary" type="submit">Save product</button><a class="btn btn-outline-secondary" href="{{ route('admin.products.index') }}">Cancel</a></div>
</form>
@unless ($product->exists)
    <script>
        function submitProductCreate(form) {
            const submitButton = form.querySelector('button[type="submit"]');

            if (submitButton.disabled) {
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';

            void saveProductCreate(form, submitButton);
        }

        async function saveProductCreate(form, submitButton) {
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const responseBody = await response.json();

                if (!response.ok) {
                    const validationMessages = Object.values(responseBody.errors ?? {}).flat();
                    throw new Error(
                        validationMessages.length > 0
                            ? validationMessages.join(' ')
                            : responseBody.message ?? `Product could not be saved (HTTP ${response.status}).`,
                    );
                }

                showProductCreateMessage(responseBody.message ?? 'Product created.', 'success');
                submitButton.textContent = 'Saved successfully';
            } catch (error) {
                showProductCreateMessage(
                    error instanceof Error
                        ? error.message
                        : 'Product could not be saved. Check your connection and try again.',
                    'danger',
                );
                submitButton.disabled = false;
                submitButton.textContent = 'Save product';
            }
        }

        function showProductCreateMessage(message, type) {
            document.getElementById('productCreateMessage')?.remove();

            const alert = document.createElement('div');
            alert.id = 'productCreateMessage';
            alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow`;
            alert.setAttribute('role', type === 'success' ? 'status' : 'alert');
            alert.setAttribute('aria-live', type === 'success' ? 'polite' : 'assertive');
            alert.style.maxWidth = 'min(32rem, calc(100vw - 2rem))';
            alert.style.zIndex = '1090';
            alert.append(document.createTextNode(message));

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn-close';
            closeButton.setAttribute('aria-label', 'Close');
            closeButton.addEventListener('click', () => alert.remove());
            alert.append(closeButton);
            document.body.append(alert);

            window.setTimeout(() => alert.remove(), 6000);
        }

        document.querySelector('[data-product-create]')?.addEventListener('input', (event) => {
            const submitButton = event.currentTarget.querySelector('button[type="submit"]');

            if (submitButton.disabled) {
                submitButton.disabled = false;
                submitButton.textContent = 'Save product';
            }
        });

        document.querySelector('[data-product-create]')?.addEventListener('change', (event) => {
            const submitButton = event.currentTarget.querySelector('button[type="submit"]');

            if (submitButton.disabled) {
                submitButton.disabled = false;
                submitButton.textContent = 'Save product';
            }
        });
    </script>
@endunless
@endsection
