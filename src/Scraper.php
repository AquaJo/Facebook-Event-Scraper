<?php

namespace FacebookEventScraper;

require_once __DIR__ . '/helpers/network.php';
require_once __DIR__ . '/helpers/htmlParser.php';

class Scraper
{
    /*
    * @param string $urlFromUser
    * @param array $options
    * @return array
    */
    public static function scrapeEvent($urlFromUser, $options = []): array
    {
        if (!filter_var($urlFromUser, FILTER_VALIDATE_URL)) {
            $urlFromUser = 'https://www.facebook.com/events/' . $urlFromUser;
        }
        $dataString = fetchWithGuzzle($urlFromUser, $options['proxy'] ?? null);
        $basicData = extractEventData($dataString);
        $id = $basicData['id'];
        $name = $basicData['name'];
        $photo = $basicData['photo'];
        $video = $basicData['video'];
        $isOnline = $basicData['isOnline'];
        $url = $basicData['url'];
        $startTimestamp = $basicData['startTimestamp'];
        $formattedDate = $basicData['formattedDate'];
        $siblingEvents = $basicData['siblingEvents'];
        $parentEvent = $basicData['parentEvent'];

        $endTimestampAndTimezone = getEndTimestampAndTimezone($dataString, $startTimestamp);
        $endTimestamp = $endTimestampAndTimezone['endTimestamp'];
        $timezone = $endTimestampAndTimezone['timezone'];

        $location = $isOnline ? null : getLocation($dataString);
        $onlineDetails = $isOnline ? getOnlineDetails($dataString) : null;

        $description = getDescription($dataString);
        $ticketUrl = getTicketUrl($dataString);
        $hosts = getHosts($dataString);
        $userStats = getUserStats($dataString);
        $usersResponded = $userStats['usersResponded'];

        return [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'location' => $location,
            'photo' => $photo,
            'video' => $video,
            'isOnline' => $isOnline,
            'url' => $url,
            'startTimestamp' => $startTimestamp,
            'endTimestamp' => $endTimestamp,
            'formattedDate' => $formattedDate,
            'timezone' => $timezone,
            'onlineDetails' => $onlineDetails,
            'hosts' => $hosts,
            'ticketUrl' => $ticketUrl,
            'siblingEvents' => $siblingEvents,
            'parentEvent' => $parentEvent,
            'usersResponded' => $usersResponded,
        ];
    }


    /*
    * @param string $urlFromUser
    * @param array $options
    * @return array - array of event ids, the first 8, most recent
    */
    public static function scrapeEvents($urlFromUser, $options = []): array
    {
        if (!filter_var($urlFromUser, FILTER_VALIDATE_URL)) {
            $urlFromUser = 'https://www.facebook.com/' . $urlFromUser . '/events';
        }

        $dataString = fetchWithGuzzle($urlFromUser, $options['proxy'] ?? null);

        $eventsIds = extractEventIds($dataString);

        return $eventsIds;
    }
}
