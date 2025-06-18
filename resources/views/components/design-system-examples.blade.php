{{-- Component Examples for Nutriplátanos Design System --}}
{{-- Save this file as: resources/views/components/design-system-examples.blade.php --}}

<x-layouts.app title="Design System Examples">
    <div class="max-w-6xl mx-auto p-6 space-y-12">

        {{-- Header --}}
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Nutriplátanos Design System</h1>
            <p class="text-lg text-gray-600">Component examples and usage patterns</p>
        </div>

        {{-- Colors --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Brand Colors</h2>

            <div class="grid grid-cols-2 gap-8">
                {{-- Primary Colors --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Primary (Banana)</h3>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach ([50, 200, 400, 500, 700] as $shade)
                            <div class="text-center">
                                <div class="w-full h-16 bg-banana-{{ $shade }} rounded-lg mb-2"></div>
                                <span class="text-xs text-gray-600">{{ $shade }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Secondary Colors --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Secondary (Leaf)</h3>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach ([50, 200, 400, 500, 700] as $shade)
                            <div class="text-center">
                                <div class="w-full h-16 bg-leaf-{{ $shade }} rounded-lg mb-2"></div>
                                <span class="text-xs text-gray-600">{{ $shade }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Typography --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Typography</h2>

            <div class="space-y-4">
                <div class="text-6xl font-bold">Hero Heading (4XL)</div>
                <div class="text-3xl font-bold">Section Heading (3XL)</div>
                <div class="text-2xl font-bold">Page Heading (2XL)</div>
                <div class="text-xl font-semibold">Card Heading (XL)</div>
                <div class="text-lg font-medium">Large Text (LG)</div>
                <div class="text-base">Body Text (Base)</div>
                <div class="text-sm">Small Text (SM)</div>
                <div class="text-xs">Caption Text (XS)</div>
            </div>
        </section>

        {{-- Buttons --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Buttons</h2>

            <div class="space-y-6">
                {{-- Button Variants --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Variants</h3>
                    <div class="flex flex-wrap gap-4">
                        <button class="btn btn-primary">Primary</button>
                        <button class="btn btn-secondary">Secondary</button>
                        <button class="btn btn-outline">Outline</button>
                        <button class="btn btn-ghost">Ghost</button>
                        <button class="btn btn-primary" disabled>Disabled</button>
                    </div>
                </div>

                {{-- Button Sizes --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <button class="btn btn-primary btn-sm">Small</button>
                        <button class="btn btn-primary">Default</button>
                        <button class="btn btn-primary btn-lg">Large</button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Form Elements --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Form Elements</h2>

            <div class="max-w-md space-y-4">
                <div>
                    <label class="form-label" for="name">Full Name</label>
                    <input class="form-input" type="text" id="name" placeholder="Enter your full name">
                </div>

                <div>
                    <label class="form-label" for="email">Email Address</label>
                    <input class="form-input" type="email" id="email" placeholder="Enter your email">
                    <div class="form-error">Please enter a valid email address</div>
                </div>

                <div>
                    <label class="form-label" for="disabled">Disabled Field</label>
                    <input class="form-input" type="text" id="disabled" disabled value="This field is disabled">
                </div>

                <div>
                    <label class="form-label" for="select">Select Option</label>
                    <select class="form-input" id="select">
                        <option>Choose an option</option>
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                    </select>
                </div>
            </div>
        </section>

        {{-- Cards --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Cards</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Basic Card --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold">Basic Card</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-gray-600">This is a basic card with header, body, and footer sections.</p>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-sm">Action</button>
                    </div>
                </div>

                {{-- Card without Footer --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold">Simple Card</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-gray-600">This card doesn't have a footer section.</p>
                        <div class="mt-4">
                            <button class="btn btn-outline btn-sm">Learn More</button>
                        </div>
                    </div>
                </div>

                {{-- Card with Image --}}
                <div class="card overflow-hidden">
                    <div class="h-48 bg-gradient-to-br from-banana-400 to-leaf-400"></div>
                    <div class="card-body">
                        <h3 class="text-lg font-semibold mb-2">Image Card</h3>
                        <p class="text-gray-600">Cards can include images and other media content.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Status Badges --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Status Badges</h2>

            <div class="flex flex-wrap gap-4">
                <span class="badge badge-success">Success</span>
                <span class="badge badge-warning">Warning</span>
                <span class="badge badge-error">Error</span>
                <span class="badge badge-info">Info</span>
            </div>
        </section>

        {{-- Navigation --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Navigation</h2>

            <div class="card">
                <div class="card-body">
                    <nav class="space-y-1">
                        <a href="#" class="nav-link active">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                            </svg>
                            Dashboard
                        </a>
                        <a href="#" class="nav-link">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                                <path fill-rule="evenodd"
                                    d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Inventory
                        </a>
                        <a href="#" class="nav-link">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                    clip-rule="evenodd" />
                            </svg>
                            Routes
                        </a>
                        <a href="#" class="nav-link">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                    clip-rule="evenodd" />
                            </svg>
                            Sales
                        </a>
                    </nav>
                </div>
            </div>
        </section>

        {{-- Interactive Elements --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Interactive Elements</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card interactive cursor-pointer">
                    <div class="card-body">
                        <h3 class="text-lg font-semibold mb-2">Interactive Card</h3>
                        <p class="text-gray-600">This card has hover effects using the .interactive class.</p>
                    </div>
                </div>

                <div class="surface-elevated p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Elevated Surface</h3>
                    <p class="text-gray-600">This uses the .surface-elevated utility for emphasis.</p>
                </div>
            </div>
        </section>

        {{-- Utility Classes --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Utility Classes</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Brand Colors</h3>
                    <div class="space-y-2">
                        <div class="text-brand-primary font-semibold">Primary Brand Text</div>
                        <div class="text-brand-secondary font-semibold">Secondary Brand Text</div>
                        <div class="bg-brand-primary text-white px-4 py-2 rounded">Primary Background</div>
                        <div class="bg-brand-secondary text-white px-4 py-2 rounded">Secondary Background</div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Focus States</h3>
                    <div class="space-y-2">
                        <button class="focus-ring px-4 py-2 bg-gray-100 rounded">Focus Ring Button</button>
                        <input class="focus-ring form-input" placeholder="Focus ring input">
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-layouts.app>
