# Facebook-Event-Scraper - Port

This is an almost feature-complete php port of the [TS & npm-Library from fancescov1](https://github.com/francescov1/facebook-event-scraper/commit/bb2998b54891c1771c1c9010c39b6b4b5cee2d74).

Be sure to have `ext-curl` aka `php-curl` installed for the desired php version.\
This is mandatory, allowing brotli decoding of facebook's response html.

## Install & Use

Run `composer require aquajo/facebook-event-scraper`.

Then use it like this:

```php
require_once __DIR__ . '/vendor/autoload.php';

use FacebookEventScraper\Scraper;

$scraper = new Scraper();
$result = $scraper->scrapeEvent('https://www.facebook.com/events/1830273880516670');

print_r($result);
```
