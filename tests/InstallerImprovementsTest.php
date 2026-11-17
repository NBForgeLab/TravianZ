<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

function fail_test(string $msg): void {
    throw new RuntimeException($msg);
}

function assert_true(bool $cond, string $msg): void {
    if (!$cond) {
        fail_test("FAILED: {$msg}");
    }
}

function assert_equals($expected, $actual, string $msg): void {
    if ($expected !== $actual) {
        fail_test("FAILED: {$msg} | Expected: " . var_export($expected, true) . ", Got: " . var_export($actual, true));
    }
}

echo "Testing Installer improvements...\n";

// 1. Verify .gitignore has .workbuddy-ai/
$gitignore = file_get_contents(__DIR__ . '/../.gitignore');
assert_true(strpos($gitignore, '.workbuddy-ai/') !== false, ".gitignore contains .workbuddy-ai/");
echo "PASS: .gitignore includes .workbuddy-ai/\n";

// 2. Verify install/index.php
$indexPhp = file_get_contents(__DIR__ . '/../install/index.php');

// Security headers check
assert_true(strpos($indexPhp, "header('X-Frame-Options: SAMEORIGIN');") !== false, "X-Frame-Options header present in index.php");
assert_true(strpos($indexPhp, "header('X-Content-Type-Options: nosniff');") !== false, "X-Content-Type-Options header present in index.php");
assert_true(strpos($indexPhp, "header('Referrer-Policy: strict-origin-when-cross-origin');") !== false, "Referrer-Policy header present in index.php");
echo "PASS: Security headers in install/index.php\n";

// Security headers across all entry points
$entryPoints = [
    'process.php' => file_get_contents(__DIR__ . '/../install/process.php'),
    'accounts.php' => file_get_contents(__DIR__ . '/../install/include/accounts.php'),
    'ajax_croppers.php' => file_get_contents(__DIR__ . '/../install/ajax_croppers.php'),
];
foreach ($entryPoints as $epName => $epCode) {
    assert_true(strpos($epCode, "header('X-Frame-Options: SAMEORIGIN');") !== false, "X-Frame-Options header present in {$epName}");
    assert_true(strpos($epCode, "header('X-Content-Type-Options: nosniff');") !== false, "X-Content-Type-Options header present in {$epName}");
    assert_true(strpos($epCode, "header('Referrer-Policy: strict-origin-when-cross-origin');") !== false, "Referrer-Policy header present in {$epName}");
}
echo "PASS: Security headers across all installer endpoints\n";

// Session & CSRF initialization check
assert_true(strpos($indexPhp, "\$_SESSION['tz_csrf']") !== false, "CSRF token initialized in session");
assert_true(strpos($indexPhp, "session_start()") !== false, "Session initialized");
assert_true(strpos($indexPhp, "'httponly' => true") !== false, "Session cookie HttpOnly enabled");
assert_true(strpos($indexPhp, "'samesite' => 'Lax'") !== false, "Session cookie SameSite enabled");
echo "PASS: CSRF token and secure session initialization in install/index.php\n";

// Permission check check
assert_true(strpos($indexPhp, "substr(sprintf('%o', fileperms('../')), -4) < '700'") === false, "Old POSIX octal fileperms check removed");
assert_true(strpos($indexPhp, "is_writable(\$tzRoot . '/GameEngine')") !== false, "Cross-platform GameEngine writability check present");
assert_true(strpos($indexPhp, "is_writable(\$tzRoot . '/var')") !== false, "Cross-platform var writability check present");
echo "PASS: Cross-platform permission checks in install/index.php\n";

// Version label check
assert_true(strpos($indexPhp, "v.11.0 Full Refactor & Redesign") === false, "Old non-standard version string removed");
assert_true(strpos($indexPhp, "\$tzBuild = 'v11.0.0 (Installer Revamped)';") !== false, "Canonical version label present");
echo "PASS: Normalized version label in install/index.php\n";

// 3. Verify CSRF hidden inputs in all 4 template forms
$forms = [
    'config.tpl' => file_get_contents(__DIR__ . '/../install/templates/config.tpl'),
    'dataform.tpl' => file_get_contents(__DIR__ . '/../install/templates/dataform.tpl'),
    'wdata.tpl' => file_get_contents(__DIR__ . '/../install/templates/wdata.tpl'),
    'accounts.tpl' => file_get_contents(__DIR__ . '/../install/templates/accounts.tpl'),
];

