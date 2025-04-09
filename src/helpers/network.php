<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

function fetchWithGuzzle($url, $proxy = null)
{
    // Initialize the Guzzle client
    $client = new Client();

    // Define the headers
    $headers = [
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'en-US,en;q=0.6',
        'Cache-Control' => 'max-age=0',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'same-origin',
        'Sec-Fetch-User' => '?1',
        'Sec-Gpc' => '1',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
    ];

    // Set up the Guzzle options
    $options = [
        'headers' => $headers
    ];

    // If a proxy is provided, add it to the options
    if ($proxy) {
        $options['proxy'] = $proxy;
    }

    try {
        // Make the GET request
        $response = $client->request('GET', $url, $options);

        // Return the response body
        return (string) $response->getBody();
    } catch (RequestException $e) {
        // Handle the error, you can log it for debugging purposes
        throw new Exception('Error fetching event, make sure your URL is correct and the event is accessible to the public.');
    }
}
