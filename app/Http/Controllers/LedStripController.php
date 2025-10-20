<?php

namespace App\Http\Controllers;

use App\Models\LedStrip;
use App\Services\Mqtt;
use Illuminate\Http\Request;

class LedStripController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(LedStrip::class, 'ledstrip');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('ledstrips.index', [
            'ledstrips' => LedStrip::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ledstrips.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:'.(new LedStrip)->getTable(),
            'topic' => 'required|string',
            'length' => 'required|integer|min:1',
            'power_supply' => 'required|integer|min:1',
        ]);

        LedStrip::create($data);

        return redirect()->route('ledstrip.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LedStrip      $ledstrip
     * @return \Illuminate\Http\Response
     */
    public function show(LedStrip $ledstrip, Mqtt $mqtt)
    {
        $lastMessage = $mqtt->readLast($ledstrip->topic);

        return view('ledstrips.show', [
            'ledstrip' => $ledstrip,
            'lastMessage' => $mqtt->readLast($ledstrip->topic),
            'lastValueLabel' => value(function ($lastMessage) {
                if ($lastMessage === '0') {
                    return __('Off');
                }
                if ($lastMessage === '1') {
                    return __('All white 100%');
                }
                if ($lastMessage === '*') {
                    return __('Rainbow');
                }
                if ($lastMessage[0] === '#' && in_array(strlen($lastMessage), [7, 9])) {
                    return $lastMessage;
                }
                return 'Unknown';
            }, $lastMessage),
            'lastValueHtml' => value(function ($lastMessage) {
                if ($lastMessage === '1') {
                    return '#ffffff';
                }
                if ($lastMessage[0] === '#' && in_array(strlen($lastMessage), [7, 9])) {
                    return $lastMessage;
                }
                return '#000000';
            }, $lastMessage),
        ]);
    }

    /**
     * Control/Update the color of the LED strip
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LedStrip      $ledstrip
     * @return \Illuminate\Http\Response
     */
    public function control(Request $request, Mqtt $mqtt, LedStrip $ledstrip)
    {
        $this->authorize('control', $ledstrip);

        $data = $request->validate([
            'color' => 'required|string',
        ]);

        if (
               ($data['color'][0] !== '#' || strlen($data['color']) !== 7)
            && $data['color'] !== '*'
        ) {
            return back()->withErrors([
                'color' => __('Invalid color').' '.$data['color'],
            ]);
        }

        $mqtt->publish($ledstrip->topic, $data['color'], 1, true);

        return redirect()->route('ledstrip.show', $ledstrip);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LedStrip      $ledstrip
     * @return \Illuminate\Http\Response
     */
    public function edit(LedStrip $ledstrip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LedStrip      $ledstrip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LedStrip $ledstrip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LedStrip      $ledstrip
     * @return \Illuminate\Http\Response
     */
    public function destroy(LedStrip $ledstrip)
    {
        //
    }
}
