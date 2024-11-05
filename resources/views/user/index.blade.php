@php($title = "Index")

@extends('layouts.app')

@section('bodyClass', 'd-flex align-items-center py-4 bg-body-tertiary')

@section('content')
    <main class="container text-center">
        <h1 class="mb-5">ITIC Intranet</h1>
        <div class="col-6 m-auto">
            <a href="{{ route('users.index') }}" class="btn btn-primary">{{ __('Manage collaborator') }}</a>
            <a href="{{ route('users.create') }}" class="btn btn-primary">{{ __('Create new collaborator') }}</a>
        </div>
    </main>
@endsection
