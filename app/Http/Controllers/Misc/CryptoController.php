<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    /**
     * Execute the single action.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return view('misc.crypto', [
            'user' => $request->user(),
            'rand_min' => ~mt_getrandmax(),
            'rand_max' => mt_getrandmax(),
        ]);
    }

    /**
     * Generate a random number using mt_rand and returns it as JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getRandomNumber(Request $request)
    {
        $seed = $request->get('seed');
        $min = $request->get('min', 0);
        $max = $request->get('max', mt_getrandmax());
        $draws = $request->get('draws');
        $results = [];

        if ($seed) {
            mt_srand($seed);
        }

        for ($i=0; $i<$draws; ++$i) {
            $results[] = mt_rand($min, $max);
        }

        return response()->json($results);
    }
}
