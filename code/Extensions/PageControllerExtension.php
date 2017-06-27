<?php

class PageControllerExtension extends DataExtension
{
    public function getFBFeed()
    {
        $FBService = new FBFeedService();
        return $FBService->getStoredPosts();
    }
}
