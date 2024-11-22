<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show Dashboard
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function __invoke(Request $request)
    {
        if ($request->user()->is_trainer === true) {
            return app(TrainingController::class)->index($request);
        }

        return view('dashboard', [
            'user' => $request->user()
                              ->load([
                                  'currentTraining',
                                  'points',
                              ]),
            'points' => $request->user()->points()->sum('points'),
        ]);
    }
}
