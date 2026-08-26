@include('errors.layout', [
    'code' => 404,
    'title' => 'Page Not Found',
    'message' => 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
    'icon' => 'feather-search'
])