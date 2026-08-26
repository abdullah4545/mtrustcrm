@include('errors.layout', [
    'code' => 503,
    'title' => 'Service Unavailable',
    'message' => 'The system is currently under maintenance. Please try again later.',
    'icon' => 'feather-tool'
])