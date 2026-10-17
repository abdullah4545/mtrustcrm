<script>
(function($){
    if(!$ || !$.ajaxTransport)return;
    // Keep DataTables' All semantics without one unbounded PHP/SQL response.
    $.ajaxTransport('+json',function(options,original){
        if(options.crmAllChunk)return;
        const url=new URL(options.url,location.href);
        const isGet=/^(GET|HEAD)$/i.test(options.type||'GET');
        const params=isGet?url.searchParams:new URLSearchParams(typeof options.data==='string'?options.data:$.param(options.data||{}));
        if(params.get('length')!=='-1' || !params.has('draw'))return;
        let active=null,cancelled=false,timer=null;
        const indicators=new Map();
        function restoreIndicators(){indicators.forEach((html,el)=>{el.innerHTML=html;});indicators.clear();}
        return {
            send:function(headers,complete){
                let rows=[],offset=0,expected=null,first=null;
                const batchSize=250;
                const draw=params.get('draw');
                function progress(){
                    const api=$.fn.dataTable;
                    if(!api)return;
                    // Identify only this request's processing indicator by ajax URL.
                    (api.settings||[]).forEach(s=>{
                        const config=s.ajax||s.sAjaxSource;
                        const endpoint=typeof config==='string'?config:(config&&config.url);
                        if(!endpoint || new URL(endpoint,location.href).pathname!==url.pathname)return;
                        const el=document.getElementById(s.sTableId+'_processing');
                        if(el){if(!indicators.has(el))indicators.set(el,el.innerHTML);el.textContent='Loading all records: '+rows.length+(expected===null?'':' / '+expected)+'…';}
                    });
                }
                function fail(status,message){
                    if(cancelled)return;
                    restoreIndicators();
                    complete(status||500,'error',{text:JSON.stringify({message:message})},'Content-Type: application/json');
                    if(window.toastr)toastr.error(message);
                }
                function next(){
                    if(cancelled)return;
                    const chunk=new URLSearchParams(params.toString());
                    chunk.set('start',String(offset));chunk.set('length',String(batchSize));
                    const chunkUrl=new URL(url.href);
                    if(isGet)chunkUrl.search=chunk.toString();
                    progress();
                    active=$.ajax($.extend({},original,{
                        url:chunkUrl.href,type:options.type||'GET',dataType:'json',
                        data:isGet?undefined:chunk.toString(),
                        headers:$.extend({},options.headers||{},headers),
                        crmAllChunk:true,global:false,timeout:60000,
                        success:function(json){
                            if(cancelled)return;
                            if(!json || !Array.isArray(json.data) || !Number.isFinite(Number(json.recordsFiltered)) || json.error){fail(500,'Unable to load all records. Please reload the list or narrow the filters.');return;}
                            if(!first){first=json;expected=Number(json.recordsFiltered);}
                            if(!json.data.length && offset<expected){fail(409,'The dataset changed while loading. Please reload the list.');return;}
                            rows.push(...json.data);offset+=json.data.length;progress();
                            if(offset>=expected){
                                const result=$.extend({},first,{draw:Number(draw),data:rows,recordsFiltered:expected});
                                restoreIndicators();
                                complete(200,'success',{json:result},'Content-Type: application/json');
                            }else timer=setTimeout(next,0);
                        },
                        error:function(xhr,status){if(!cancelled)fail(xhr.status||500,status==='timeout'?'Loading a batch timed out. Please retry or narrow the filters.':'Unable to load all records. Please retry.');},
                        complete:undefined,statusCode:undefined
                    }));
                }
                next();
            },
            abort:function(){cancelled=true;clearTimeout(timer);if(active)active.abort();restoreIndicators();}
        };
    });
})(window.jQuery);
</script>
