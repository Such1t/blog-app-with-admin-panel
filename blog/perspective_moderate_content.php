<?php
function moderate_content($content) {
    // Remove base64 images from content
    $contentWithoutImages = preg_replace('/<img[^>]+>/i', '', $content);

    $apiKey = 'AIzaSyA-wmHR3k7RQHZ86ScaebGXcsO_dHYOOow'; // Replace with your Perspective API key
    $url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . $apiKey;

    $data = [
        'comment' => ['text' => $contentWithoutImages], // Use the content without images here
        'languages' => ['en'],
        'requestedAttributes' => ['TOXICITY' => new stdClass(), 'SEVERE_TOXICITY' => new stdClass()]
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/json",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context); // Suppress warnings with @

    // Log the response for debugging
    error_log('API Response: ' . $result);

    if ($result === FALSE) {
        // Return an error flag instead of marking the content as inappropriate
        return ['error' => true, 'message' => 'Failed to call the Perspective API.'];
    }

    $response = json_decode($result, true);

    if (isset($response['error'])) {
        // Return an error if the API responded with an error
        return ['error' => true, 'message' => 'Perspective API error: ' . $response['error']['message']];
    }

    $toxicityScore = $response['attributeScores']['TOXICITY']['summaryScore']['value'];
    $flaggedText = [];

    // Check spans for specific flagged words or phrases
    if (isset($response['attributeScores']['TOXICITY']['spanScores'])) {
        foreach ($response['attributeScores']['TOXICITY']['spanScores'] as $span) {
            if ($span['score']['value'] >= 0.5) {
                $flaggedText[] = substr($content, $span['begin'], $span['end'] - $span['begin']);
            }
        }
    }

    return ['isInappropriate' => $toxicityScore >= 0.5, 'flaggedText' => $flaggedText]; // Return feedback
}
?>