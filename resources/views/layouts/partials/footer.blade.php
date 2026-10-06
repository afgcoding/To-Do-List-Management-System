@if (empty($scriptsOnly))
<footer class="app-layout-footer">
    © {{ now()->year }} {{ setting('company_name', config('app.name', 'TaskFlow')) }}. Enterprise task management.
</footer>
@endif

@if (empty($chromeOnly))
<script src="{{ asset('js/vendor/modernizr.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js" integrity="sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://unpkg.com/bootstrap-material-design@4.1.1/dist/js/bootstrap-material-design.js" integrity="sha384-CauSuKpEqAFajSpkdjv3z9t8E7RlpJ1UP0lKM/+NdtSarroVKu069AlsRPKkFBz9" crossorigin="anonymous"></script>
<script>
    if (window.jQuery) {
        $(document).ready(function () {
            if (typeof $('body').bootstrapMaterialDesign === 'function') {
                $('body').bootstrapMaterialDesign();
            }
        });
    }
</script>
<script src="{{ asset('js/main.js') }}"></script>
<script>
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-modal-open],[data-modal-close]');
        if (!trigger) return;
        const modal = document.getElementById(trigger.dataset.modalOpen || trigger.dataset.modalClose);
        if (modal) {
            modal.classList.toggle('hidden', Boolean(trigger.dataset.modalClose));
            modal.classList.toggle('flex', Boolean(trigger.dataset.modalOpen));
        }
    });
    document.addEventListener('keydown', event => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            document.querySelector('[data-quick-search]')?.focus();
        }
        if (event.key === 'Escape') document.querySelectorAll('[data-modal]').forEach(modal => modal.classList.add('hidden'));
    });
</script>
@include('components.alert')
@yield('scripts')
@stack('scripts')
@endif
