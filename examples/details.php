<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FacebookEventScraper\Scraper;

$scraper = new Scraper();
$result = $scraper->scrapeEvent('1830273880516670'); // or use 'https://www.facebook.com/events/1830273880516670'

print_r($result);
