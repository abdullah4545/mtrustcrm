@include('errors.layout', [
    'code' => 403,
    'title' => 'Access Denied',
    'message' => 'You do not have permission to access this page. Please contact your administrator if you think this is a mistake.',
    'icon' => 'feather-lock'
])