<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::with([
                        'currentTraining',
                        'roles' => function ($query) {
                            $query->orderBy('name');
                        },
                     ])
                     ->withSum('points as total_points', 'points')
                     ->when($request->roles, function ($query, $roles) {
                         return $query->whereHas('roles', function (Builder $query) use ($roles) {
                             $query->whereIn('id', $roles);
                         });
                     })
                     ->when($request->name, function ($query, $name) {
                         return $query->where('firstname', 'like', '%' . $name . '%')
                                      ->orWhere('lastname', 'like', '%' . $name . '%');
                     })
                     ->orderBy('lastname')
                     ->paginate(100)
                     ->withQueryString();

        return view('users.index', [
            'roles' => Role::orderBy('name')->get(),
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user->load([
            'currentTraining',
            'courses' => function ($query) use ($user) {
                $query->where('label', 'NOT LIKE', '%annulé%')
                      ->with(['absences' => function ($query) use ($user) {
                          $query->where('user_id', $user->id);
                      }])
                      ->orderBy('label');
            },
            'roles' => function ($query) {
                $query->orderBy('name');
            },
        ]);

        $coursesDetails = $user->courses
                               ->groupBy('label')
                               ->mapWithKeys(function ($courses, $courseName) {
                                   $courseDuration = $courses->sum('duration');
                                   $absenceDuration = $courses->reduce(function ($carry, $course) {
                                       return $carry + $course->absences->reduce(function ($carry, $absence) use ($course) {
                                           $startedAt = $course->started_at->max($absence->started_at);
                                           $endedAt = $course->ended_at->min($absence->ended_at);
                                           return $carry + $startedAt->diffInRealMinutes($endedAt);
                                       });
                                   });

                                   return [$courseName => [
                                       'courses_duration' => $courseDuration,
                                       'absences_duration' => $absenceDuration,
                                   ]];
                               });

        return view('users.show', [
            'courses_details' => $coursesDetails,
            'roles' => Role::orderBy('name')->get(),
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User          $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Update the specified resource roles in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User          $user
     * @return \Illuminate\Http\Response
     */
    public function updateRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($data['roles']);

        if ($request->wantsJson()) {
            return response('Updated', 200);
        }
        return redirect()->route('users.show', $user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
