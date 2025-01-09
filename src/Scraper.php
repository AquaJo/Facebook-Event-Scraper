<?php

namespace FacebookEventScraper;

require_once __DIR__ . '/Helpers/network.php';
require_once __DIR__ . '/Helpers/htmlParser.php';

class Scraper
{
    public static function scrapeEvent($urlFromUser, $options = [])
    {
        if (!filter_var($urlFromUser, FILTER_VALIDATE_URL)) {
            $urlFromUser = 'https://www.facebook.com/events/' . $urlFromUser;
        }
        $dataString = fetchEvent($urlFromUser, $options['proxy'] ?? null);
        $basicData = getBasicData($dataString);
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
}
