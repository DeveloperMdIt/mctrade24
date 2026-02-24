<?php

namespace Template\admorris_pro\Utils;

final class Html
{
  protected $reachedLimit = false,
    $totalLen = 0,
    $maxLen = 25,
    $toRemove = [];

  /**
   * Shorten text without splitting words or breaking html tags
   *
   * @source https://stackoverflow.com/a/16584383
   */
  public static function trim(?string $html, int $maxLen = 25): string
  {

    if (empty($html)) {
      return '';
    }
    $dom = new \DomDocument();

    $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // UTF-8 fix
    // https://www.php.net/manual/en/domdocument.loadhtml.php#95251
    foreach ($dom->childNodes as $item)
    if ($item->nodeType == XML_PI_NODE)
        $dom->removeChild($item); // remove hack
    $dom->encoding = 'UTF-8'; // insert proper


    $instance = new self();
    $toRemove = $instance->walk($dom, $maxLen);

    // remove any nodes that exceed limit
    foreach ($toRemove as $child) {
      $child->parentNode->removeChild($child);
    }

    return $dom->saveHTML();
  }

  protected function walk(\DomNode $node, $maxLen)
  {
    if ($this->reachedLimit) {
      $this->toRemove[] = $node;
    } else {
      // only text nodes should have text,
      // so do the splitting here
      if ($node instanceof \DomText) {
        $this->totalLen += $nodeLen = strlen($node->nodeValue);

        // use mb_strlen / mb_substr for UTF-8 support
        if ($this->totalLen > $maxLen) {
          $node->nodeValue =
            substr($node->nodeValue, 0, $nodeLen - ($this->totalLen - $maxLen)) . '...';
          $this->reachedLimit = true;
        }
      }

      // if node has children, walk its child elements
      if (isset($node->childNodes)) {
        foreach ($node->childNodes as $child) {
          $this->walk($child, $maxLen);
        }
      }
    }

    return $this->toRemove;
  }
}
