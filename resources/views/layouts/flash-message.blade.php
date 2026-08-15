<div style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;">
    @if ($message = Session::get('success'))
        <div class="alert alert-primary alert-dismissible fade show shadow-sm" role="alert">
            <strong>{{ $message }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif ($message = Session::get('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>{{ $message }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif ($message = Session::get('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <strong>{{ $message }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            Check the following errors :
            @foreach ($errors->all() as $error)
                <br><strong>{{ $error }}</strong>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>{{ $message }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>

{{-- FOR JAVASCRIPT --}}

<div id="alert-success" class="alert alert-primary alert-dismissible fade show d-none" role="alert">
    <strong id="alert-success-message"></strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
        onclick="document.getElementById('alert-success').classList.add('d-none')"></button>
</div>

<div id="alert-danger" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
    <strong id="alert-danger-message"></strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
        onclick="document.getElementById('alert-danger').classList.add('d-none')"></button>
</div>

<script>
    // Auto hide Bootstrap alerts after 3 seconds (3000ms)
    setTimeout(function () {
        let alertNodes = document.querySelectorAll('.alert');
        alertNodes.forEach(function (alert) {
            // Bootstrap 5 fade out
            alert.classList.remove('show');
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 300); // remove from DOM after fade
        });
    }, 3000);
</script>