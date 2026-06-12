<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Company Settings
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <h3 class="text-lg font-semibold mb-4">Company Information</h3>

                <div class="space-y-4">

                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="text-lg">{{ $company->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Slug</p>
                        <p class="text-lg">{{ $company->slug }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Plan</p>
                        <p class="text-lg capitalize">{{ $company->plan }}</p>
                    </div>

                </div>

                <a href="{{ route('company.edit') }}"
                    class="mt-6 inline-block bg-indigo-600 text-white px-4 py-2 rounded">

                    Edit Company

                </a>

            </div>

        </div>
    </div>

</x-app-layout>