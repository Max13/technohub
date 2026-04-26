@props(['type' => 'primary', 'message'])

<div class="toast-container position-fixed top-0 end-0 p-2">
    <div class="toast" role="alert" aria-live="@if(in_array($type, ['warning', 'danger'], true)) assertive @else polite @endif" aria-atomic="true">
        <div class="toast-header">
            <i @class([
                'me-2',
                'bi',
                'bi-chat-dots text-primary' => $type === 'primary',
                'bi-check-lg text-success' => $type === 'success',
                'bi-exclamation-circle text-warning' => $type === 'warning',
                'bi-exclamation-triangle text-danger' => $type === 'danger',
            ]) style="font-size: 1.2rem"></i>
            <strong class="me-auto">{{ __('Message') }}</strong>
            <button type="button" class="btn-close small" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">{{ $message }}</div>
    </div>
</div>
