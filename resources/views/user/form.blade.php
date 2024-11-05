@php($title = "New collaborateur")

@extends('layouts.app')

@section('bodyClass', 'd-flex align-items-center py-4 bg-body-tertiary')

{{--
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@endpush
--}}

@section('content')
    <main class="container text-center">
        <h1 class="mb-5">{{ __('New collaborator') }}</h1>
        <form action="{{ route('users.store') }}" method="post" class="row w-50 m-auto text-start">
            @csrf
            <div class="col-md-6 mb-4">
                <label for="firstname" class="form-label">{{ __('First name') }}</label>
                <input type="text" name="firstname" id="firstname" class="form-control">
            </div>
            <div class="col-md-6 mb-4">
                <label for="lastname" class="form-label">{{ __('Last name') }}</label>
                <input type="text" name="lastname" id="lastname" class="form-control">
            </div>
            <div class="col-md-6 mb-4">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" id="email" class="form-control">
            </div>
            <div class="col-md-6 mb-4">
                <label for="portablepro" class="form-label">{{ __('Professional Number') }}</label>
                <input type="text" name="portablepro" id="portablepro" class="form-control">
            </div>
            <p class="form-check-label">{{ __('Groups') }}</p>
            <div class="row d-flex justify-content-between align-items-baseline m-auto">
                <div class="col-md-6 mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="userGroup[]" id="userGroup" value="" class="form-check-input">
                        <label for="userGroup" class="form-check-label">{{ __('Active Directory') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="userGroup[]" id="userGroup" value="" class="form-check-input">
                        <label for="userGroup" class="form-check-label">{{ __('Active Directory') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="userGroup[]" id="userGroup" value="" class="form-check-input">
                        <label for="userGroup" class="form-check-label">{{ __('Active Directory') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="userGroup[]" id="userGroup" value="" class="form-check-input">
                        <label for="userGroup" class="form-check-label">{{ __('Active Directory') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="userGroup[]" id="userGroup" value="" class="form-check-input">
                        <label for="userGroup" class="form-check-label">{{ __('Active Directory') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="userGroup[]" id="userGroup" value="" class="form-check-input">
                        <label for="userGroup" class="form-check-label">{{ __('Active Directory') }}</label>
                    </div>
                </div>
            </div>
            <div class="row g-2 align-items-baseline">
                <div class="col-auto">
                    <label for="userOrganisation" class="form-label">{{ __('Organisation Unit') }}</label>
                </div>
                <div class="col-md-6">
                    <select name="userOrganisation" id="userOrganisation" class="form-select w-100">
                        <option selected>{{ __('Choose') }}</option>
                        <option>{{ __('Active Directory') }}</option>
                        <option>{{ __('Active Directory') }}</option>
                    </select>
                </div>
            </div>
            <div class="col-12 text-center mt-3">
                <button class="btn btn-success">{{ __('Send') }}</button>
            </div>
        </form>
    </main>
@endsection
{{--
@push('scripts')
    <script>
        document.querySelectorAll('.select').forEach((el)=>{
            let settings = {plugins: {remove_button: {title: 'Delete'}}};
            new TomSelect(el,settings);
        });
    </script>
@endpush
--}}
