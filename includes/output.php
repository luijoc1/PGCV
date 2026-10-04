<?php

function escapeHtml($value)
{
    // El registro antiguo ya guardaba algunas entidades HTML en los nombres.
    return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
}

function safeImageUrl($filename, $prefix = 'images/', $fallback = 'noimage.jpg')
{
    if (!is_string($filename) || $filename === '' || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        $filename = $fallback;
    }
    return $prefix . rawurlencode($filename);
}

function safeProductZoomUrl($filename)
{
    $original = safeImageUrl($filename);
    if (!is_string($filename) || $filename === '' || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        return $original;
    }
    $large = 'large-' . $filename;
    return is_file(__DIR__ . '/../images/' . $large) ? safeImageUrl($large) : $original;
}

function highlightProductName($name, $keyword)
{
    $name = is_string($name) ? $name : '';
    if (!is_string($keyword) || $keyword === '') {
        return escapeHtml($name);
    }
    $parts = preg_split('/(' . preg_quote($keyword, '/') . ')/iu', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) {
        return escapeHtml($name);
    }
    $output = '';
    foreach ($parts as $index => $part) {
        $text = escapeHtml($part);
        $output .= $index % 2 === 1 ? '<b>' . $text . '</b>' : $text;
    }
    return $output;
}

function safeProductDescription($html)
{
    if (!is_string($html) || $html === '') {
        return '';
    }
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    try {
        $loaded = $document->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>', LIBXML_NONET);
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
    if (!$loaded) {
        return escapeHtml($html);
    }
    $allowed = ['p','br','b','strong','i','em','u','s','ul','ol','li','h1','h2','h3','h4','h5','h6','blockquote','pre','code','div','span','table','thead','tbody','tfoot','tr','td','th','caption'];
    $blocked = ['script','style','iframe','object','embed','svg','math','template','noscript'];
    $render = function (DOMNode $node) use (&$render, $allowed, $blocked) {
        if ($node->nodeType === XML_TEXT_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
            return escapeHtml($node->nodeValue);
        }
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }
        $tag = strtolower($node->nodeName);
        if (in_array($tag, $blocked, true)) {
            return '';
        }
        $children = '';
        foreach ($node->childNodes as $child) {
            $children .= $render($child);
        }
        if (!in_array($tag, $allowed, true)) {
            return $children;
        }
        if ($tag === 'br') {
            return '<br>';
        }
        // Se reconstruye HTML permitido sin atributos del contenido original.
        return '<' . $tag . '>' . $children . '</' . $tag . '>';
    };
    $body = $document->getElementsByTagName('body')->item(0);
    return $body ? $render($body) : '';
}
