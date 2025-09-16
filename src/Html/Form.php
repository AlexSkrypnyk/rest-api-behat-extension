<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Html;

use GuzzleHttp\Psr7\MultipartStream;

class Form
{
    private array $body;

    private string $contentTypeHeaderValue = '';

    public function __construct(array $body)
    {
        $this->body = $body;
    }

    public function getBody(): MultipartStream|string
    {
        if ($this->bodyHasFileObject()) {
            return $this->getMultipartStreamBody();
        }

        return $this->getNameValuePairBody();
    }

    public function getContentTypeHeaderValue(): string
    {
        return $this->contentTypeHeaderValue;
    }

    private function setContentTypeHeaderValue(string $value): void
    {
        $this->contentTypeHeaderValue = $value;
    }

    private function bodyHasFileObject(): bool
    {
        foreach ($this->body as $element) {
            if ($element['object'] == 'file') {
                return true;
            }
        }

        return false;
    }

    private function getMultipartStreamBody(): MultipartStream
    {
        $multiparts = array_map(
            function (array $element): array {

                if ($element['object'] == 'file') {
                    return ['name' => $element['name'], 'contents' => fopen($element['value'], 'r')];
                }

                return ['name' => $element['name'], 'contents' => $element['value']];
            },
            $this->body
        );

        $boundary = sha1(uniqid('', true));

        $this->setContentTypeHeaderValue('multipart/form-data; boundary=' . $boundary);

        return new MultipartStream($multiparts, $boundary);
    }

    private function getNameValuePairBody(): string
    {
        $body = [];
        foreach ($this->body as $element) {
            $body[$element['name']] = $element['value'];
        }

        $this->setContentTypeHeaderValue('application/x-www-form-urlencoded');

        return http_build_query($body);
    }
}
