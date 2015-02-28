<?php
namespace DLV\WebsiteBundle\Services\Facebook;


use Facebook\GraphObject;

/**
 * Class FacebookPost
 * @package DLV\WebsiteBundle\Services\Facebook
 */
class FacebookPost
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var GraphObject
     */
    private $from;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $title = 'Danger Live Voltage status update';
    /**
     * @var \DateTime
     */
    private $created;
    /**
     * @var string
     */
    private $message = null;
    /**
     * @var string
     */
    private $picture = null;
    /**
     * @var string
     */
    private $video = null;
    /**
     * @var array
     */
    private $link = array();
    private $comments = array();
    private $likes;
    /**
     * @var array
     */
    private $obsoleteData = array(
        'id',
        'from',
        'type',
        'privacy',
        'created_time',
        'updated_time'
    );
    /**
     * @var array
     */
    private $data = array();
    /**
     * @var bool
     */
    private $hidden = false;

    /**
     * @param GraphObject $post
     */
    public function __construct(GraphObject $post)
    {
        FacebookObject::register($post);
        $this->id = $post->getProperty('id');
        $this->from = $post->getProperty('from');
        $this->type = $post->getProperty('type');
        $this->created = new \DateTime($post->getProperty('created_time'));
        $availableData = $post->getPropertyNames();
        foreach ($availableData as $dataKey) {
            if (!in_array($dataKey, $this->obsoleteData)) {
                $data = $post->getProperty($dataKey);
                if ($data instanceof GraphObject) {
                    $data = $data->asArray();
                }
                $this->data[$dataKey] = $data;
            }
        }

        if (array_key_exists('story',$this->data)) {
            switch ($this->data['story']) {
                case 'Danger Live Voltage likes a post.':
                case 'Danger Live Voltage likes a status.':
                case 'Danger Live Voltage likes a link.':
                case 'Danger Live Voltage commented on a post.':
                case 'Danger Live Voltage commented on a status.':
                case 'Danger Live Voltage commented on a link.':
                    $this->hidden = true;
                    break;
                default:
                    $to = ((array_key_exists('to', $this->data))? $this->data['to'] : null);
                    $this->title = self::getTitleFromStory($this->data['story'], $this->data['story_tags'], $to);
                    break;
            }
        }
        else {
            $from = $this->from;
            $to = ((array_key_exists('to', $this->data))? $this->data['to'] : null);
            $message_tags = ((array_key_exists('message_tags', $this->data))? $this->data['message_tags'] : null);
            $this->title = self::createTitleFromData($from, $to, $message_tags, $this->type);
        }

        if (array_key_exists('message',$this->data)) {
            $message = htmlentities($this->data['message']);
            $message_tags = ((array_key_exists('message_tags', $this->data))? $this->data['message_tags'] : array());
            $this->message = self::parseMessage($message, $message_tags, true);
        }
        elseif ($this->type == 'status') {
            $this->hidden = true;
        }

        if ($this->type == 'photo') {
            $photoObject = FacebookObject::load('/' . $this->data['object_id']);
            /** @var Graphobject[] $images*/
            $images = $photoObject->getPropertyAsArray('images');
            $this->picture = $images[0]->getProperty('source');
        }

        if ($this->type == 'video') {
            $this->video = $this->data['source'];
        }

        if($this->type == 'link' || $this->type == 'event') {
            $this->link['url'] = $this->data['link'];
            $linkData = array('name','description','caption','picture');
            foreach ($linkData as $dataKey) {
                if (array_key_exists($dataKey,$this->data)) $this->link[$dataKey] = $this->data[$dataKey] ;
            }
            if($this->type == 'event') {
                $this->type = 'link';
                if (strpos('facebook.com', $this->link['url']) !== false) {
                    $this->link['url'] = 'https://www.facebook.com' . $this->link['url'];
                }
            }
            if (!is_null($this->message)) {
                if (substr_count($this->message,'class="fb-message-link"') > 1 ) {
                    $this->type = 'status';
                    if (array_key_exists('picture', $this->data)) {
                        $this->type = 'photo';
                        $this->picture = $this->data['picture'];
                    }
                }
            }
        }
        $this->comments = self::parseComments((array_key_exists('comments', $this->data)? $this->data['comments'] : array()));
        $this->likes = self::parseLikes((array_key_exists('likes', $this->data)? $this->data['likes'] : array()));
    }

    private static function parseLikes($likes) {
        $likeString = '';
        $userLikes = false;
        $likesCount = 0;
        if (array_key_exists('data', $likes)) {
            foreach ($likes['data'] as $like) {
                if (FacebookObject::isLoaded('/me')) {
                    $me = FacebookObject::load('/me');
                    if ($like->id == $me->getProperty('id')) {
                        $userLikes = true;
                    }
                    else {
                        $likesCount++;
                    }
                }
                else {
                    $likesCount++;
                }
            }
        }
        if ($likesCount > 0) {
            if ($userLikes) {
                $likeString = 'You and ' . $likesCount . ' other vip' . (($likesCount > 1)? '\'s' : '') . ' Rock this';
            }
            else {
                $likeString = $likesCount . ' vip' . (($likesCount > 1)? '\'s' : '') . ' Rock this';
            }
        }
        elseif ($userLikes) {
            $likeString = 'You Rock this';
        }
        return $likeString;
    }

    /**
     * @param array $comments
     */
    private static function parseComments($comments) {
        $commentList = array();
        foreach ($comments as $comment) {
            if (is_array($comment)) {
                $comment = $comment[0];
                $user = $comment->from;
                $userId = $user->id;
                $user = FacebookObject::load('/' . $userId);
                $userPicture = FacebookObject::load('/' . $userId . '/picture', array('redirect'=> false, 'type'=> 'square'))->getProperty('url');
                $userName = $user->getProperty('name');
                $userLink = $user->getProperty('link');
                $userProfile = '<a class="facebook-link" href="' . $userLink . '">' . $userName . '</a>';

                $message = htmlentities($comment->message);
                $message_tags = ((array_key_exists('message_tags', get_object_vars($comment)))? $comment->message_tags : array());
                $commentMessage = self::parseMessage($message, $message_tags, true);

                $commentLikes = $comment->like_count;
                $commentLikesString = '';
                if ($commentLikes > 0) {
                    $commentLikesString = $commentLikes . ' vip ' . (($commentLikes > 1)? '\'s' : '') . ' Rock this.';
                    if ($commentLikes == 1 && $comment->user_likes) {
                        $commentLikesString = 'You Rock this';
                    }
                    if ($comment->user_likes) {
                        $commentLikesString = 'You and ' . ($commentLikes - 1) . ' other vip' . (($commentLikes > 2)? '\'s' : '') . ' Rock this';
                    }
                }

                $commentList[] = array(
                    'id'=> $comment->id,
                    'user'=> array(
                        'id'=> $userId,
                        'profile'=> $userProfile,
                        'picture'=> $userPicture,
                    ),
                    'message'=> $commentMessage,
                    'likes'=> $commentLikesString
                );
            }
        }
        return $commentList;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    public function toArray() {
        $post = array(
            'id'=> $this->id,
            'title'=> $this->title,
            'date'=> $this->created->format('d/m/Y'),
            'likes'=> $this->likes,
            'comments'=> $this->comments
        );
        if (!is_null($this->message)) $post['message'] = $this->message;
        if (!is_null($this->picture)) $post['picture'] = $this->picture;
        if (!is_null($this->video)) $post['video'] = $this->video;
        if ($this->type == 'link') $post['link'] = $this->link;

        return $post;
    }

    /**
     * @param string $message
     * @param array $tags
     *
     * @return string
     */
    private static function parseMessage($message, $tags = array(), $parseLinks = false)
    {
        if ($parseLinks) {
            $message_lines = explode("\n", $message);
            $message_parts = array();
            foreach ($message_lines as $line) {
                $line_parts = explode(' ', $line);
                foreach ($line_parts as $linepart) {
                    $message_parts[] = $linepart;
                }
            }
            $message_links = array();
            foreach ($message_parts as $part) {
                $partIsLink = false;
                if (preg_match('/^http:\/\//', $part)) $partIsLink = true;
                elseif (preg_match('/^https:\/\//', $part)) $partIsLink = true;

                if ($partIsLink) {
                    $message_links[] = $part;
                    $link = '<a class="fb-message-link" href="'. $part . '" target="_blank">'. $part . '</a>';
                    $message = substr_replace($message,$link,strpos($message,$part),strlen($part));
                }
            }
        }
        foreach ($tags as $tag) {
            if (is_array($tag)) {
                $tag = $tag[0];
            }
            $path = '/' . $tag->id;
            $graphObject = FacebookObject::load($path);
            $linkUrl = $graphObject->getProperty('link');
            $name = htmlentities($tag->name);
            $link = '<a class="facebook-link" href="' . $linkUrl . '" target="_blank">' . $name . '</a>';
            if (strpos($message, $name) !== false) {
                $message = substr_replace($message, $link, (strpos($message, $name)), strlen($name));
            }
        }
        return $message;
    }

    /**
     * @param string $story
     * @param array $tags
     * @param array $to
     *
     * @return string
     */
    private static function getTitleFromStory($story, $tags = array(), $to = null)
    {
        $title = htmlentities($story);
        foreach ($tags as $tag) {
            $tag = $tag[0];
            $path = '/' . $tag->id;
            $graphObject = FacebookObject::load($path);
            $linkUrl = $graphObject->getProperty('link');
            $name = htmlentities($tag->name);
            if ($name=="") {
                continue;
            }
            if ($tag->type == "event" && strpos($tag->type, $story) == false) {
                $name = 'event';
            }
            $link = '<a class="facebook-link" href="' . $linkUrl . '" target="_blank">' . $name . '</a>';
            $title = substr_replace($title, $link, (strpos($title, $name)), strlen($name));
        }
        if (!is_null($to)) {
            foreach ($to as $toTag) {
                $name = htmlentities($toTag->name);
                if (strpos($title,$name) !== false) {
                    $parseToTag = true;
                    foreach ($tags as $tag) {
                        if ($toTag->name == $tag[0]->name) $parseToTag = false;
                    }
                    if ($parseToTag) {
                        $path = '/' . $toTag->id;
                        $graphObject = FacebookObject::load($path);
                        $linkUrl = $graphObject->getProperty('link');
                        if (is_null($linkUrl) && array_key_exists('location',get_object_vars($toTag))) {
                            $linkUrl = 'https://www.facebook.com/events/' . $toTag->id;
                        }
                        $link = '<a class="facebook-link" href="' . $linkUrl . '" target="_blank">' . $name . '</a>';
                        $title = substr_replace($title, $link, (strpos($title, $name)), strlen($name));
                    }
                }
            }

        }
        return $title;
    }

    private static function createTitleFromData(GraphObject $from, $to, $tags, $type)
    {
        $title = '';
        $tagged = false;
        $tagLinks = array();
        $toLinks = array();
        $fromUrl = FacebookObject::load('/' . $from->getProperty('id'))->getProperty('link');
        $fromLink = '<a class="facebook-link" href="' . $fromUrl . '" target="_blank">' . $from->getProperty('name') . '</a>';
        if (!is_null($to)) {
            foreach ($to as $recipient) {
                $path = '/' . $recipient->id;
                $graphObject = FacebookObject::load($path);
                $linkUrl = $graphObject->getProperty('link');
                if (is_null($linkUrl) && array_key_exists('location',get_object_vars($recipient))) {
                    //$linkUrl = 'https://www.facebook.com/events/' . $recipient->id;
                    continue;
                }
                $link = '<a class="facebook-link" href="' . $linkUrl . '" target="_blank">' . $recipient->name . '</a>';
                $toLinks[] = $link;
            }
        }
        if (!is_null($tags)) {
            foreach ($tags as $tag) {
                $tag = $tag[0];
                $path = '/' . $tag->id;
                $graphObject = FacebookObject::load($path);
                $linkUrl = $graphObject->getProperty('link');
                $link = '<a class="facebook-link" href="' . $linkUrl . '" target="_blank">' . $tag->name . '</a>';
                $tagLinks[] = $link;
                if (in_array($link, $toLinks)) $tagged = true;
            }
        }
        if ($from->getProperty('name') == 'Danger Live Voltage') {
            if ($tagged) {
                $title = self::createTitleFromType($fromLink, null, $type);
            }
            else {
                if (count($toLinks)) {
                    $title = self::createTitleFromType($fromLink, $toLinks[0], $type);
                }
                else {
                    $title = self::createTitleFromType($fromLink, null, $type);
                }
            }
        }
        elseif ($tagged) {
            foreach ($toLinks as $link) {
                if(preg_match('/Danger Live Voltage/',$link)) {
                    $title = $link;
                }
            }
            $title.= ' was tagged in ';
            $title.= $fromLink;
            $title.= '\'s post';
        }
        else {
            $toLink = null;
            foreach ($toLinks as $link) {
                if(preg_match('/Danger Live Voltage/',$link)) {
                    $toLink = $link;
                }
            }
            $title = self::createTitleFromType($fromLink, $toLink, $type);
        }

        return $title;
    }

    private static function createTitleFromType($from, $to, $type) {
        $action = '';
        switch ($type) {
            case 'status':
                if (is_null($to)) $action = 'status update';
                if (is_string($to)) $action = 'posted to';
                break;
            default:
                if (is_null($to)) $action = 'shared a' . ((in_array(substr(strtolower($type), 0, 1),array('a','e','i','o','u','y'))) ? 'n' : '' ) . ' ' . $type ;
                if (is_string($to)) $action = 'shared a' . ((in_array(substr(strtolower($type), 0, 1),array('a','e','i','o','u','y'))) ? 'n' : '' ) . ' ' . $type . ' to';
                break;
        }
        $title = $from . ' ' . $action;
        if (is_string($to)) $title.= ' ' . $to;
        return $title;
    }
}