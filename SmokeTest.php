<?php

use PHPUnit\Framework\TestCase;

require_once _DIR_ . '/../includes/session.php';

class SmokeTest extends TestCase
{
    public function testCargaSession()
    {
        $this->assertTrue(true);
    }
}
