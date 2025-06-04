<?php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', '23042345108-l7dg6vqrr1jnb83efue17ojemldlvnar.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/isabelle-prints/auth/google-callback.php');
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_SCOPES', 'email profile');

// Enable Google OAuth
define('GOOGLE_OAUTH_ENABLED', true);
?>