<script>
function showOrganizationError(xhr){
    const data = xhr.responseJSON || {};
    const messages = {0:'Network connection failed. Check your connection and retry.',401:'Your login has expired. Please sign in again.',403:'You do not have permission for this organization or selected area.',404:'The organization or requested endpoint was not found.',413:'The upload exceeds the server request-size limit. Reduce the image sizes.',419:'Your session has expired. Refresh this page and sign in again before retrying.',429:'Too many requests. Please wait a moment and try again.',502:'The application server is unavailable (502). Please retry shortly.',503:'The application is temporarily unavailable (503).',504:'The server timed out (504). Check whether the organization was saved before retrying.'};
    let message;
    if(data.errors){
        message = Object.entries(data.errors).flatMap(([field, errors]) => (Array.isArray(errors) ? errors : [errors]).map(error => field + ': ' + error)).join('\n');
    } else {
        message = messages[xhr.status] || data.message || ('Unable to save. HTTP ' + xhr.status + '. Ask the administrator to check the server log.');
    }
    if(data.error_reference) message += '\nError reference: ' + data.error_reference;
    Swal.fire({title:'Unable to save', text:message, icon:'error'});
}
</script>
