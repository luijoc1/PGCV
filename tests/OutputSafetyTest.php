<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../includes/output.php';
require_once __DIR__ . '/../includes/csrf.php';

final class OutputSafetyTest extends TestCase
{
    private function document($html): DOMDocument
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $document;
    }

    public function testTextAndAttributeEscapingPreservesNamesWithoutCreatingMarkup(): void
    {
        $input = 'O\'Connor & Hijos <img src=x onerror="alert(1)">';
        $escaped = escapeHtml($input);
        $document = $this->document('<input value="' . $escaped . '"><p>' . $escaped . '</p>');
        $this->assertSame($input, $document->getElementsByTagName('input')->item(0)->getAttribute('value'));
        $this->assertSame($input, $document->getElementsByTagName('p')->item(0)->textContent);
        $this->assertSame(0, $document->getElementsByTagName('img')->length);
        $this->assertSame('Ana &amp; José', escapeHtml('Ana &amp; José'));
    }

    public function testSearchHighlightingDoesNotExposeNameOrKeywordAsMarkup(): void
    {
        $name = 'Freno <img src=x onerror="alert(1)">';
        foreach (['Freno','<img', '"', '', 'sin coincidencia'] as $keyword) {
            $document = $this->document('<p>' . highlightProductName($name, $keyword) . '</p>');
            $this->assertSame(0, $document->getElementsByTagName('img')->length);
            $this->assertSame($name, $document->getElementsByTagName('p')->item(0)->textContent);
        }
        $this->assertSame('<b>ÁRBOL</b>', highlightProductName('ÁRBOL', 'árbol'));
    }

    public function testDescriptionsKeepFormattingAndDiscardExecutableContent(): void
    {
        $html = '<p onclick="alert(1)">Motor <strong>nuevo</strong></p><ul><li>Original</li></ul><script>alert(1)</script><style>body{display:none}</style><a href="javascript:alert(1)">Detalles</a>';
        $this->assertSame('<p>Motor <strong>nuevo</strong></p><ul><li>Original</li></ul>Detalles', safeProductDescription($html));
    }

    public function testMalformedAndForeignMarkupCannotCreateActiveNodes(): void
    {
        $payloads = [
            '<svg onload="alert(1)"><script>alert(1)</script></svg><p>Seguro</p>',
            '<math><mtext><table><mglyph><style><!--</style><img src=x onerror="alert(1)"></math>',
            '<p style="background:url(javascript:alert(1))" id="x"><img src=x onerror="alert(1)">Texto</p>',
            '<iframe srcdoc="<script>alert(1)</script>"></iframe><form action="bad"><input autofocus onfocus="alert(1)"></form>',
            '<table><tr><td onclick="alert(1)">Contenido</td></tr></table>',
        ];
        foreach ($payloads as $payload) {
            $safe = safeProductDescription($payload);
            $document = $this->document($safe);
            foreach (['script','style','svg','math','img','iframe','form','input'] as $tag) {
                $this->assertSame(0, $document->getElementsByTagName($tag)->length);
            }
            foreach ($document->getElementsByTagName('*') as $node) {
                $this->assertSame(0, $node->attributes->length);
            }
            $this->assertSame($safe, safeProductDescription($safe));
        }
    }

    public function testImageUrlsCannotEscapeTheirDirectoryOrAttribute(): void
    {
        $this->assertSame('images/noimage.jpg', safeImageUrl('../config.php'));
        $this->assertSame('images/noimage.jpg', safeImageUrl('..\\config.php'));
        $this->assertSame('../images/profile.jpg', safeImageUrl(null, '../images/', 'profile.jpg'));
        $this->assertSame('images/foto%20%22%20onerror%3D%22x.jpg', safeImageUrl('foto " onerror="x.jpg'));
    }

    public function testActualProfileTemplateEscapesValuesAndTextareaContents(): void
    {
        $_SESSION = [];
        $payload = '\"><img src=x onerror="alert(1)">';
        $address = '</textarea><script>alert(1)</script>';
        $user = ['firstname' => $payload, 'lastname' => 'Pérez', 'email' => 'test@example.com', 'password' => 'private-password-hash', 'contact_info' => $payload, 'address' => $address];
        ob_start();
        include __DIR__ . '/../includes/profile_modal.php';
        $html = ob_get_clean();
        $this->assertStringNotContainsString('private-password-hash', $html);
        $document = $this->document($html);
        $xpath = new DOMXPath($document);
        $this->assertSame($payload, $xpath->query('//input[@id="firstname"]')->item(0)->getAttribute('value'));
        $this->assertSame($address, $xpath->query('//textarea[@id="address"]')->item(0)->textContent);
        $this->assertSame(0, $document->getElementsByTagName('img')->length);
        $this->assertSame(0, $document->getElementsByTagName('script')->length);
        $this->assertSame(0, $xpath->query('//*[@onerror]')->length);
    }
}
