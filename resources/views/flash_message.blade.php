@if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                aria-hidden="true">×</span></button>
        @foreach ($errors->all() as $error)
            <strong>{!! $error !!}</strong><br>
        @endforeach
    </div>
@endif
@if (session()->has('success'))
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <strong>{{ session()->get('success') }}</strong>
    </div>
@endif
@if (session()->has('info'))
    <div class="alert alert-info alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <strong>{{ session()->get('info') }}</strong>
    </div>
@endif

@if (session()->has('error'))
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <strong>{{ session()->get('error') }}</strong>
    </div>
@endif
