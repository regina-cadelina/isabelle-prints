<?php
require_once 'config/google-config.php';

echo "<h1>Google OAuth Debug</h1>";

echo "<h2>Configuration Check</h2>";
echo "<p><strong>Client ID:</strong> " . GOOGLE_CLIENT_ID . "</p>";
echo "<p><strong>Client Secret:</strong> " . substr(GOOGLE_CLIENT_SECRET, 0, 10) . "..." . "</p>";
echo "<p><strong>Redirect URI:</strong> " . GOOGLE_REDIRECT_URI . "</p>";

echo "<h2>Test Google Auth URL</h2>";
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'scope' => GOOGLE_SCOPES,
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
];

$authUrl = GOOGLE_AUTH_URL . '?' . http_build_query($params);
echo "<p><a href='{$authUrl}' target='_blank'>Test Google Login</a></p>";

echo "<h2>Current URL for Reference</h2>";
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo "<p>Current URL: {$currentUrl}</p>";
echo "<p>Make sure your redirect URI in Google Console matches exactly: " . GOOGLE_REDIRECT_URI . "</p>";

echo "<h2>Steps to Fix Google OAuth:</h2>";
echo "<ol>";
echo "<li>Go to <a href='https://console.cloud.google.com/' target='_blank'>Google Cloud Console</a></li>";
echo "<li>Select your project</li>";
echo "<li>Go to APIs & Services > Credentials</li>";
echo "<li>Click on your OAuth 2.0 Client ID</li>";
echo "<li>Make sure 'Authorized redirect URIs' contains: <code>" . GOOGLE_REDIRECT_URI . "</code></li>";
echo "<li>Make sure 'Authorized JavaScript origins' contains: <code>http://localhost</code></li>";
echo "<li>Save the changes</li>";
echo "<li>Wait 5-10 minutes for changes to take effect</li>";
echo "</ol>";
?>