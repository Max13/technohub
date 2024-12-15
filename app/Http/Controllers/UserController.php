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
        return view('users.show', [
            'roles' => Role::orderBy('name')->get(),
            'user' => $user->load([
                'currentTraining',
                'roles' => function ($query) {
                    $query->orderBy('name');
                },
                'trainings' => function ($query) {
                    $query->orderBy('nth_year');
                },
            ]),
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
