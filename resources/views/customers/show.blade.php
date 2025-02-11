<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customer Details') }}
            </h2>
            <a href="{{ route('customers.edit', $customer) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Customer
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Company Logo -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Company Logo') }}</h3>
                        <img src="{{ $customer->logo_url }}" 
                             alt="{{ $customer->name }} Logo" 
                             class="w-48 h-48 object-contain border rounded-lg"
                             onerror="this.onerror=null; this.src='{{ url('images/default-company-logo.svg') }}';">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <span class="text-gray-500">Name:</span>
                                    <span class="ml-2">{{ $customer->name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Email:</span>
                                    <span class="ml-2">{{ $customer->email }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Phone:</span>
                                    <span class="ml-2">{{ $customer->phone ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Company:</span>
                                    <span class="ml-2">{{ $customer->company_name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <span class="text-gray-500">Address:</span>
                                    <p class="mt-1">{{ $customer->address ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Notes:</span>
                                    <p class="mt-1">{{ $customer->notes ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span class="ml-2">{{ $customer->created_at->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Last Updated:</span>
                                    <span class="ml-2">{{ $customer->updated_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900">Projects</h3>
                        <div class="mt-4">
                            @if($customer->projects->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($customer->projects as $project)
                                        <div class="border rounded-lg p-4">
                                            <h4 class="font-medium">{{ $project->name }}</h4>
                                            <p class="text-sm text-gray-500 mt-1">{{ $project->description }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">No projects found for this customer.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <x-secondary-button onclick="window.history.back()" type="button">
                            {{ __('Back') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 