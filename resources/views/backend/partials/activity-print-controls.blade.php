<style>
@media screen {body{font-size:11px;padding:16px;overflow:auto}.crm-print-actions{position:sticky;bottom:0;background:#fff;padding:16px;border:1px solid #333;font:14px sans-serif}.crm-print-actions button{padding:10px 20px;margin-right:10px}}
@media print {.crm-print-actions,.swal2-container{display:none!important}}
</style>
<div class="crm-print-actions"><button type="button" id="printReport">Print report</button><button type="button" onclick="window.close()">Close</button><span>Closing the print dialog does not confirm whether printing succeeded.</span><div id="printFeedback" role="status"></div></div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
    let pending=false, completed=false;
    const canPay=@json(auth()->user()->can('activity.payment.manage'));
    async function afterDialog(){
        if(!pending || completed || !canPay) return;
        pending=false;
        const answer=await Swal.fire({icon:'question',text:'Do you want to mark all records included in this printed report as Paid?',showCancelButton:true,confirmButtonText:'Yes, Mark as Paid',cancelButtonText:'Cancel'});
        if(!answer.isConfirmed) return;
        try {
            const response=await fetch(@json(route('activities.printed-payment')), {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({token:@json($printToken),confirmed:true})});
            const data=await response.json();
            if(!response.ok) throw new Error(data.message||'Unable to update payment status.');
            completed=true;
            document.getElementById('printFeedback').textContent=data.message;
            if(window.opener && !window.opener.closed) window.opener.postMessage({type:'crm-report-payment-updated'},location.origin);
            await Swal.fire('Updated',data.message,'success');
        } catch(error){ await Swal.fire('Unable to update',error.message,'error'); }
    }
    window.addEventListener('afterprint',afterDialog);
    document.getElementById('printReport').addEventListener('click',()=>{pending=true;window.print();});
    // A real HTML print surface supports afterprint; embedded PDF viewers do not.
    window.addEventListener('load',()=>{document.fonts.ready.then(()=>document.getElementById('printReport').click());});
})();
</script>