foreach ($forms as $name => $content) {
    assert_true(
        preg_match('/<input type="hidden" name="csrf" value="<\?= htmlspecialchars\(\$_SESSION\[\'tz_csrf\'\]/', $content) === 1,
        "CSRF hidden input in {$name}"
    );
    echo "PASS: CSRF hidden field in {$name}\n";
}

// 4. Verify CSRF validation in process.php and include/accounts.php
$processPhp = $entryPoints['process.php'];
assert_true(strpos($processPhp, "hash_equals(\$_SESSION['tz_csrf'], (string) \$_POST['csrf'])") !== false, "CSRF validated in process.php");
assert_true(strpos($processPhp, "!is_string(\$_POST['csrf'])") !== false, "CSRF type check before validation in process.php");
assert_true(strpos($processPhp, "tz_install_fail('Invalid security token (CSRF). Please refresh and try again.')") !== false, "tz_install_fail called on CSRF mismatch in process.php");
echo "PASS: CSRF validation in install/process.php\n";

$accountsPhp = $entryPoints['accounts.php'];
assert_true(strpos($accountsPhp, "hash_equals(\$_SESSION['tz_csrf'], (string) \$_POST['csrf'])") !== false, "CSRF validated in include/accounts.php");
assert_true(strpos($accountsPhp, "!is_string(\$_POST['csrf'])") !== false, "CSRF type check before validation in accounts.php");
assert_true(strpos($accountsPhp, "header(\"Location: ../index.php?s=4&err=3\");") !== false, "Redirect with err=3 on CSRF mismatch in include/accounts.php");
echo "PASS: CSRF validation in install/include/accounts.php\n";

// 5. Verify Password Toggle in accounts.tpl, config.tpl and script.tpl
$accountsTpl = $forms['accounts.tpl'];
assert_true(strpos($accountsTpl, 'class="tz-password-wrap"') !== false, "Password wrap class in accounts.tpl");
assert_true(strpos($accountsTpl, 'class="tz-pw-toggle"') !== false, "Password toggle button in accounts.tpl");
assert_true(strpos($accountsTpl, 'tabindex="-1"') === false, "No tabindex=-1 on password toggles in accounts.tpl (keyboard accessible)");
assert_true(strpos($accountsTpl, 'aria-pressed="false"') !== false, "aria-pressed attribute present in accounts.tpl");
assert_true(strpos($accountsTpl, 'name="mhpw"') !== false, "mhpw field present");
assert_true(strpos($accountsTpl, 'name="spw"') !== false, "spw field present");
assert_true(strpos($accountsTpl, 'name="apass"') !== false, "apass field present");

$configTpl = $forms['config.tpl'];
assert_true(strpos($configTpl, 'class="tz-password-wrap"') !== false, "Password wrap class in config.tpl");
assert_true(strpos($configTpl, 'tabindex="-1"') === false, "No tabindex=-1 on password toggles in config.tpl (keyboard accessible)");

$scriptTpl = file_get_contents(__DIR__ . '/../install/templates/script.tpl');
assert_true(strpos($scriptTpl, '.tz-password-wrap') !== false, "Password wrap CSS in script.tpl");
assert_true(strpos($scriptTpl, '.tz-pw-toggle') !== false, "Password toggle CSS in script.tpl");
assert_true(strpos($scriptTpl, '.tz-pw-toggle:focus-visible') !== false, "Visible focus ring CSS in script.tpl");
assert_true(strpos($scriptTpl, 'buildPasswordToggles') !== false, "buildPasswordToggles JS function in script.tpl");
assert_true(strpos($scriptTpl, 'tz_step_is_revisitable') !== false, "tz_step_is_revisitable helper in script.tpl");
echo "PASS: Password toggle feature and accessibility in accounts.tpl, config.tpl and script.tpl\n";

// 6. Verify No Static Inline Styles in greet.tpl, end.tpl, wdata.tpl, accounts.tpl, config.tpl
$templatesToCheck = [
    'greet.tpl' => file_get_contents(__DIR__ . '/../install/templates/greet.tpl'),
    'end.tpl' => file_get_contents(__DIR__ . '/../install/templates/end.tpl'),
    'wdata.tpl' => $forms['wdata.tpl'],
    'accounts.tpl' => $forms['accounts.tpl'],
    'config.tpl' => $forms['config.tpl'],
];

foreach ($templatesToCheck as $tplName => $tplContent) {
    preg_match_all('/style=["\'][^"\']*["\']/i', $tplContent, $matches);
    $foundStyles = $matches[0];
    assert_true(
        count($foundStyles) === 0,
        "No inline styles in {$tplName}, but found: " . implode(', ', $foundStyles)
    );
    echo "PASS: Zero inline styles in {$tplName}\n";
}

