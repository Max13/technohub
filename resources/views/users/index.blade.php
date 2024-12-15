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
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-2 my-2">
            @foreach ($users as $user)
                <div class="col">
                    <div class="card h-100 w-100 hover:shadow">
                        <a class="text-decoration-none text-reset" href="{{ route('users.show', $user) }}">
                            <div class="card-body">
                                <p class="card-title">{{ $user->lastname }} {{ $user->firstname }}</p>

                                <ul class="list-unstyled small">
                                    @foreach($user->roles as $role)
                                        <li class="badge list-inline-item me-0 text-black" style="background-color:{{ $role->bgColor }}">{{ $role->name }}</li>
                                    @endforeach
                                </ul>

                                <div class="d-flex justify-content-between">
                                    @if ($user->currentTraining)
                                        <p class="card-text small text-muted mb-0">{{ $user->currentTraining->name }}</p>
                                    @else
                                        <p class="card-text small text-muted mb-0">&nbsp;</p>
                                    @endif
                                    <p class="card-text small text-muted mb-0">{{ $user->points->sum('points') }} {{ __('Points') }}</p>
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
