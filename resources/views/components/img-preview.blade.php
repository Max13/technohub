<div class="modal" id="{{ $id }}" aria-label="{{ __('Illustration preview') }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body mx-auto">
                <h4 class="mb-4" data-question></h4>
                <img class="w-100" alt="{{ __('Illustration') }}" data-image>
            </div>
        </div>
    </div>
</div>

<script defer>
    const imgPreviewModal = document.getElementById('img-preview-modal');

    imgPreviewModal.addEventListener('show.bs.modal', ev => {
        imgPreviewModal.querySelector('[data-question]').innerHTML = ev.relatedTarget.dataset.question;
        imgPreviewModal.querySelector('[data-image]').src = ev.relatedTarget.src;
    });
</script>