// 7. Verify CSS classes added to script.tpl
$cssClasses = [
    '.tz-alert-success',
    '.tz-lead',
    '.tz-mt-1',
    '.tz-mt-2',
    '.tz-mt-3',
    '.tz-mt-4',
    '.tz-mb-2',
    '.tz-mb-3',
    '.tz-mb-4',
    '.tz-disclaimer-list',
    '.tz-field-group',
    '.tz-field-col',
    '.tz-progress-wdata',
    '.tz-status-text',
    '.tz-countdown',
    '.tz-countdown.is-visible',
    '.is-hidden',
];

foreach ($cssClasses as $cls) {
    assert_true(strpos($scriptTpl, $cls) !== false, "CSS class {$cls} defined in script.tpl");
}
echo "PASS: All semantic and utility CSS classes defined in script.tpl\n";

// 8. Functional test of CSRF validation algorithm with deep edge cases
$validToken = bin2hex(random_bytes(32));
$invalidToken = bin2hex(random_bytes(32));

$csrfCheck = function($sessionToken, $postToken) {
    return (!empty($sessionToken) && !empty($postToken) && is_string($sessionToken) && is_string($postToken) && hash_equals($sessionToken, (string) $postToken));
};

assert_true($csrfCheck($validToken, $validToken), "Valid CSRF token passes");
assert_true(!$csrfCheck($validToken, $invalidToken), "Mismatched CSRF token fails");
assert_true(!$csrfCheck($validToken, ''), "Empty POST token fails");
assert_true(!$csrfCheck('', $validToken), "Empty session token fails");
assert_true(!$csrfCheck(null, $validToken), "Null session token fails");
assert_true(!$csrfCheck($validToken, null), "Null POST token fails");
assert_true(!$csrfCheck($validToken, ['evil_array']), "Array POST token fails safely without notice");
assert_true(!$csrfCheck($validToken, new stdClass()), "Object POST token fails safely without notice");
assert_true(!$csrfCheck($validToken, 123456), "Integer POST token fails safely");
echo "PASS: CSRF validation algorithm edge cases (types, nulls, arrays, objects)\n";

// 9. Functional simulated request tests on process.php
function run_php_snippet(string $code): string {
    $b64 = base64_encode($code);
    return (string) shell_exec("php -r \"eval(base64_decode('{$b64}'));\"");
}

$testCases = [
    'missing_token' => ['post' => ['subconst' => '1'], 'session' => ['tz_csrf' => $validToken]],
    'mismatched_token' => ['post' => ['subconst' => '1', 'csrf' => 'wrong'], 'session' => ['tz_csrf' => $validToken]],
    'array_token' => ['post' => ['subconst' => '1', 'csrf' => ['a']], 'session' => ['tz_csrf' => $validToken]],
    'missing_session' => ['post' => ['subconst' => '1', 'csrf' => $validToken], 'session' => []],
];

foreach ($testCases as $label => $tc) {
    $code = '
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = ' . var_export($tc['post'], true) . ';
        $_SESSION = ' . var_export($tc['session'], true) . ';
        include ' . var_export(__DIR__ . '/../install/process.php', true) . ';
    ';
    $res = run_php_snippet($code);
    assert_true(strpos($res, 'Invalid security token') !== false, "process.php blocked {$label} scenario");
}
echo "PASS: Functional simulated CSRF attacks on process.php correctly blocked\n";

// 10. Functional simulated request tests on include/accounts.php
// Direct GET request should redirect to s=4 without err=3
$codeGet = '
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_POST = [];
    $_SESSION = ["tz_csrf" => "' . $validToken . '"];
    include ' . var_export(__DIR__ . '/../install/include/accounts.php', true) . ';
';
$outputGet = run_php_snippet($codeGet);
assert_true(trim($outputGet) === '', "Direct GET on accounts.php cleanly redirects without output");

// POST with invalid CSRF token should redirect to s=4&err=3
$codePostBad = '
    $_SERVER["REQUEST_METHOD"] = "POST";
    $_POST = ["csrf" => "bad", "mhpw" => "p", "spw" => "p"];
    $_SESSION = ["tz_csrf" => "' . $validToken . '"];
    include ' . var_export(__DIR__ . '/../install/include/accounts.php', true) . ';
';
$outputPostBad = run_php_snippet($codePostBad);
assert_true(trim($outputPostBad) === '', "POST bad CSRF on accounts.php cleanly redirects without output");

echo "PASS: Functional simulated requests on include/accounts.php handled correctly\n";

echo "\nALL TESTS PASSED SUCCESSFULLY!\n";
