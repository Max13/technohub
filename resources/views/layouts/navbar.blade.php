<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img class="dark:d-none" src="{{ mix('/img/logo-h_black.svg') }}" alt="ITIC Logo" height="24">
            <img class="light:d-none" src="{{ mix('/img/logo-h_white.svg') }}" alt="ITIC Logo" height="24">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {{-- Left side --}}
            <div class="navbar-nav me-auto mb-2 mb-lg-0">
                <a class="nav-link @if(Route::currentRouteName() == 'dashboard') active @endif" @if(Route::currentRouteName() == 'dashboard') aria-current="page" @endif href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                @can('viewAny', App\Models\Marking\Criterion::class)
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('Marking') }}</a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('marking/criteria.index') }}">{{ __('Criteria') }}</a>
                        </div>
                    </div>
                @endcan
                @can('viewAny', App\Models\User::class)
                    <a class="nav-link @if(Route::currentRouteName() == 'users.index') active @endif" @if(Route::currentRouteName() == 'users.index') aria-current="page" @endif href="{{ route('users.index') }}">{{ __('Users') }}</a>
                @endcan
                @can('viewAny', App\Models\Training::class)
                    <a class="nav-link @if(Route::currentRouteName() == 'trainings.index') active @endif" @if(Route::currentRouteName() == 'trainings.index') aria-current="page" @endif href="{{ route('trainings.index') }}">{{ __('My trainings') }}</a>
                @endcan
                @can('viewAny', App\Models\Exam::class)
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('Exams') }}</a>
                        <div class="dropdown-menu">
                            <a class="nav-link @if(Route::currentRouteName() == 'exams.index') active @endif" @if(Route::currentRouteName() == 'exams.index') aria-current="page" @endif href="{{ route('exams.index') }}">{{ __('My exams') }}</a>
                            <a class="nav-link @if(Route::currentRouteName() == 'exams.assignments.index') active @endif" @if(Route::currentRouteName() == 'exams.assignments.index') aria-current="page" @endif href="{{ route('exams.assignments.index') }}">{{ __('Assignments') }}</a>
                        </div>
                    </div>
                @elsecan('viewAny', App\Models\Exam\Assignment::class)
                    <a class="nav-link @if(Route::currentRouteName() == 'exams.assignments.index') active @endif" @if(Route::currentRouteName() == 'exams.assignments.index') aria-current="page" @endif href="{{ route('exams.assignments.index') }}">{{ __('Assignments') }}</a>
                @endcan
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('Miscellaneous') }}</a>
                    <div class="dropdown-menu">
                        <a class="nav-link @if(Route::currentRouteName() == 'ledstrip.index') active @endif" @if(Route::currentRouteName() == 'ledstrip.index') aria-current="page" @endif href="{{ route('ledstrip.index') }}">{{ __('LEDs') }}</a>
                        <a class="nav-link @if(Route::currentRouteName() == 'misc.crypto') active @endif" @if(Route::currentRouteName() == 'misc.crypto') aria-current="page" @endif href="{{ route('misc.crypto') }}">{{ __('Crypto') }}</a>
                    </div>
                </div>
            </div>

            @auth
            {{-- Right side --}}
            <div class="d-flex">
                <img class="avatar rounded-circle me-3" alt="Avatar" src="https://gravatar.com/avatar/{{ hash('sha256', strtolower(auth()->user()->email)) }}.jpg?s=200&d=identicon">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ auth()->user()->firstname }} {{ auth()->user()->lastname[0] }}.
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('auth.logout') }}">{{ __('Logout') }}</a>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</nav>
