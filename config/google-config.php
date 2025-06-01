<?php
// Google OAuth Configuration
// Make sure these match EXACTLY what's in your Google Cloud Console
define('GOOGLE_CLIENT_ID', '2304234510B-l7dg6vqrr1jpb83efue17ojemldlvnar.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/isabelle-prints/auth/google-callback.php');

// Google OAuth URLs
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_INFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// Required scopes
define('GOOGLE_SCOPES', 'openid email profile');
?>