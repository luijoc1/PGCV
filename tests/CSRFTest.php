<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/csrf.php';

final class CSRFTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsGeneratedAndPreservedForSession(): void
    {
        $token = generateCSRFToken();
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $token);
        $this->assertSame($token, generateCSRFToken());
        $this->assertTrue(validateCSRFToken($token));
    }

    public function testMalformedMissingAndAnotherSessionsTokensAreRejected(): void
    {
        $token = generateCSRFToken();
        foreach ([null, '', [], 123, str_repeat('g', 64)] as $value) {
            $this->assertFalse(validateCSRFToken($value));
        }
        $_SESSION = [];
        $this->assertFalse(validateCSRFToken($token));
        $_SESSION['csrf_token'] = [];
        $this->assertFalse(validateCSRFToken($token));
    }

    public function testFormsAndAjaxCanUseMatchingTokenButGetCannot(): void
    {
        $token = generateCSRFToken();
        $this->assertTrue(validCSRFRequest('POST', $token, null));
        $this->assertTrue(validCSRFRequest('POST', null, $token));
        $this->assertFalse(validCSRFRequest('POST', null, null));
        $this->assertFalse(validCSRFRequest('POST', 'wrong', $token));
        $this->assertFalse(validCSRFRequest('POST', [], $token));
        foreach (['GET','PUT','DELETE',''] as $method) {
            $this->assertFalse(validCSRFRequest($method, $token, $token));
        }
    }
}
