<?php

namespace Google\Service\CommentAnalyzer\Resource;

use Google\Service\Resource;

class Comments extends Resource
{
    /**
     * Analyzes the provided comment and returns scores for requested attributes.
     * (comments.analyze)
     *
     * @param Google_Service_CommentAnalyzer_AnalyzeCommentRequest $postBody
     * @param array $optParams Optional parameters.
     * @return Google_Service_CommentAnalyzer_AnalyzeCommentResponse
     */
    public function analyze($postBody, $optParams = [])
    {
        $params = ['postBody' => $postBody];
        $params = array_merge($params, $optParams);
        return $this->call('analyze', [$params], \Google\Service\CommentAnalyzer\AnalyzeCommentResponse::class);
    }
}
