<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class Json implements \Stringable
{
    private $content;

    public function __construct($content, $encodedAsString = true)
    {
        $this->content = true === $encodedAsString ? $this->decode((string) $content) : $content;
    }

    public static function fromRawContent($content): static
    {
        return new static($content, false);
    }

    public function read($expression, PropertyAccessor $propertyAccessor)
    {
        if (is_array($this->content)) {
            $expression = preg_replace('/^root/', '', (string) $expression);
        } else {
            $expression = preg_replace('/^root./', '', (string) $expression);
        }

        // If root asked, we return the entire content
        if (strlen(trim((string) $expression)) <= 0) {
            return $this->content;
        }

        return $propertyAccessor->getValue($this->content, $expression);
    }

    public function getRawContent()
    {
        return $this->content;
    }

    public function encode($pretty = true)
    {
        if (true === $pretty && defined('JSON_PRETTY_PRINT')) {
            return json_encode($this->content, JSON_PRETTY_PRINT);
        }

        return json_encode($this->content);
    }

    public function __toString(): string
    {
        return (string) $this->encode(false);
    }

    private function decode(string $content)
    {
        $result = json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                sprintf('The string "%s" is not valid json', $content)
            );
        }

        return $result;
    }
}
