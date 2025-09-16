# RestApiExtension for Behat

## About this fork

[![tests](https://github.com/AlexSkrypnyk/rest-api-behat-extension/actions/workflows/test-php.yml/badge.svg)](https://github.com/AlexSkrypnyk/rest-api-behat-extension/actions/workflows/test-php.yml)
![GitHub release](https://img.shields.io/github/v/release/AlexSkrypnyk/rest-api-behat-extension?logo=github)
![LICENSE](https://img.shields.io/github/license/AlexSkrypnyk/rest-api-behat-extension)

This is a fork of the original `ubirak/rest-api-behat-extension` package, 
modified to support modern PHP versions and to introduce Drupal-compatible
step definitions.

I pledge to maintain this fork and keep it up to date with the latest changes
from the original repository.

### Installation

Installed via Composer:

Add the following to your `composer.json` file under the `repositories` section:

```json
{
    "type": "vcs",
    "url": "https://github.com/AlexSkrypnyk/rest-api-behat-extension.git",
    "canonical": true
}
```
Then run:

```bash
composer require --dev ubirak/rest-api-behat-extension
```

### Versioning Policy

This fork follows the upstream project closely while providing additional
patches. To ensure compatibility with Composer and standard Semantic
Versioning (SemVer), releases in this fork are always published under the same
major and minor versions as upstream, with only the patch number incremented.

For example, if upstream’s latest release is `v9.1.0` and this fork introduces
extra fixes before upstream publishes a new version, the fork will tag its
release as `v9.1.1`. If upstream later publishes `v9.1.1`, the fork will move
forward and release `v9.1.2`. This way, patch numbers always increase
sequentially, avoiding conflicts with upstream tags and guaranteeing a clear
upgrade path.

Consumers requiring this fork simply add it as a VCS repository and keep using
the upstream package name. Composer will then resolve dependencies to the fork,
and applications using a constraint such as `^9.1` will transparently receive
the patched releases without needing to change version requirements.

---

For now only JSON API is supported to analyze response, but you could use REST part to perform request on any type of API.

## Warning

From version `7.0`, namespace vendor changed from `Rezzza` to `Ubirak`.

## Install

Require the package as a development dependency :

```sh
composer require --dev ubirak/rest-api-behat-extension
```

Don't forget to load the extension and the context if needed in your `behat.yml` :
```yaml
default:
    extensions:
        Ubirak\RestApiBehatExtension\Extension:
            rest:
                base_url: http://localhost:8888
                store_response: true
    suites:
        default:
            contexts:
                - Ubirak\RestApiBehatExtension\RestApiContext
                - Ubirak\RestApiBehatExtension\Json\JsonContext
```

Then you will need to require in your composer the http client you want to use, and the message factory.

Example:
```
composer require --dev guzzlehttp/psr7 php-http/curl-client
```

## Usage
You can use directly the `JsonContext` or `RestApiContext` by loading them in your behat.yml or use the `RestApiBrowser` and `JsonInspector` by adding them in the construct of your own context.

```php
<?php
/**/

use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser;
use Ubirak\RestApiBehatExtension\Json\JsonInspector;

class FeatureContext implements Context
{
    private $restApiBrowser;

    private $jsonInspector;

    public function __construct(RestApiBrowser $restApiBrowser, JsonInspector $jsonInspector)
    {
        $this->restApiBrowser = $restApiBrowser;
        $this->jsonInspector = $jsonInspector;
    }
}
```
