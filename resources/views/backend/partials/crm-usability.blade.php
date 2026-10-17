<style>
html body .form-control,html body .form-select,html body input:not([type=checkbox]):not([type=radio]):not([type=hidden]):not([type=button]):not([type=submit]),html body textarea,html body select,html body .select2-container .select2-selection,html body .select2-search__field,html body .choices__inner,html body .ts-control,html body .ck-editor__editable,html body .tox-tinymce,html body .note-editor{border-color:#000!important}
html body input[type=checkbox],html body input[type=radio]{border-color:#000!important}
html body .activity-entry-card .select2-container .select2-selection--single{border-color:#000!important}
html body .form-control:focus,html body .form-select:focus,html body .select2-container--focus .select2-selection{border-color:#000!important;outline:2px solid #2563eb;outline-offset:1px;box-shadow:0 0 0 3px #2563eb33!important}
html body .form-control:disabled,html body .form-select:disabled,html body .select2-container--disabled .select2-selection{border-color:#666!important;background:#eee!important;opacity:.75}
html body .is-invalid,html body .is-invalid+.select2 .select2-selection,html body .was-validated :invalid{border-color:#dc3545!important;outline-color:#dc3545}
html body .required-mark,html body .required-indicator,html body label .text-danger{color:#dc3545!important}
/* Use the document's outer scrollbar on every admin page, not a second content scroller. */
html{height:auto!important;max-height:none!important;overflow-x:hidden!important;overflow-y:auto!important}
body:not(.modal-open){height:auto!important;max-height:none!important;overflow:visible!important}
.nxl-container,.nxl-container .nxl-content,.nxl-container .main-content{height:auto!important;max-height:none!important;overflow:visible!important}
.nxl-container>.ps__rail-y,.nxl-container .nxl-content>.ps__rail-y,.nxl-container .main-content>.ps__rail-y{display:none!important}
/* Main admin/page scrollbar and the existing sidebar scrollbar. */
html,body,.nxl-container,.nxl-content,.main-content{scrollbar-width:auto;scrollbar-color:#64748b #edf2f7}
html::-webkit-scrollbar,body::-webkit-scrollbar,.nxl-container::-webkit-scrollbar,.nxl-content::-webkit-scrollbar,.main-content::-webkit-scrollbar{width:16px;height:16px;display:block}
html::-webkit-scrollbar-thumb,body::-webkit-scrollbar-thumb,.nxl-container::-webkit-scrollbar-thumb,.nxl-content::-webkit-scrollbar-thumb,.main-content::-webkit-scrollbar-thumb{background:#64748b;border:3px solid #edf2f7;border-radius:10px}
html::-webkit-scrollbar-track,body::-webkit-scrollbar-track,.nxl-container::-webkit-scrollbar-track,.nxl-content::-webkit-scrollbar-track,.main-content::-webkit-scrollbar-track{background:#edf2f7}
.nxl-navigation .navbar-content{scrollbar-width:auto!important;scrollbar-color:#64748b #edf2f7;overflow-y:auto!important}
.nxl-navigation .navbar-content::-webkit-scrollbar{display:block!important;width:14px!important}
.nxl-navigation .navbar-content::-webkit-scrollbar-thumb{background:#64748b;border:3px solid #edf2f7;border-radius:10px}
.nxl-navigation .navbar-content>.ps__rail-y{display:none!important}
/* Style native bottom bars only. Do not insert wrappers or top scrollbars. */
.table-responsive,.dataTables_wrapper,.dt-container,.dataTables_scrollBody,.dt-scroll-body,.report-table-wrapper,.tour-table-wrap{scrollbar-width:auto;scrollbar-color:#64748b #edf2f7;max-width:100%;min-width:0}
.table-responsive::-webkit-scrollbar,.dataTables_wrapper::-webkit-scrollbar,.dt-container::-webkit-scrollbar,.dataTables_scrollBody::-webkit-scrollbar,.dt-scroll-body::-webkit-scrollbar,.report-table-wrapper::-webkit-scrollbar,.tour-table-wrap::-webkit-scrollbar{height:16px}
.table-responsive::-webkit-scrollbar-thumb,.dataTables_wrapper::-webkit-scrollbar-thumb,.dt-container::-webkit-scrollbar-thumb,.dataTables_scrollBody::-webkit-scrollbar-thumb,.dt-scroll-body::-webkit-scrollbar-thumb,.report-table-wrapper::-webkit-scrollbar-thumb,.tour-table-wrap::-webkit-scrollbar-thumb{background:#64748b;border:3px solid #edf2f7;border-radius:10px}
.table-responsive::-webkit-scrollbar-track,.dataTables_wrapper::-webkit-scrollbar-track,.report-table-wrapper::-webkit-scrollbar-track,.tour-table-wrap::-webkit-scrollbar-track{background:#edf2f7}
.table-responsive,.dataTables_scrollBody,.dt-scroll-body,.report-table-wrapper,.tour-table-wrap{overflow-x:auto!important;height:auto!important;max-height:none!important;-webkit-overflow-scrolling:touch;touch-action:pan-x pan-y}
.dataTables_wrapper,.dt-container{display:block;clear:both}
.table-responsive>.dataTables_wrapper,.table-responsive>.dt-container{overflow:visible!important}
.crm-table-dragging,.crm-table-dragging *{cursor:grabbing!important;user-select:none!important}
.crm-tables-all:not(.modal-open){height:auto!important;max-height:none!important;overflow:visible!important}
.crm-tables-all .main-content,.crm-tables-all .nxl-container,.crm-tables-all .nxl-content{max-height:none!important;height:auto!important;overflow-y:visible!important}
@media(max-width:767.98px){
    .nxl-container,.main-content,.nxl-content,.card,.card-body{min-width:0}
    .dataTables_wrapper .dataTables_length,.dataTables_wrapper .dataTables_filter,.dataTables_wrapper .dataTables_info,.dataTables_wrapper .dataTables_paginate{float:none!important;max-width:100%;white-space:normal}
    .dataTables_wrapper .dataTables_filter input{max-width:100%;width:min(210px,55vw)!important}
    .dataTables_wrapper .dataTables_paginate{display:flex;flex-wrap:wrap;justify-content:center;gap:2px}
    .dataTables_wrapper .dataTables_paginate .paginate_button{margin:2px 0;padding:.4em .65em}
}
.org-address{white-space:normal;min-width:180px;max-width:360px;overflow-wrap:anywhere}
#crmAttachmentPreview img{display:block;max-width:100%;max-height:72dvh;width:auto;height:auto;object-fit:contain;margin:auto}
</style>
<div class="modal fade" id="crmAttachmentPreview" tabindex="-1" aria-labelledby="crmAttachmentTitle" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="crmAttachmentTitle">TA / DA Attachment</h5><button type="button" class="btn btn-light fs-4 py-0" data-bs-dismiss="modal" aria-label="Close">&times;</button></div><div class="modal-body"><img id="crmAttachmentImage" alt="TA or DA attachment"></div><div class="modal-footer"><button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button></div></div></div></div>
<script>
(function(){
    const interactive='a,button,input,select,textarea,label,[role=button],[contenteditable],.select2-container';
    document.addEventListener('click',function(e){
        const link=e.target.closest('a[data-crm-image]');
        if(!link)return;
        e.preventDefault();e.stopPropagation();
        document.getElementById('crmAttachmentImage').src=link.href;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('crmAttachmentPreview')).show();
    },true);
    document.getElementById('crmAttachmentPreview').addEventListener('hidden.bs.modal',function(){document.getElementById('crmAttachmentImage').removeAttribute('src');});
    let scheduled=false;
    function requiredMarks(root){
        root.querySelectorAll('label,.form-label').forEach(label=>{
            const walker=document.createTreeWalker(label,NodeFilter.SHOW_TEXT);const nodes=[];
            while(walker.nextNode()) if(walker.currentNode.textContent.includes('*') && !walker.currentNode.parentElement.closest('.required-mark,.required-indicator')) nodes.push(walker.currentNode);
            nodes.forEach(node=>{const parts=node.textContent.split('*'),frag=document.createDocumentFragment();parts.forEach((part,i)=>{if(i){const m=document.createElement('span');m.className='required-indicator';m.textContent='*';frag.append(m);}frag.append(document.createTextNode(part));});node.replaceWith(frag);});
        });
        root.querySelectorAll('input[required],select[required],textarea[required]').forEach(control=>{
            if(control.disabled || control.type==='hidden')return;
            const label=(control.id && root.querySelector('label[for="'+CSS.escape(control.id)+'"]')) || control.closest('[class*="col-"],.form-group')?.querySelector('label');
            if(label && !label.textContent.includes('*')){const m=document.createElement('span');m.className='required-indicator';m.textContent=' *';label.append(m);}
        });
    }
    function hitsText(e,element){
        const walker=document.createTreeWalker(element,NodeFilter.SHOW_TEXT);
        while(walker.nextNode()){
            if(!walker.currentNode.textContent.trim())continue;
            const range=document.createRange();range.selectNodeContents(walker.currentNode);
            if(Array.from(range.getClientRects()).some(r=>e.clientX>=r.left && e.clientX<=r.right && e.clientY>=r.top && e.clientY<=r.bottom))return true;
        }
        return false;
    }
    function addScroll(table){
        if(table.closest('.dataTables_scrollHead,.dt-scroll-head,.dataTables_scrollFoot,.dt-scroll-foot'))return;
        // Enhance ONLY a scroll container the page already owns; never reparent a table.
        const box=table.closest('.table-responsive,.dataTables_scrollBody,.dt-scroll-body,.report-table-wrapper,.tour-table-wrap');
        if(!box || box.dataset.crmDragReady)return;
        box.dataset.crmDragReady='1';
        if(!box.hasAttribute('tabindex'))box.setAttribute('tabindex','0');
        if(!box.hasAttribute('aria-label'))box.setAttribute('aria-label','Scroll table horizontally');
        let drag=null;
        const finish=()=>{
            if(drag?.moved)box.dataset.crmSuppressClick='1';
            drag=null;box.classList.remove('crm-table-dragging');
            setTimeout(()=>delete box.dataset.crmSuppressClick,0);
        };
        box.addEventListener('pointerdown',e=>{
            if(e.pointerType!=='mouse'||e.button!==0||e.target.closest(interactive)||window.getSelection()?.toString()||hitsText(e,e.target))return;
            const rect=box.getBoundingClientRect();
            if(e.clientY>=rect.top+box.clientHeight || e.clientX>=rect.left+box.clientWidth)return;
            drag={x:e.clientX,y:e.clientY,left:box.scrollLeft,moved:false};
        });
        window.addEventListener('pointermove',e=>{
            if(!drag)return;
            const dx=e.clientX-drag.x,dy=e.clientY-drag.y;
            if(!drag.moved && Math.abs(dy)>Math.abs(dx)){drag=null;return;}
            if(Math.abs(dx)>5){drag.moved=true;box.classList.add('crm-table-dragging');box.scrollLeft=drag.left-dx;e.preventDefault();}
        },{passive:false});
        window.addEventListener('pointerup',finish);
        window.addEventListener('pointercancel',finish);
        window.addEventListener('blur',finish);
        box.addEventListener('click',e=>{if(box.dataset.crmSuppressClick && !e.target.closest(interactive)){e.preventDefault();e.stopPropagation();}},true);
    }
    function scan(){scheduled=false;requiredMarks(document);document.querySelectorAll('table.table').forEach(addScroll);}
    function schedule(){if(!scheduled){scheduled=true;requestAnimationFrame(scan);}}
    new MutationObserver(schedule).observe(document.body,{childList:true,subtree:true});
    if(window.jQuery)$(document).on('draw.dt length.dt',function(){
        let all=false;if($.fn.dataTable) $.fn.dataTable.tables({api:true}).tables().every(function(){if(this.page.len()===-1)all=true;});
        document.body.classList.toggle('crm-tables-all',all);schedule();
    });
    window.addEventListener('message',e=>{if(e.origin===location.origin && e.data?.type==='crm-report-payment-updated')location.reload();});
    scan();
})();
</script>
