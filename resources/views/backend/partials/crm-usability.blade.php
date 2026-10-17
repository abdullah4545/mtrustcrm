<style>
html body .form-control,html body .form-select,html body input:not([type=checkbox]):not([type=radio]):not([type=hidden]):not([type=button]):not([type=submit]),html body textarea,html body select,html body .select2-container .select2-selection,html body .select2-search__field,html body .choices__inner,html body .ts-control,html body .ck-editor__editable,html body .tox-tinymce,html body .note-editor{border-color:#000!important}
html body input[type=checkbox],html body input[type=radio]{border-color:#000!important}
html body .activity-entry-card .select2-container .select2-selection--single{border-color:#000!important}
html body .form-control:focus,html body .form-select:focus,html body .select2-container--focus .select2-selection{border-color:#000!important;outline:2px solid #2563eb;outline-offset:1px;box-shadow:0 0 0 3px #2563eb33!important}
html body .form-control:disabled,html body .form-select:disabled,html body .select2-container--disabled .select2-selection{border-color:#666!important;background:#eee!important;opacity:.75}
html body .is-invalid,html body .is-invalid+.select2 .select2-selection,html body .was-validated :invalid{border-color:#dc3545!important;outline-color:#dc3545}
html body .required-mark,html body .required-indicator,html body label .text-danger{color:#dc3545!important}
.nxl-navigation .navbar-content{scrollbar-width:auto!important;scrollbar-color:#555 #e5e7eb;overflow-y:auto!important}
.nxl-navigation ::-webkit-scrollbar{display:block!important;width:14px!important}.nxl-navigation ::-webkit-scrollbar-thumb{background:#555;border:3px solid #e5e7eb;border-radius:10px}.nxl-navigation .navbar-content>.ps__rail-y{display:none!important}.nxl-navigation .ps__rail-y{width:14px!important;opacity:1!important}.nxl-navigation .ps__thumb-y{width:10px!important;background:#555!important}
.crm-table-scroll,.crm-table-top{scrollbar-width:auto;scrollbar-color:#555 #e5e7eb;overscroll-behavior-x:contain}
.crm-table-scroll::-webkit-scrollbar,.crm-table-top::-webkit-scrollbar{height:16px;width:14px}
.crm-table-scroll::-webkit-scrollbar-thumb,.crm-table-top::-webkit-scrollbar-thumb{background:#555;border:3px solid #e5e7eb;border-radius:10px}
.crm-table-scroll::-webkit-scrollbar-track,.crm-table-top::-webkit-scrollbar-track{background:#e5e7eb}
.crm-table-scroll .dataTables_wrapper,.crm-table-scroll .dt-container{overflow:visible!important;height:auto!important;max-height:none!important}
.crm-table-scroll{overflow-x:auto!important;max-height:none!important;height:auto!important;min-width:0;touch-action:pan-x pan-y}
.crm-table-top{overflow-x:auto;overflow-y:hidden;height:20px;min-height:20px;background:#f3f4f6}
.crm-table-top>div{height:1px}.crm-table-dragging,.crm-table-dragging *{cursor:grabbing!important;user-select:none!important}
.crm-tables-all:not(.modal-open){height:auto!important;max-height:none!important;overflow-y:auto!important}
.crm-tables-all .main-content,.crm-tables-all .nxl-container,.crm-tables-all .nxl-content,.crm-tables-all .dataTables_wrapper,.crm-tables-all .dt-container{max-height:none!important;height:auto!important;overflow-y:visible!important}
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
        if(table.closest('.select2-container,.modal') || table.closest('.dataTables_scrollHead,.dt-scroll-head,.dataTables_scrollFoot,.dt-scroll-foot'))return;
        let box=table.parentElement;
        const known=table.closest('.dataTables_scrollBody,.dt-scroll-body,.table-responsive,.tour-table-wrap,.report-table-wrap,.report-table-wrapper');
        if(known)box=known;
        if(box===document.body || box.tagName==='FORM')return;
        if(!known && !box.classList.contains('crm-table-scroll')){
            const wrapper=document.createElement('div');table.before(wrapper);wrapper.append(table);box=wrapper;
        }
        if(box.dataset.crmScroll)return;
        box.dataset.crmScroll='1';box.classList.add('crm-table-scroll');
        // Only one horizontal scroll container around each table.
        let ancestor=box.parentElement;
        while(ancestor && !ancestor.matches('.main-content,.modal-body')){
            if(ancestor.matches('.table-responsive,.dataTables_wrapper,.dt-container,.dataTables_scroll,.dt-scroll')){ancestor.style.overflow='visible';ancestor.style.maxHeight='none';ancestor.style.height='auto';}
            ancestor=ancestor.parentElement;
        }
        box.querySelectorAll('.dataTables_wrapper,.dt-container').forEach(el=>{el.style.overflow='visible';el.style.maxHeight='none';el.style.height='auto';});
        const top=document.createElement('div'),spacer=document.createElement('div');top.className='crm-table-top';top.tabIndex=0;top.setAttribute('role','region');top.setAttribute('aria-label','Scroll table horizontally');top.append(spacer);box.before(top);
        let syncing=false;
        function sync(source,target){if(syncing)return;syncing=true;target.scrollLeft=source.scrollLeft;syncing=false;}
        top.addEventListener('scroll',()=>sync(top,box));box.addEventListener('scroll',()=>sync(box,top));
        function resize(){spacer.style.width=box.scrollWidth+'px';top.style.display=box.scrollWidth>box.clientWidth+1?'block':'none';top.scrollLeft=box.scrollLeft;}
        const ro=new ResizeObserver(resize);ro.observe(box);ro.observe(table);resize();
        let drag=null;
        box.addEventListener('pointerdown',e=>{
            if(e.pointerType!=='mouse'||e.button!==0||e.target.closest(interactive)||window.getSelection()?.toString()||hitsText(e,e.target))return;
            // Keep native scrollbar thumb interaction intact.
            const rect=box.getBoundingClientRect();if(e.clientY>rect.top+box.clientHeight)return;
            drag={x:e.clientX,y:e.clientY,left:box.scrollLeft,moved:false};
        });
        window.addEventListener('pointermove',e=>{
            if(!drag)return;
            const dx=e.clientX-drag.x,dy=e.clientY-drag.y;
            if(!drag.moved && Math.abs(dy)>Math.abs(dx)){drag=null;return;}
            if(Math.abs(dx)>5){drag.moved=true;box.classList.add('crm-table-dragging');box.scrollLeft=drag.left-dx;e.preventDefault();}
        },{passive:false});
        window.addEventListener('pointerup',()=>{if(drag?.moved)box.dataset.crmSuppressClick='1';drag=null;box.classList.remove('crm-table-dragging');setTimeout(()=>delete box.dataset.crmSuppressClick,0);});
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
