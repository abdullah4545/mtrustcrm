
 
<script src="{{ asset('public/backend/vendors/js/vendors.min.js') }}"></script>
 
<script src="{{ asset('public/backend/vendors/js/daterangepicker.min.js') }}"></script>
<script src="{{ asset('public/backend/vendors/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('public/backend/vendors/js/circle-progress.min.js') }}"></script>
 
<script src="{{ asset('public/backend/js/common-init.min.js') }}"></script>
<script src="{{ asset('public/backend/js/dashboard-init.min.js') }}"></script>
 
<script src="{{ asset('public/backend/js/theme-customizer-init.min.js') }}"></script>
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>