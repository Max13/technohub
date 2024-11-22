@extends('layouts.app')

@section('content')
    @include('layouts.navbar')

    <main class="container py-4">
        <h1>{{ $training->name }}</h1>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4 my-4">
            @foreach ($training->students->sortBy('lastname') as $student)
                <div class="col">
                    <div class="card w-100 hover:shadow">
                        <a class="text-decoration-none text-reset" href="{{ route('students.points.create', $student) }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $student->fullname }}</h5>
                                <p class="card-text small text-muted">{{ $student->points()->sum('points') }} {{ __('Points') }}</p>
                            </div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush
