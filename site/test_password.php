<?php
function validatePassword($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}

// Test cases
$testCases = [
    ['password' => 'short', 'expected' => false, 'reason' => 'Too short'],
    ['password' => 'nouppercase123!', 'expected' => false, 'reason' => 'No uppercase'],
    ['password' => 'NOLOWERCASE123!', 'expected' => false, 'reason' => 'No lowercase'],
    ['password' => 'NoNumbers!', 'expected' => false, 'reason' => 'No numbers'],
    ['password' => 'NoSpecial123', 'expected' => false, 'reason' => 'No special characters'],
    ['password' => 'ValidPass123!', 'expected' => true, 'reason' => 'Valid password'],
    ['password' => 'AnotherValid1@', 'expected' => true, 'reason' => 'Valid password'],
];

echo "Testing password validation function:\n\n";

$allPassed = true;
foreach ($testCases as $test) {
    $result = validatePassword($test['password']);
    $status = $result === $test['expected'] ? 'PASS' : 'FAIL';
    if ($status === 'FAIL') $allPassed = false;
    echo "Password: '{$test['password']}' - {$status} ({$test['reason']})\n";
}

echo "\n" . ($allPassed ? 'All tests passed!' : 'Some tests failed.');
?>
