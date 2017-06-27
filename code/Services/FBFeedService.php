<?php

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Class FBFeedService
 * @package FBFeed
 */
class FBFeedService
{

    /**
     * @var mixed
     */
    private $appID;
    /**
     * @var mixed
     */
    private $appSecret;
    /**
     * @var mixed|null
     */
    private $pageID;
    /**
     * @var mixed
     */
    private $accessToken;

    /**
     * @param null $pageID The Facebook ID of the page, can be obtained at http://findmyfacebookid.com
     */
    public function __construct($pageID = null)
    {
        $siteConfig = SiteConfig::current_site_config();

        if (!$pageID) {
            $pageID = $siteConfig->FBPageID;
        }

        $this->pageID = $pageID;
        $this->appID = $siteConfig->FBAppID;
        $this->appSecret = $siteConfig->FBAppSecret;
        $this->accessToken = $siteConfig->FBAccessToken;
    }

    /**
     * Get our local copies of the Facebook Page posts
     *
     * @param int $limit
     * @return \DataList|\SS_Limitable
     */
    public function getStoredPosts($limit = 4)
    {
        return FacebookPost::get()->limit($limit);
    }

    /**
     * Store a Facebook Page post into our database
     *
     * @param $fb_id
     * @param $content
     * @param $url
     * @param $timePosted
     * @param null $imageSource
     * @return FacebookPost
     */
    public function storePost($fb_id, $timePosted, $url, $content, $imageSource = null)
    {
        $fbPost = new FacebookPost();
        $fbPost->FBID = $fb_id;
        $fbPost->TimePosted = $timePosted;
        $fbPost->URL = $url;
        $fbPost->Content = $content;
        if ($imageSource) {
            $fbPost->ImageSource = $imageSource;
        }
        $fbPost->write();

        return $fbPost;
    }

    /**
     * Retrieve Facebook Page posts using the Facebook RESTful API
     *
     * @param int $limit
     * @return array|bool
     */
    public function getPostsFromFacebook($limit = 4)
    {
        $posts = array();

        $fb = new Facebook([
            'app_id' => $this->appID,
            'app_secret' => $this->appSecret,
            'default_graph_version' => 'v2.8',
        ]);
        $fb->setDefaultAccessToken($this->accessToken);

        try {
            $request = $fb->get('/' . $this->pageID . '/feed?fields=object_id,created_time,permalink_url,message,type&limit=' . $limit);
            $response = $request->getDecodedBody();

            foreach ($response['data'] as $iteration => $responseData) {
                if (isset($responseData['message'])) {
                    $posts[$iteration]['FBID'] = $responseData['id'];
                    $posts[$iteration]['TimePosted'] = $responseData['created_time'];
                    $posts[$iteration]['URL'] = $responseData['permalink_url'];
                    $posts[$iteration]['Content'] = $responseData['message'];
                }

                if ($responseData['type'] == "photo") {
                    if (isset($responseData['object_id'])) {
                        $subRequest = $fb->get('/' . $responseData['object_id'] . '?fields=images');
                        $subResponse = $subRequest->getDecodedBody();

                        // Get the largest image below the limit
                        $images = $subResponse['images'];
                        $maxWidth = 400;
                        $largestWidth = 0;
                        $largestIndex = 0;
                        // Loop through each supplied image object, remembering the largest below the limit
                        foreach ($images as $index => $image) {
                            if ($image['width'] < $maxWidth && $image['width'] > $largestWidth) {
                                $largestIndex = $index;
                                $largestWidth = $image['width'];
                            }
                        }
                        // Cherry-pick the source of the largest image asset
                        $posts[$iteration]['source'] = $images{$largestIndex}['source'];
                    }
                }
            }
            return $posts;
        } catch (FacebookResponseException $e) {
            // The Graph API returned an error
            error_log('FBFeed SilverStripe Module Exception #1: ' . $e->getMessage());
            exit;
        } catch (FacebookSDKException $e) {
            // Some other error occurred
            error_log('FBFeed SilverStripe Module Exception #2: ' . $e->getMessage());
            exit;
        }

        return false;
    }
}
