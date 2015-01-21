<?php
namespace DLV\WebsiteBundle\Services\Facebook;


use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class facebookPage
 * @package DLV\WebsiteBundle\Services\Facebook
 */
class FacebookPage
{

    /**
     * @var FacebookSession
     */
    protected static $session;

    /**
     * @param FacebookSession $session
     */
    public static function setSession($session)
    {
        self::$session = $session;
    }

    /**
     * @return FacebookSession
     */
    public static function getSession()
    {
        return self::$session;
    }

    /**
     * @param int $limit
     * @return FacebookPost[]
     * @throws \Facebook\FacebookRequestException
     */
    public static function fetchPosts($limit = 40)
    {
        $path = '/dangerlivevoltage/feed';
        $options = array('limit'=> $limit, 'include_hidden'=> false);
        $pageFeed = FacebookObject::load($path, $options);
        $rawPosts = self::buildPosts($pageFeed->getPropertyAsArray('data'));
        $filteredPosts = self::filterPosts($rawPosts);
        return self::getPostsData($filteredPosts);
    }

    /**
     * @param \Facebook\GraphObject[] $postsStream
     * @return FacebookPost[]
     */
    private static function buildPosts($postsStream)
    {
        $posts = array();
        foreach ($postsStream as $post) {
            $posts[] = new FacebookPost($post);
        }
        return $posts;
    }

    /**
     * @param FacebookPost[] $rawPosts
     * @return FacebookPost[]
     */
    private static function filterPosts($rawPosts)
    {
        $posts = array();
        foreach ($rawPosts as $post) {
            if (!$post->isHidden()) {
                $posts[] = $post;
            }
        }
        return $posts;
    }

    /**
     * @param FacebookPost[] $posts
     * @return array
     */
    private static function getPostsData($posts) {
        $postsData = array();
        foreach ($posts as $post) {
            $postsData[] = $post->toArray();
        }
        return $postsData;
    }
}