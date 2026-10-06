@extends('layouts.app')

@section('title', 'Terms & Conditions - Khanal Liquor')
@section('meta_description', 'Read the terms and conditions for browsing Khanal Liquor and placing orders through the website.')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="mx-auto" style="max-width: 900px;">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="text-decoration-none small">← Back</a>
        <p class="text-uppercase text-primary fw-semibold small mt-4 mb-1">Store policies</p>
        <h1 class="fw-bold mb-2">Terms &amp; Conditions</h1>
        <p class="text-muted mb-4">Last updated {{ now()->format('F j, Y') }}</p>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <p>
                    These terms describe the basic conditions for browsing Khanal Liquor and placing orders through this website.
                    By using the website or placing an order, you agree to these terms and any laws that apply where you are located.
                </p>

                <h2 class="h5 fw-bold mt-4">1. Legal eligibility</h2>
                <p>
                    You must be legally allowed to purchase and receive alcoholic beverages in your location to use the ordering
                    service. You are responsible for complying with applicable age, identity, and purchase requirements. Orders
                    must not be placed for anyone who is not legally eligible to receive them.
                </p>

                <h2 class="h5 fw-bold mt-4">2. Product information and availability</h2>
                <p>
                    Product descriptions, images, prices, and stock information are provided for general shopping purposes and may
                    be updated. We make reasonable efforts to keep listings accurate, but an item may become unavailable or a
                    listing may contain an error. We may contact you about an affected order before fulfilling it.
                </p>

                <h2 class="h5 fw-bold mt-4">3. Orders and payment</h2>
                <p>
                    Submitting an order request does not guarantee acceptance or availability. We will confirm orders through the
                    information provided at checkout. Cash on delivery orders are payable when delivered. Online Fonepay checkout
                    is not currently available on this website.
                </p>

                <h2 class="h5 fw-bold mt-4">4. Delivery</h2>
                <p>
                    Delivery depends on the address, service availability, and any applicable legal requirements. Please provide
                    accurate contact and delivery details and ensure an eligible recipient is available to receive the order.
                    Delivery estimates, where provided, are not guaranteed.
                </p>

                <h2 class="h5 fw-bold mt-4">5. Changes, cancellations, and order issues</h2>
                <p>
                    If you need to correct or ask about an order, contact the store promptly using the contact details published
                    on the website. Requests may not be possible after an order has been prepared or dispatched. Nothing in these
                    terms removes rights or remedies available under applicable law.
                </p>

                <h2 class="h5 fw-bold mt-4">6. Accounts and website use</h2>
                <p>
                    Keep your account credentials private and provide accurate account information. Do not misuse the website,
                    interfere with its operation, attempt unauthorized access, or submit unlawful or misleading information.
                </p>

                <h2 class="h5 fw-bold mt-4">7. Updates to these terms</h2>
                <p>
                    These terms may be updated as the website or its services change. The latest version will be published on this
                    page with its revision date.
                </p>

                <h2 class="h5 fw-bold mt-4">8. Contact</h2>
                <p class="mb-0">
                    For questions about these terms or an order, use the contact information displayed on the Khanal Liquor website.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
