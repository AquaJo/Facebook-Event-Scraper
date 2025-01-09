<?php

function findJsonInString(string $dataString, string $key, ?callable $isDesiredValue = null): array
{
    $prefix = '"' . $key . '":';
    $startPosition = 0;

    while (true) {
        $idx = strpos($dataString, $prefix, $startPosition);
        if ($idx === false) {
            return ['startIndex' => -1, 'endIndex' => -1, 'jsonData' => null];
        }

        $idx += strlen($prefix);
        $startIndex = $idx;
        $startCharacter = $dataString[$startIndex];

        // Value is null
        if ($startCharacter === 'n' && substr($dataString, $startIndex, 4) === 'null') {
            return ['startIndex' => $startIndex, 'endIndex' => $startIndex + 3, 'jsonData' => null];
        }

        // Unexpected start character
        if ($startCharacter !== '{' && $startCharacter !== '[') {
            throw new Exception("Invalid start character: $startCharacter");
        }

        $endCharacter = $startCharacter === '{' ? '}' : ']';
        $nestedLevel = 0;
        $isIndexInString = false;

        while ($idx < strlen($dataString) - 1) {
            $idx++;
            if ($dataString[$idx] === '"' && $dataString[$idx - 1] !== '\\') {
                $isIndexInString = !$isIndexInString;
            } elseif ($dataString[$idx] === $endCharacter && !$isIndexInString) {
                if ($nestedLevel === 0) {
                    break;
                }
                $nestedLevel--;
            } elseif ($dataString[$idx] === $startCharacter && !$isIndexInString) {
                $nestedLevel++;
            }
        }

        $jsonDataString = substr($dataString, $startIndex, $idx - $startIndex + 1);
        $jsonData = json_decode($jsonDataString, true);

        if (!$isDesiredValue || $isDesiredValue($jsonData)) {
            return ['startIndex' => $startIndex, 'endIndex' => $idx, 'jsonData' => $jsonData];
        }

        $startPosition = $idx;
    }
}

function getDescription($html)
{
    $jsonData = findJsonInString($html, 'event_description')['jsonData'];

    if (!$jsonData) {
        throw new Exception('No event description found, please verify that your event URL is correct');
    }

    return $jsonData['text'];
}

function getBasicData($html)
{
    $jsonData = findJsonInString($html, 'event', function ($candidate) {
        return isset($candidate['day_time_sentence']);
    })['jsonData'];
    if (!$jsonData) {
        throw new Exception('No event data found, please verify that your URL is correct and the event is accessible without authentication');
    }

    return [
        'id' => $jsonData['id'],
        'name' => $jsonData['name'],
        'photo' => isset($jsonData['cover_media_renderer']['cover_photo']) ? [
            'url' => $jsonData['cover_media_renderer']['cover_photo']['photo']['url'],
            'id' => $jsonData['cover_media_renderer']['cover_photo']['photo']['id'],
            'imageUri' => $jsonData['cover_media_renderer']['cover_photo']['photo']['image']['uri'] ?? $jsonData['cover_media_renderer']['cover_photo']['photo']['full_image']['uri']
        ] : null,
        'video' => isset($jsonData['cover_media_renderer']['cover_video']) ? [
            'url' => $jsonData['cover_media_renderer']['cover_video']['url'],
            'id' => $jsonData['cover_media_renderer']['cover_video']['id'],
            'thumbnailUri' => $jsonData['cover_media_renderer']['cover_video']['image']['uri']
        ] : null,
        'formattedDate' => $jsonData['day_time_sentence'],
        'startTimestamp' => $jsonData['start_timestamp'],
        'isOnline' => $jsonData['is_online'],
        'url' => $jsonData['url'],
        'siblingEvents' => array_map(function ($sibling) {
            return [
                'id' => $sibling['id'],
                'startTimestamp' => $sibling['start_timestamp'],
                'endTimestamp' => $sibling['end_timestamp'],
                'parentEvent' => ['id' => $sibling['parent_event']['id']]
            ];
        }, $jsonData['comet_neighboring_siblings'] ?? []),
        'parentEvent' => isset($jsonData['parent_if_exists_or_self']) && $jsonData['parent_if_exists_or_self']['id'] !== $jsonData['id']
            ? ['id' => $jsonData['parent_if_exists_or_self']['id']]
            : null
    ];
}

function getTicketUrl($html)
{
    $jsonData = findJsonInString($html, 'event', function ($candidate) {
        return isset($candidate['event_buy_ticket_url']);
    })['jsonData'];

    return $jsonData['event_buy_ticket_url'] ?? null;
}

function getUserStats($html)
{
    $jsonData = findJsonInString($html, 'event_connected_users_public_responded')['jsonData'];

    return [
        'usersResponded' => $jsonData['count'] ?? null
    ];
}

function getLocation($html)
{
    $result = findJsonInString($html, 'event_place', function ($candidate) {
        return isset($candidate['location']);
    });

    $jsonData = $result['jsonData'];
    $startIndex = $result['startIndex'];

    if ($startIndex === -1) {
        throw new Exception('No location information found, please verify that your event URL is correct');
    }

    if ($jsonData === null) {
        return null;
    }

    return [
        'id' => $jsonData['id'],
        'name' => $jsonData['name'],
        'description' => $jsonData['best_description']['text'] ?? null,
        'url' => $jsonData['url'] ?? null,
        'coordinates' => isset($jsonData['location']) ? [
            'latitude' => $jsonData['location']['latitude'],
            'longitude' => $jsonData['location']['longitude']
        ] : null,
        'countryCode' => $jsonData['location']['reverse_geocode']['country_alpha_two'] ?? null,
        'type' => $jsonData['place_type'],
        'address' => $jsonData['address']['street'] ?? null,
        'city' => isset($jsonData['city']) ? [
            'name' => $jsonData['city']['contextual_name'],
            'id' => $jsonData['city']['id']
        ] : null
    ];
}

function getHosts($html)
{
    $jsonData = findJsonInString($html, 'event_hosts_that_can_view_guestlist', function ($candidate) {
        return isset($candidate[0]['profile_picture']);
    })['jsonData'];

    if ($jsonData === null) {
        return [];
    }

    return array_map(function ($host) {
        return [
            'id' => $host['id'],
            'name' => $host['name'],
            'url' => $host['url'],
            'type' => $host['__typename'],
            'photo' => [
                'imageUri' => $host['profile_picture']['uri']
            ]
        ];
    }, $jsonData);
}

function getOnlineDetails($html)
{
    $jsonData = findJsonInString($html, 'online_event_setup', function ($candidate) {
        return isset($candidate['third_party_url'], $candidate['type']);
    })['jsonData'];

    if ($jsonData === null) {
        throw new Exception('No online event details found, please verify that your event URL is correct');
    }

    return [
        'url' => $jsonData['third_party_url'],
        'type' => $jsonData['type']
    ];
}

function getEndTimestampAndTimezone($html, $expectedStartTimestamp)
{
    $jsonData = findJsonInString($html, 'data', function ($candidate) use ($expectedStartTimestamp) {
        return isset($candidate['end_timestamp'], $candidate['tz_display_name']) && $candidate['start_timestamp'] === $expectedStartTimestamp;
    })['jsonData'];

    if ($jsonData === null) {
        throw new Exception('No end date & timezone details found, please verify that your event URL is correct');
    }

    return [
        'endTimestamp' => $jsonData['end_timestamp'] ?: null,
        'timezone' => $jsonData['tz_display_name']
    ];
}
