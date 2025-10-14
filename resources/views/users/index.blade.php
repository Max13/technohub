@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ __('Users') }}</h1>

        <form id="filter-users" class="row ms-0 mt-4 mb-2">
            <fieldset class="col col-md-4">
                <legend class="row fs-5 fw-light">{{ __('By roles') }}</legend>
                <div class="row row-cols-1 row-cols-sm-2">
                    @foreach ($roles as $role)
                        <div class="col me-0 form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="role-{{ $role->id }}" name="roles[]" value="{{ $role->id }}" @if(in_array($role->id, request()->get('roles', []))) checked @endif>
                            <label class="form-check-label badge text-black" style="background-color:{{ $role->bgColor }}" for="role-{{ $role->id }}">{{ $role->name }}</label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
            <fieldset class="col-auto">
                <legend class="fs-5 fw-light">{{ __('By name') }}</legend>
                <input type="text" class="form-control form-control-sm" name="name" placeholder="{{ __('Type a name') }}" aria-label="{{ __('Type a name') }}" value="{{ request()->name }}" autocomplete="off">
            </fieldset>
        </form>

        <div class="row mb-4">
            <div class="col-2">
                <button form="filter-users" class="btn btn-sm btn-primary w-100" type="submit">{{ __('Filter') }}</button>
            </div>
        </div>

        {{ $users->links() }}
        <div class="row">
            @foreach ($users as $user)
                <div class="col-xl-4 col-sm-6 py-2">
                    <div class="card h-100 hover:shadow">
                        <a href="{{ route('users.show', $user) }}" class="text-dark">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md">
                                        <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                            <i class="bi bi-person p-3"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 ms-3">
                                        <h5 class="font-size-16 mb-1">{{ $user->fullname }}</h5>
                                        @foreach($user->roles as $role)
                                            <li class="badge list-inline-item me-0 text-black" style="background-color:{{ $role->bgColor }}">{{ $role->name }}</li>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-3 pt-1">
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-info-circle font-size-15 align-middle pe-2 text-primary"></i>
                                        <span class="badge badge-soft-success mb-0">{{ optional($user->currentTraining)->name ?? '–' }}</span>
                                    </p>
                                    <p class="text-muted mb-0 mt-2">
                                        <i class="bi bi-award font-size-15 align-middle pe-2 text-primary"></i>{{ $user->total_points ?? '-' }} Points
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        {{ $users->links() }}
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
