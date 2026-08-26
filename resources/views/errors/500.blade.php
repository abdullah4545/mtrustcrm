@include('errors.layout', [
    'code' => 500,
    'title' => 'Server Error',
    'message' => 'We are working to fix the problem. Please try again in a few minutes.',
    'icon' => 'feather-alert-octagon'
])