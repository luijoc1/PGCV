<?php

declare(strict_types=1);

require_once __DIR__.'/BaseWebTestCase.php';

final class SessionUserTest extends BaseWebTestCase
{
    public function test_session_user_exists(): void
    {
        $this->loginAs(33);

        $this->assertTrue(
            isset($_SESSION['user'])
        );

        $this->assertEquals(
            33,
            $_SESSION['user']
        );
    }
}