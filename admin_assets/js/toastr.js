$(document).ready(function () {
    $(".tst1").on("click", function () {
        $.toast({
            heading: 'Welcome to my Elite admin',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg: '#ff6849',
            icon: 'info',
            hideAfter: 3000,
            stack: 6
        });

    });

    $(".tst2").on("click", function () {
        $.toast({
            heading: 'Welcome to my Elite admin',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg: '#ff6849',
            icon: 'warning',
            hideAfter: 3500,
            stack: 6
        });

    });
    $(".tst3").on("click", function () {
        $.toast({
            heading: 'Welcome to my Elite admin',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg: '#ff6849',
            icon: 'success',
            hideAfter: 3500,
            stack: 6
        });

    });

    $(".tst4").on("click", function () {
        $.toast({
            heading: 'Welcome to my Elite admin',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg: '#ff6849',
            icon: 'error',
            hideAfter: 3500

        });

    });
    // @if(Session::has('message'))
    // toastr.options =
    // {
    //     "closeButton" : true,
    //     "progressBar" : true
    // }
    //         toastr.success("{{ session('message') }}");
    // @endif
  
    // @if(Session::has('error'))
    // toastr.options =
    // {
    //     "closeButton" : true,
    //     "progressBar" : true
    // }
    //         toastr.error("{{ session('error') }}");
    // @endif
  
    // @if(Session::has('info'))
    // toastr.options =
    // {
    //     "closeButton" : true,
    //     "progressBar" : true
    // }
    //         toastr.info("{{ session('info') }}");
    // @endif
  
    // @if(Session::has('warning'))
    // toastr.options =
    // {
    //     "closeButton" : true,
    //     "progressBar" : true
    // }
    //         toastr.warning("{{ session('warning') }}");
    // @endif

});
