<?php
require_once __DIR__ . '/BaseHttpTestCase.php';

final class PhotoUploadHttpTest extends BaseHttpTestCase
{
    private $csrf;
    private $image;

    protected function setUp(): void
    {
        parent::setUp();
        $login = $this->http('/__session', ['id' => 2]);
        $this->csrf = json_decode($login['body'], true)['csrf_token'];
        $this->image = $this->sandbox . '/fixture.png';
        $image = imagecreatetruecolor(2, 2);
        imagepng($image, $this->image);
        imagedestroy($image);
    }

    private function upload(string $route, array $post, ?string $file = null): array
    {
        $post['csrf_token'] = $this->csrf;
        $post['photo'] = new CURLFile($file ?? $this->image, 'application/x-httpd-php', '../attack.php');
        $response = $this->http('/admin/' . $route . '.php', $post, 'POST', true);
        $this->assertSame(302, $response['status']);
        return json_decode($this->http('/__state', [], 'GET')['body'], true);
    }

    /** @dataProvider entities */
    public function testRealMultipartPhotoUploadUsesActualImageTypeAndPreservesOtherData(string $table): void
    {
        file_put_contents($this->sandbox . '/images/old-photo.jpg', 'original file');
        $this->connection->exec("UPDATE $table SET photo='old-photo.jpg' WHERE id=1");
        $before = $this->snapshot();
        $result = $this->upload($table . '_photo', ['upload' => 1, 'id' => 1]);
        $this->assertArrayHasKey('success', $result);
        $filename = $this->connection->query("SELECT photo FROM $table WHERE id=1")->fetchColumn();
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\.png\z/', $filename);
        $this->assertFileExists($this->sandbox . '/images/' . $filename);
        $this->assertSame(file_get_contents($this->image), file_get_contents($this->sandbox . '/images/' . $filename));
        $expected = $before;
        $expected[$table][0]['photo'] = $filename;
        $this->assertSame($expected, $this->snapshot());
        $this->assertSame('original file', file_get_contents($this->sandbox . '/images/old-photo.jpg'));
    }

    /** @dataProvider entities */
    public function testFailedPhotoUpdateKeepsOldPhotoAndRemovesNewFile(string $table): void
    {
        file_put_contents($this->sandbox . '/images/old-photo.jpg', 'original file');
        $this->connection->exec("UPDATE $table SET photo='old-photo.jpg' WHERE id=1");
        $before = $this->snapshot();
        $this->connection->exec("CREATE TRIGGER reject_photo BEFORE UPDATE OF photo ON $table BEGIN SELECT RAISE(ABORT,'simulated photo update failure'); END");
        $result = $this->upload($table . '_photo', ['upload' => 1, 'id' => 1]);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([$this->sandbox . '/images/old-photo.jpg'], glob($this->sandbox . '/images/*'));
    }

    /** @dataProvider entities */
    public function testUnknownEntityDoesNotStorePhoto(string $table): void
    {
        $before = $this->snapshot();
        $result = $this->upload($table . '_photo', ['upload' => 1, 'id' => 999]);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], glob($this->sandbox . '/images/*'));
    }

    public function entities(): array
    {
        return ['product' => ['products'], 'user' => ['users']];
    }

    /** @dataProvider invalidFiles */
    public function testInvalidFileCannotReplacePhoto(string $contents): void
    {
        $path = $this->sandbox . '/invalid.png';
        file_put_contents($path, $contents);
        $before = $this->snapshot();
        $result = $this->upload('products_photo', ['upload' => 1, 'id' => 1], $path);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], glob($this->sandbox . '/images/*'));
    }

    public function invalidFiles(): array
    {
        return ['php' => ['<?php echo "bad";'], 'svg' => ['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>']];
    }

    /** @dataProvider creationRequests */
    public function testFailedCreationRemovesUploadedPhoto(string $table, array $post): void
    {
        $before = $this->snapshot();
        $this->connection->exec('DROP TABLE logs_' . ($table === 'products' ? 'productos' : 'usuarios'));
        $result = $this->upload($table . '_add', $post);
        $this->assertArrayHasKey('error', $result);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], glob($this->sandbox . '/images/*'));
    }

    /** @dataProvider creationRequests */
    public function testSuccessfulCreationKeepsPhotoLinkedToNewRecord(string $table, array $post): void
    {
        $result = $this->upload($table . '_add', $post);
        $this->assertArrayHasKey('success', $result);
        $filename = $this->connection->query("SELECT photo FROM $table ORDER BY id DESC LIMIT 1")->fetchColumn();
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\.png\z/', $filename);
        $this->assertFileExists($this->sandbox . '/images/' . $filename);
        $this->assertCount(1, glob($this->sandbox . '/images/*'));
    }

    public function creationRequests(): array
    {
        return [
            'product' => ['products', ['add' => 1, 'name' => 'Photo product', 'category' => '1', 'price' => '20.50', 'stock' => '2', 'stock_minimo' => '1', 'description' => 'Test']],
            'user' => ['users', ['add' => 1, 'firstname' => 'Photo', 'lastname' => 'User', 'email' => 'photo@example.com', 'password' => 'secret123', 'address' => '', 'contact' => '']],
        ];
    }
}
