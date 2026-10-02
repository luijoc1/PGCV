<?php
require_once __DIR__ . '/BaseHttpTestCase.php';

final class ProfilePhotoTest extends BaseHttpTestCase
{
    private function update(int $id, array $changes = [], bool $upload = true): array
    {
        $login = $this->http('/__session', ['id' => $id]);
        $csrf = json_decode($login['body'], true)['csrf_token'];
        $fields = ['csrf_token' => $csrf, $id === 1 ? 'edit' : 'save' => 1, 'curr_password' => 'test-secret123', 'password' => '', 'firstname' => 'Updated', 'lastname' => 'Profile', 'email' => $id === 1 ? 'client@example.com' : 'admin@example.com', 'address' => 'Calle 1', 'contact' => '3001234567', 'id' => $id === 1 ? 2 : 1];
        if ($upload) {
            $imagePath = $this->sandbox . '/profile.png';
            $image = imagecreatetruecolor(2, 2);
            imagepng($image, $imagePath);
            imagedestroy($image);
            $fields['photo'] = new CURLFile($imagePath, 'image/png', 'profile.png');
        }
        $response = $this->http($id === 1 ? '/perfil_editar.php' : '/admin/profile_update.php', array_replace($fields, $changes), 'POST', $upload);
        $this->assertSame(302, $response['status']);
        $this->assertStringContainsString('location: ' . ($id === 1 ? 'perfil.php' : 'home.php'), strtolower($response['headers']));
        return json_decode($this->http('/__state', [], 'GET')['body'], true);
    }

    public function roles(): array { return ['customer' => [1], 'administrator' => [2]]; }

    /** @dataProvider roles */
    public function testProfilePhotoBelongsOnlyToAuthenticatedAccount(int $id): void
    {
        $before = $this->snapshot();
        $result = $this->update($id);
        $this->assertArrayHasKey('success', $result);
        $after = $this->snapshot();
        $this->assertSame($before['users'][$id === 1 ? 1 : 0], $after['users'][$id === 1 ? 1 : 0]);
        $this->assertSame($before['users'][$id - 1]['password'], $after['users'][$id - 1]['password']);
        $filename = $after['users'][$id - 1]['photo'];
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\.png\z/', $filename);
        $this->assertFileExists($this->sandbox . '/images/' . $filename);
    }

    /** @dataProvider roles */
    public function testDatabaseFailureRemovesNewPhotoAndKeepsOldProfile(int $id): void
    {
        file_put_contents($this->sandbox . '/images/old.jpg', 'original');
        $this->connection->exec("UPDATE users SET photo='old.jpg' WHERE id=$id");
        $before = $this->snapshot();
        $this->connection->exec("CREATE TRIGGER reject_profile BEFORE UPDATE ON users BEGIN SELECT RAISE(ABORT,'simulated profile failure'); END");
        $result = $this->update($id);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([$this->sandbox . '/images/old.jpg'], glob($this->sandbox . '/images/*'));
        $this->assertSame('original', file_get_contents($this->sandbox . '/images/old.jpg'));
    }

    /** @dataProvider rejectedChanges */
    public function testRejectedProfileDoesNotStorePhotoOrChangeAccount(int $id, array $changes): void
    {
        $before = $this->snapshot();
        $result = $this->update($id, $changes);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], glob($this->sandbox . '/images/*'));
    }

    public function rejectedChanges(): array
    {
        $cases = [];
        foreach ([1, 2] as $id) {
            foreach (['wrong password' => ['curr_password' => 'wrong'], 'array password' => ['curr_password' => ['bad']], 'short new password' => ['password' => 'short'], 'duplicate email' => ['email' => $id === 1 ? 'admin@example.com' : 'client@example.com'], 'invalid email' => ['email' => 'invalid']] as $name => $changes) $cases[$id . ' ' . $name] = [$id, $changes];
        }
        return $cases;
    }

    /** @dataProvider roles */
    public function testOmittingPhotoPreservesPreviousFile(int $id): void
    {
        $this->connection->exec("UPDATE users SET photo='old.jpg' WHERE id=$id");
        file_put_contents($this->sandbox . '/images/old.jpg', 'original');
        $result = $this->update($id, [], false);
        $this->assertArrayHasKey('success', $result);
        $this->assertSame('old.jpg', $this->connection->query("SELECT photo FROM users WHERE id=$id")->fetchColumn());
        $this->assertSame([$this->sandbox . '/images/old.jpg'], glob($this->sandbox . '/images/*'));
    }
}
