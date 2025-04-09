<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FacebookEventScraper\Scraper;

$scraper = new Scraper();
$result = $scraper->scrapeEvents('github'); // or use 'https://www.facebook.com/GitHub/events/'

print_r($result);
