
 
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function(){
    function globalSelect2Init(root){
        if(!window.jQuery || !$.fn.select2) return;
        $(root || document).find('select.form-control, select.form-select').each(function(){
            const $el=$(this);
            if($el.hasClass('no-select2') || $el.hasClass('select2-hidden-accessible') || $el.closest('.dataTables_length').length) return;
            const $modal=$el.closest('.modal');
            $el.select2({width:'100%',allowClear:!$el.prop('required'),dropdownParent:$modal.length?$modal:$(document.body)});
        });
    }
    window.globalSelect2Init=globalSelect2Init;
    $(function(){
        globalSelect2Init(document);
        $(document).on('shown.bs.modal', '.modal', function(){ globalSelect2Init(this); });
        const obs=new MutationObserver(function(ms){
            ms.forEach(m=>m.addedNodes.forEach(n=>{ if(n.nodeType===1) globalSelect2Init(n); }));
        });
        obs.observe(document.body,{childList:true,subtree:true});
    });
})();
</script>
