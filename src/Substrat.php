<?php

namespace Julien\Substrat;


class Substrat {

  const MAX_TAGS_PER_TEMPLATE = 200;
  protected $template;
  protected $data;

  function __construct($template, $data) {
    $this->template = $template;
    $this->data = $data;
  }

  public function replaceAll(): string {
    $template = $this->template;
    $upperbound = 0;
    do {
      $tags = $this->extractTags($template);
      if (count($tags)) {
        $template = $this->replaceString(
          $template,
          $tags
        );
      }
      $upperbound += 1;
    }
    while (count($tags) && $upperbound < self::MAX_TAGS_PER_TEMPLATE);
    return $template;
  }


  protected function getFirstKeyValue(array $map) {
    $key = array_keys($map)[0];
    $value = array_values($map)[0];
    return [$key, $value];
  }

  protected function replaceString(string $template, $tags): string {
    list($pos, $tag)=$this->getFirstKeyValue($tags);
    if (empty($tags)) {
      throw new \Exception("No tag found in template.");
    }

    list($from, $to) = explode('-', $pos);

    if (is_null($to)) {
      throw new \Exception("Invalid character range: ".var_export($tags, true));
    }

    $replacementValue =$this->getValueByPath(
      $this->data, 
      $this->getCleanTagValue($tag)
    );
    return $this->replaceRange(
      $template, 
      explode(':', $from), 
      explode(':', $to), 
      $replacementValue
    );
  }

  protected function getCleanTagValue($tag) {
    $trimmed_from_brakets = rtrim(ltrim($tag, '{'), '}');
    return trim($trimmed_from_brakets);
  }

  protected function getValueByPath(array $data, string $path): mixed {
    $segments = explode('.', $path);
    return $this->getValueBySegments($data, $segments);
  }

  protected function getValueBySegments(array $data, array $segments): mixed {
    if (empty($segments)) {
      return $data; // reached the end of the path
    }

    $key = array_shift($segments);

    if (!array_key_exists($key, $data)) {
      return null; // path not found
    }

    $value = $data[$key];

    if (empty($segments)) {
      return $value; // last segment
    }

    // If next step requires going deeper but $value is not an array → fail
    if (!is_array($value)) {
      return null;
    }

    return $this->getValueBySegments($value, $segments);
  }


  /*
   * @param $template string
   * @param $fromLocation array
   *
   */
  protected function replaceRange(string $template, array $fromLocation, array $toLocation, mixed $replacement): string {
    $replacementStr = (string) $replacement;

    // Split template into lines
    $lines = preg_split('/\r\n|\r|\n/', $template);

    // Convert 1-based to 0-based indices
    [$fromLine, $fromChar] = $fromLocation;
    [$toLine, $toChar] = $toLocation;
    $fromLineIndex = $fromLine - 1;
    $toLineIndex = $toLine - 1;
    $fromCharIndex = $fromChar - 1;
    $toCharIndex = $toChar - 1;

    if (!isset($lines[$fromLineIndex]) || !isset($lines[$toLineIndex])) {
      throw new \InvalidArgumentException("Invalid line numbers in range.");
    }

    if ($fromLineIndex === $toLineIndex) {
      // Single-line replacement
      $line = $lines[$fromLineIndex];
      $lines[$fromLineIndex] = substr($line, 0, $fromCharIndex)
        . $replacementStr
        . substr($line, $toCharIndex + 1);
    } else {
      // Multi-line replacement
      // First line: keep content before fromCharIndex
      $lines[$fromLineIndex] = substr($lines[$fromLineIndex], 0, $fromCharIndex) . $replacementStr;
      // Last line: keep content after toCharIndex
      $lines[$toLineIndex] = substr($lines[$toLineIndex], $toCharIndex + 1);
      // Remove all lines in between
      if ($toLineIndex - $fromLineIndex > 1) {
        array_splice($lines, $fromLineIndex + 1, $toLineIndex - $fromLineIndex - 1);
      }
      // Merge first and last line
      $lines[$fromLineIndex] .= $lines[$toLineIndex];
      // Remove the old last line
      array_splice($lines, $fromLineIndex + 1, 1);
    }

    return implode("\n", $lines);
  }

  protected function extractTags(string $html): array {
    $tags = [];
    $lines = preg_split('/\r\n|\r|\n/', $html);

    foreach ($lines as $lineIndex => $lineContent) {
      // Match Twig-like tags: {{ ... }} or {% ... %}
      preg_match_all('/(\{\{.*?\}\}|\{%.*?%\})/', $lineContent, $matches, PREG_OFFSET_CAPTURE);

      foreach ($matches[0] as $match) {
        [$tag, $startChar] = $match;
        $endChar = $startChar + strlen($tag) - 1;
        $key = sprintf(
          "%d:%d-%d:%d",
          $lineIndex + 1, // lines are 1-indexed
          $startChar + 1, // characters are 1-indexed
          $lineIndex + 1,
          $endChar + 1
        );
        $tags[$key] = $tag;
      }
    }

    return $tags;
  }

}

/*
  protected function replaceSubTemplate(string $template, $tags): string {
    list($key, $value)=$this->getFirstKeyValue($tags);

    list($subTemplate,$value)= explode("|", $value);
    $tagVal= $this->getCleanTagValue($value);
    $subTemplate=$this->getCleanTagValue($subTemplate);
    // detect global variable:
    if (!function_exists($subTemplate)) {
      throw new \Exception(
        "Found no global variable with name '$subTemplate'.");
    }

    if (empty($tags)) {
      throw new \Exception("No tag found in template.");
    }

    $values = $this->getValueByPath($this->data, $tagVal);
    if (!is_array($values)) {
      throw new \InvalidArgumentException("'$tagVal' is expected to be an array.");
    }
    $html ="";
    foreach ($values as $k=>$val) {
      echo "kv:";
      var_dump($k, $val);
      list($from, $to) = explode('-', $key);
      echo "from to ";
      var_dump($from, $to);
      if (is_null($to)) {
        throw new \Exception("Invalid character range: ".var_export($tags, true));
      }
      var_dump($k);
      var_dump($this->getValueByPath(
          $this->data, 
          $tagVal
        ));
      var_dump($tagVal);
      var_dump($val);
      var_dump($val[$tagVal]);
      $html.= $this->replaceRange(
        $subTemplate(), 
        explode(':', $from), 
        explode(':', $to), 
        $this->getValueByPath(
          $this->data, 
          $tagVal
        )[$k]
      );
    }
    return $html;
  }
 */
