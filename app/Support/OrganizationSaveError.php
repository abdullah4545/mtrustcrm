<?php
namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class OrganizationSaveError
{
    public static function response(\Throwable $e)
    {
        if ($e instanceof HttpExceptionInterface) {
            return response()->json(['status'=>false, 'message'=>$e->getMessage() ?: 'The request is not allowed.'], $e->getStatusCode());
        }
        $reference = (string) Str::uuid();
        Log::error('Organization save failed', ['reference'=>$reference, 'user_id'=>auth()->id(), 'exception'=>$e]);
        $message = 'An application error prevented saving. Ask the administrator to check the server log using the error reference.';
        if ($e instanceof QueryException) {
            $code = (int) ($e->errorInfo[1] ?? 0);
            $message = match ($code) {
                1054, 1146 => 'The database schema is missing a required table or column. Apply the CRM database updates.',
                1062 => 'This record conflicts with an existing unique value. Check for duplicate information.',
                1048, 1364 => 'A field required by the database is empty. The administrator must check the table requirements.',
                1451 => 'This contact or record is used by another record and cannot be removed yet.',
                1452 => 'A selected category, area, department or designation no longer exists. Refresh the form and select it again.',
                1406 => 'One of the submitted values exceeds its database column length.',
                1205, 1213 => 'The database is busy or another update is in progress. Please retry shortly.',
                1045, 2002, 2003, 2006 => 'The application could not connect to the database. Ask the administrator to check the database service.',
                default => 'The database could not save this organization. Ask the administrator to check the error reference.',
            };
            $detail = (string) ($e->errorInfo[2] ?? '');
            // Show only the offending schema identifier, never SQL, credentials or bindings.
            if (in_array($code, [1054,1048,1364,1406], true) && preg_match("/(?:column|field) '([A-Za-z0-9_.]+)'/i", $detail, $match)) {
                $message .= ' Field: '.$match[1].'.';
            }
        } elseif ($e instanceof \Symfony\Component\HttpFoundation\File\Exception\FileException) {
            $message = 'The visiting card could not be uploaded. Check the upload folder permissions and available disk space.';
        }
        return response()->json(['status'=>false, 'message'=>$message, 'error_reference'=>$reference], 500);
    }
}
