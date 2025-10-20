<div>
    <form action="{{ $action }}" method="post" autocomplete="off">
        @csrf
        @if (strtolower($method ?? 'post') !== 'post')
            @method($method)
        @endif

        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="name" class="form-label">{{ __('Name') }}&nbsp;&nbsp;<small class="text-secondary">({{ __('Must be unique') }})</small></label>
                <input type="text" class="form-control @if($errors->has('name')) is-invalid @endif" id="name" name="name" value="{{ old('name') }}" aria-describedby="name-validation" placeholder="{{ __('Room 9 – Goldorak') }}" required>
                <div id="name-validation" class="invalid-feedback">{{ $errors->first('name') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="topic" class="form-label">{{ __('Topic') }}</label>
                <input type="text" class="form-control @if($errors->has('topic')) is-invalid @endif" id="topic" name="topic" value="{{ old('topic', '/room/X/leds') }}" aria-describedby="topic-validation" placeholder="/room/9/leds" required>
                <div id="topic-validation" class="invalid-feedback">{{ $errors->first('topic') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="length" class="form-label">{{ __('Length') }}</label>
                <div class="input-group">
                    <input type="number" class="form-control @if($errors->has('length')) is-invalid @endif" id="length" name="length" value="{{ old('length') }}" aria-describedby="length-addon length-validation" placeholder="1000" min="1" step="1" required>
                    <span class="input-group-text" id="length-addon">{{ __('LEDs') }}</span>
                </div>
                <div id="length-validation" class="invalid-feedback">{{ $errors->first('length') }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-10 mx-auto">
                <label for="power_supply" class="form-label">{{ __('Power Supply') }}</label>
                <div class="input-group">
                    <input type="number" class="form-control @if($errors->has('power_supply')) is-invalid @endif" id="power_supply" name="power_supply" value="{{ old('power_supply') }}" aria-describedby="power_supply-addon power_supply-validation" placeholder="80" min="1" step="1" required>
                    <span class="input-group-text" id="power_supply-addon">{{ __('Amps') }}</span>
                </div>
                <div id="power_supply-validation" class="invalid-feedback">{{ $errors->first('power_supply') }}</div>
            </div>
        </div>

        <div class="row">
            <div class="col-10 mx-auto text-center">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
        </div>
    </form>
</div>
