<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension;

use atoum\atoum\asserter\generator;
use Ubirak\RestApiBehatExtension\Rest\WrongResponseExpectation;
use Ubirak\RestApiBehatExtension\Rest\HttpExchangeFormatter;
use atoum\atoum\asserter;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Psr\Http\Message\ResponseInterface;
use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser;
use Behat\Gherkin\Node\TableNode;

class RestApiContext implements Context, SnippetAcceptingContext
{
    protected generator $asserter;

    protected RestApiBrowser $restApiBrowser;

    public function __construct(RestApiBrowser $restApiBrowser)
    {
        $this->restApiBrowser = $restApiBrowser;
        $this->asserter = new generator();
    }

    /**
     * @param string $method request method
     * @param string $url    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
     */
    public function iSendARequest(string $method, $url): void
    {
        $this->restApiBrowser->sendRequest($method, $url);
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string       $method request method
     * @param string       $url    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
     */
    public function iSendARequestWithBody(string $method, $url, PyStringNode $body): void
    {
        $this->restApiBrowser->sendRequest($method, $url, (string) $body);
    }

    /**
     * @When I send a POST request to :url as HTML form with body:
     */
    public function iSendAPostRequestToAsHtmlFormWithBody($url, TableNode $body): void
    {
        $formElements = [];
        foreach ($body as $element) {
            if (!isset($element['object'])) {
                throw new \Exception('You have to specify an object attribute');
            }

            $formElements[] = $element;
        }

        $this->restApiBrowser->sendRequest("POST", $url, $formElements);
    }

    /**
     * @param string $code status code
     *
     * @Then /^(?:the )rest ?response status code should be (\d+)$/
     */
    public function theResponseCodeShouldBe($code): void
    {
        $expected = intval($code);
        $actual = intval($this->getResponse()->getStatusCode());
        try {
            $this->asserter->variable($actual)->isEqualTo($expected);
        } catch (\Exception $exception) {
            throw new WrongResponseExpectation(
                $exception->getMessage(),
                $this->restApiBrowser->getRequest(),
                $this->getResponse(),
                $exception
            );
        }
    }

    /**
     * @return ResponseInterface
     */
    protected function getResponse()
    {
        return $this->restApiBrowser->getResponse();
    }

    /**
     * @Given /^I set "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iSetHeaderEqualTo($headerName, string $headerValue): void
    {
        $this->restApiBrowser->setRequestHeader($headerName, $headerValue);
    }

    /**
     * @Given /^I add "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iAddHeaderEqualTo($headerName, string $headerValue): void
    {
        $this->restApiBrowser->addRequestHeader($headerName, $headerValue);
    }

    /**
     * Set login / password for next HTTP authentication.
     *
     * @When /^I set basic authentication with "(?P<username>[^"]*)" and "(?P<password>[^"]*)"$/
     */
    public function iSetBasicAuthenticationWithAnd(string $username, string $password): void
    {
        $authorization = base64_encode($username.':'.$password);
        $this->restApiBrowser->setRequestHeader('Authorization', 'Basic '.$authorization);
    }

    /**
     * @Then print request and response
     */
    public function printRequestAndResponse(): void
    {
        $formatter = $this->buildHttpExchangeFormatter();
        echo "REQUEST:\n";
        echo $formatter->formatRequest();
        echo "\nRESPONSE:\n";
        echo $formatter->formatFullExchange();
    }

    /**
     * @Then print request
     */
    public function printRequest(): void
    {
        echo $this->buildHttpExchangeFormatter()->formatRequest();
    }

    /**
     * @Then print response
     */
    public function printResponse(): void
    {
        echo $this->buildHttpExchangeFormatter()->formatFullExchange();
    }

    protected function buildHttpExchangeFormatter(): HttpExchangeFormatter
    {
        return new HttpExchangeFormatter($this->restApiBrowser->getRequest(), $this->getResponse());
    }
}
