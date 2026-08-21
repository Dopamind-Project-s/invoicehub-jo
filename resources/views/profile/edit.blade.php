<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" dir="rtl">
                <h3 class="text-lg font-medium text-gray-900">بيانات الحساب</h3>
                <dl class="mt-4 space-y-2">
                    <div><dt class="font-semibold inline">الدور:</dt> <dd class="inline">{{ $user->roles->pluck('name')->join('، ') ?: ($user->isSuperAdmin() ? 'مدير عام' : 'مستخدم') }}</dd></div>
                    <div><dt class="font-semibold inline">المنشأة:</dt> <dd class="inline">{{ $user->company?->name_ar ?: $user->company?->legal_name_ar ?: 'غير مرتبط بمنشأة' }}</dd></div>
                </dl>
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
