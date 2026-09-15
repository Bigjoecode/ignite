<?php
declare(strict_types=1);

// Cleans article HTML coming from the admin editor before it is saved:
// only allow-listed tags and attributes survive, and nothing may point to
// another website (site rule: content never sends visitors off-site).

const CLEAN_TAGS = [
    'p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
    'ul', 'ol', 'li', 'a', 'img', 'figure', 'figcaption', 'blockquote', 'hr', 'div', 'span', 'code', 'pre',
    'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
];
const CLEAN_DROP_WITH_CONTENT = [
    'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select',
    'svg', 'math', 'video', 'audio', 'source', 'link', 'meta', 'noscript', 'template', 'canvas',
];
const CLEAN_ATTRS = [
    'a'      => ['href', 'title'],
    'img'    => ['src', 'alt', 'width', 'height'],
    'th'     => ['scope', 'colspan', 'rowspan'],
    'td'     => ['colspan', 'rowspan'],
    'ol'     => ['start'],
    'div'    => ['class'],
    'p'      => ['class'],
    'figure' => ['class'],
];
const CLEAN_CLASSES = ['post-callout', 'post-table', 'align-left', 'align-center', 'align-right'];

/**
 * @return array{0: string, 1: string[]} the cleaned HTML and plain-language notes about removals
 */
function clean_post_html(string $html): array
{
    $notes = [];
    if (trim($html) === '') {
        return ['', $notes];
    }

    $doc = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div data-clean-root="1">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = (new DOMXPath($doc))->query('//div[@data-clean-root]')->item(0);
    if (!$root) {
        return ['', ['The content could not be read and was not saved.']];
    }
    clean_children($doc, $root, $notes);

    $out = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $out .= $doc->saveHTML($child);
    }
    return [trim($out), array_values(array_unique($notes))];
}

function clean_children(DOMDocument $doc, DOMNode $parent, array &$notes): void
{
    foreach (iterator_to_array($parent->childNodes) as $node) {
        if ($node instanceof DOMComment || $node instanceof DOMProcessingInstruction) {
            $parent->removeChild($node);
            continue;
        }
        if (!$node instanceof DOMElement) {
            continue; // text stays as text
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, CLEAN_DROP_WITH_CONTENT, true)) {
            $parent->removeChild($node);
            $notes[] = 'Removed embedded content (' . $tag . ') that is not allowed in articles.';
            continue;
        }

        // one H1 per page is the article title, so body headings start at H2
        if ($tag === 'h1') {
            $node = clean_rename($doc, $node, 'h2');
            $tag  = 'h2';
        }

        if (!in_array($tag, CLEAN_TAGS, true)) {
            clean_children($doc, $node, $notes);
            clean_unwrap($node);
            continue;
        }

        $allowed = CLEAN_ATTRS[$tag] ?? [];
        foreach (iterator_to_array($node->attributes) as $attr) {
            $name = strtolower($attr->name);
            if (!in_array($name, $allowed, true)) {
                $node->removeAttribute($attr->name);
            }
        }
        if ($node->hasAttribute('class')) {
            $classes = array_intersect(preg_split('/\s+/', $node->getAttribute('class')) ?: [], CLEAN_CLASSES);
            $classes ? $node->setAttribute('class', implode(' ', $classes)) : $node->removeAttribute('class');
        }

        // editor tables get the blog's styled, scrollable wrapper
        if ($tag === 'table' && !($parent instanceof DOMElement && $parent->getAttribute('class') === 'post-table')) {
            $wrap = $doc->createElement('div');
            $wrap->setAttribute('class', 'post-table');
            $parent->replaceChild($wrap, $node);
            $wrap->appendChild($node);
        }

        if ($tag === 'a') {
            $href = clean_internal_href(trim($node->getAttribute('href')));
            if ($href === null) {
                clean_children($doc, $node, $notes);
                clean_unwrap($node);
                $notes[] = 'Removed a link to another website (the link text was kept). Articles can only link to pages on this site.';
                continue;
            }
            $node->setAttribute('href', $href);
        }

        if ($tag === 'img') {
            $src = trim($node->getAttribute('src'));
            if (!clean_is_local_media($src)) {
                $parent->removeChild($node);
                $notes[] = 'Removed an image hosted on another website. Use Add Media to upload it or import it from its URL.';
                continue;
            }
            foreach (['width', 'height'] as $dim) {
                if ($node->hasAttribute($dim) && !ctype_digit($node->getAttribute($dim))) {
                    $node->removeAttribute($dim);
                }
            }
            if (!$node->hasAttribute('alt')) {
                $node->setAttribute('alt', '');
            }
        }

        clean_children($doc, $node, $notes);
    }
}

/** Internal path for a link on this site, or null for anything that leaves the site. */
function clean_internal_href(string $href): ?string
{
    if ($href === '') {
        return null;
    }
    if ($href[0] === '#' || preg_match('#^(mailto|tel):#i', $href)) {
        return $href;
    }
    if ($href[0] === '/' && ($href[1] ?? '') !== '/' && ($href[1] ?? '') !== '\\') {
        return $href;
    }
    $parts = parse_url($href);
    $site  = strtolower((string) parse_url(cfg('base_url'), PHP_URL_HOST));
    if (
        isset($parts['scheme'], $parts['host'])
        && in_array(strtolower($parts['scheme']), ['http', 'https'], true)
        && preg_replace('/^www\./', '', strtolower($parts['host'])) === $site
    ) {
        return ($parts['path'] ?? '/')
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }
    return null;
}

function clean_is_local_media(string $src): bool
{
    return (bool) preg_match('#^/(uploads|assets/img)/[A-Za-z0-9._/-]+$#', $src) && strpos($src, '..') === false;
}

function clean_unwrap(DOMElement $node): void
{
    $parent = $node->parentNode;
    if (!$parent) {
        return;
    }
    while ($node->firstChild) {
        $parent->insertBefore($node->firstChild, $node);
    }
    $parent->removeChild($node);
}

function clean_rename(DOMDocument $doc, DOMElement $node, string $tag): DOMElement
{
    $new = $doc->createElement($tag);
    while ($node->firstChild) {
        $new->appendChild($node->firstChild);
    }
    $node->parentNode->replaceChild($new, $node);
    return $new;
}
