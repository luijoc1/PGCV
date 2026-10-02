<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/image_upload.php';

final class ImageUploadTest extends TestCase
{
    private $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function file($contents): array
    {
        $path = tempnam(sys_get_temp_dir(), 'pgcv_upload_');
        $this->paths[] = $path;
        file_put_contents($path, $contents);
        return ['error' => UPLOAD_ERR_OK, 'tmp_name' => $path, 'name' => '../attack.php', 'type' => 'image/png', 'size' => 1];
    }

    public function testOptionalPhotoCanBeAbsent(): void
    {
        $this->assertNull(saveImageUpload(null));
        $this->assertNull(saveImageUpload(['error' => UPLOAD_ERR_NO_FILE]));
    }

    public function testActualImageTypeOverridesClientNameAndMime(): void
    {
        $image = imagecreatetruecolor(2, 2);
        foreach (['png' => 'imagepng', 'jpg' => 'imagejpeg', 'gif' => 'imagegif', 'webp' => 'imagewebp'] as $extension => $encoder) {
            ob_start();
            $encoder($image);
            $file = $this->file(ob_get_clean());
            $file['type'] = 'application/x-httpd-php';
            $this->assertSame($extension, validateImageUpload($file));
        }
        imagedestroy($image);
    }

    public function testRejectsPhpDisguisedAsImage(): void
    {
        $file = $this->file('<?php echo "example";');
        $file['name'] = 'photo.png';
        $this->expectException(InvalidArgumentException::class);
        validateImageUpload($file);
    }

    public function testRejectsSvg(): void
    {
        $this->expectException(InvalidArgumentException::class);
        validateImageUpload($this->file('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'));
    }

    public function testChecksActualSizeInsteadOfDeclaredSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        validateImageUpload($this->file(str_repeat('a', 5 * 1024 * 1024 + 1)));
    }

    public function testRejectsFailedUpload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        validateImageUpload(['error' => UPLOAD_ERR_PARTIAL]);
    }

    public function testRejectsMalformedUpload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        validateImageUpload(['error' => [UPLOAD_ERR_OK]]);
    }

    public function testCannotMoveAnArbitraryLocalFile(): void
    {
        $image = imagecreatetruecolor(2, 2);
        ob_start();
        imagepng($image);
        $file = $this->file(ob_get_clean());
        imagedestroy($image);
        $this->expectException(InvalidArgumentException::class);
        saveImageUpload($file);
    }
}
