<?php
namespace App\Support;

use Illuminate\Http\Request;

class OrganizationContactInput
{
    public static function prepare(Request $request): void
    {
        $contacts = $request->input('contacts', []);
        if (!is_array($contacts)) return; // Let the array validation report malformed input.
        foreach ($contacts as &$contact) {
            if (!is_array($contact)) continue;
            foreach ($contact as $key => &$value) {
                if (is_string($value)) $value = trim($value) === '' ? null : trim($value);
                if (in_array($key, ['phone_numbers', 'email_addresses'], true) && is_array($value)) {
                    $value = array_values(array_filter(array_map(fn($v) => is_string($v) ? trim($v) : $v, $value), fn($v) => $v !== null && $v !== ''));
                }
            }
            unset($value);
        }
        unset($contact);
        $request->merge(['contacts' => $contacts]);
    }

    public static function isBlank(array $contact, $image = null): bool
    {
        if ($image) return false;
        foreach (['first_name','last_name','phone','phone_two','email','address','additional_info','department_id','designation_id'] as $key) {
            if (isset($contact[$key]) && trim((string)$contact[$key]) !== '') return false;
        }
        foreach (['phone_numbers','email_addresses'] as $key) {
            foreach (($contact[$key] ?? []) as $value) {
                if ($value !== null && trim((string)$value) !== '') return false;
            }
        }
        return true; // ID/status alone are not contact information.
    }
}
