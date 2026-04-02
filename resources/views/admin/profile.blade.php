@extends('admin.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="px-4 py-6 max-w-7xl mx-auto">

    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">
        Profile
    </h2>

    <div class="space-y-6">
        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
            @include('profile.partials.update-password-form')
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
            @include('profile.partials.delete-user-form')
        </div>
    </div>

</div>
@endsection
