<?php

require_once 'Config.class.php';
require_once 'Message.class.php';
require_once 'TwitterClient.class.php';

class TwitterBot
{
    protected $twitter;
    protected $screenName;

    public function __construct( $name )
    {
        $this->screenName = $name;
        $this->twitter = new TwitterClient();
    }

    public function tweet( $tweet )
    {
        return $this->twitter->tweet( $tweet );
    }

    public function tweetRandom()
    {
        $tweet_contents = file_get_contents( Config::tweetsFile() );
        $tweet_contents = str_replace( "\r\n", "\n", $tweet_contents );
        $tweets = explode( "\n", $tweet_contents );

        return $this->twitter->tweet( $tweets[array_rand( $tweets )] );
    }

    public function reply()
    {
        $replies = $this->twitter->getReplies();
        $replies = $this->avoidOldReplies( $replies );
        $replies = $this->avoidUnnecessaryReplies( $replies );
        $replies = $this->avoidAlreadyReplied( $replies );
        $replies = $this->avoidLoopReplies( $replies );

        if ( count( $replies ) <= 0 )
            return array( 'Reply not exits.' );

        $results = array();
        $latest_reply_id = NULL;
        $replies = array_reverse( $replies );
        foreach ( $replies as $reply )
        {
            $reply_tweet = $this->createReplyTweet( $reply );
            $response = $this->twitter->tweet( $reply_tweet, $reply->id );
            if ( property_exists( $response, 'in_reply_to_status_id' ) &&
                 $response->in_reply_to_status_id )
            {
                $latest_reply_id = $response->in_reply_to_status_id;
            }
            $results[] = $response;
        }
        if ( $latest_reply_id !== NULL )
        {
            $this->saveAlreadyReplied( $latest_reply_id );
        }
        return $results;
    }

    protected function avoidOldReplies( $replies_response )
    {
        $replies = array();
        $now = strtotime( 'now' );
        $limit = $now - Config::OLD_REPLY_LIMIT * 60;
        foreach ( $replies_response as $reply )
        {
            $created_at = strtotime( $reply->created_at );
            if ( $created_at <= $limit )
                break;
            $replies[] = $reply;
        }
        return $replies;
    }

    protected function avoidUnnecessaryReplies( $replies_response )
    {
        $replies = array();
        foreach ( $replies_response as $reply )
        {
            if( $this->screenName === $reply->user->screen_name )
                continue;
            if(strpos( $reply->text, 'RT' ) != FALSE ||
               strpos( $reply->text, 'QT' ) != FALSE )
                continue;
            $replies[] = $reply;
        }
        return $replies;
    }

    protected function avoidAlreadyReplied( $replies_response )
    {
        $replies = array();
        $replied = trim( file_get_contents( Config::alreadyRepliedIdFile() ) );
        foreach ( $replies_response as $reply )
        {
            if ( (string)$reply->id === $replied )
                break;
            $replies[] = $reply;
        }
        return $replies;
    }

    /**
     * Replyを無限に繰り返さないように同じユーザーへのReply数に制限を設ける
     */
    protected function avoidLoopReplies( $replies_response )
    {
        $replyUserCounts = array();
        foreach( $replies_response as $reply )
        {
            if ( ! array_key_exists( $reply->user->screen_name, $replyUserCounts ) )
            {
                $replyUserCounts[$reply->user->screen_name] = 1;
            }
            else
            {
                $replyUserCounts[$reply->user->screen_name] += 1;
            }
        }

        $replies = array();
        foreach($replies_response as $reply)
        {
            if( $replyUserCounts[$reply->user->screen_name] < Config::REPLY_LOOP_LIMIT )
            {
                $replies[] = $reply;
            }
        }
        return $replies;
    }

    protected function createReplyTweet( $reply )
    {
        $tweet = '';
        $foods = require( Config::FoodsFile() );

        $reply_content = trim( $reply->text );
        $reply_content = preg_replace( "/(^@.+\s)(.+)/", '$2', $reply_content );
        if ( array_key_exists( $reply_content, $foods ) )
        {
            $isLike = $foods[$reply_content];
            if ( $isLike )
            {
                $tweet = $reply_content . Message::LIKE;
            }
            else
            {
                $tweet = $reply_content . Message::UNLIKE;
            }
        }
        else
        {
            $reply_contents = file_get_contents( Config::RepliesFile() );
            $reply_contents = str_replace( "\r\n", "\n", $reply_contents );
            $replies = explode( "\n", $reply_contents );
            $tweet = $replies[array_rand( $replies )];
        }

        $reply_tweet = '@' . $reply->user->screen_name . ' ' . $tweet;
        return $reply_tweet;
    }

    protected function saveAlreadyReplied( $latest_replied_id )
    {
        file_put_contents( Config::alreadyRepliedIdFile(), $latest_replied_id );
    }
}