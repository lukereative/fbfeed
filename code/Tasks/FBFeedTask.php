<?php

class FBFeedTask extends BuildTask
{

    private $fbService;

/**
* Initiate the service and copy new posts to our database
*/
    public function run($request)
    {
        $this->fbService = new FBFeedService();

        $storedPosts = $this->fbService->getStoredPosts();
        $posts = $this->fbService->getPostsFromFacebook();
        $inserted = 0;
        foreach ($posts as $i => $post) {
            if (!isset($post['FBID'])) {
                continue;
            }

            $existingPost = FacebookPost::get()->filter(array(
                'FBID' => $post['FBID']
            ))->first();

            if ($existingPost) {
                continue;
            } else {
                if (isset($post['source'])) {
                    $imageSource = $post['source'];
                } else {
                    $imageSource = null;
                }

                $this->fbService->storePost($post['FBID'], $post['TimePosted'], $post['URL'], $post['Content'], $imageSource);
                $inserted++;
            }
        }

        echo 'Stored ' . $inserted . ' new posts.';
    }
}
