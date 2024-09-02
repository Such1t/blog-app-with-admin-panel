<?php

namespace Google\Service;

class CommentAnalyzer extends \Google\Service
{
    public $comments;

    public function __construct(\Google\Client $client)
    {
        parent::__construct($client);
        $this->rootUrl = 'https://commentanalyzer.googleapis.com/';
        $this->servicePath = 'v1alpha1/';
        $this->version = 'v1alpha1';
        $this->serviceName = 'commentanalyzer';

        $this->comments = new CommentAnalyzer\Resource\Comments(
            $this,
            $this->serviceName,
            'comments',
            [
                'methods' => [
                    'analyze' => [
                        'path' => 'comments:analyze',
                        'httpMethod' => 'POST',
                        'parameters' => [],
                    ],
                ],
            ]
        );
    }
}
