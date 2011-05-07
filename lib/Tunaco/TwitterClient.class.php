<?php

require_once 'HTTP/Request2.php';
require_once 'HTTP/OAuth/Consumer.php';
require_once 'Config.class.php';

class TwitterClient
{
    const OAUTH_CONSUMER_KEY = '';
    const OAUTH_CONSUMER_SECRET = '';
    const OAUTH_ACCESS_TOKEN = '';
    const OAUTH_ACCESS_TOKEN_SECRET = '';

    protected $oAuthConsumer;

    public function __construct()
    {
        $this->oAuthConsumer =
            new HTTP_OAuth_Consumer( TwitterClient::OAUTH_CONSUMER_KEY,
                                     TwitterClient::OAUTH_CONSUMER_SECRET );

        $consumer_request = new HTTP_OAuth_Consumer_Request;
        $http_request = new HTTP_Request2();
        $http_request->setConfig( 'ssl_verify_peer', false );
        $consumer_request->accept( $http_request );
        $this->oAuthConsumer->accept( $consumer_request );
        $this->oAuthConsumer->setToken( TwitterClient::OAUTH_ACCESS_TOKEN );
        $this->oAuthConsumer->setTokenSecret( TwitterClient::OAUTH_ACCESS_TOKEN_SECRET );
    }

    public function tweet( $tweet, $in_reply_status_id = NULL )
    {
        $url = 'https://api.twitter.com/1/statuses/update.json';
        $value = array( 'status' => $tweet );
        if ( $in_reply_status_id !== NULL )
        {
            $value[ 'in_reply_to_status_id' ] = $in_reply_status_id;
        }
        $response = $this->oAuthConsumer->sendRequest( $url, $value, 'POST' );
        return json_decode( $response->getBody() );
    }

    public function getReplies( $include_rts = FALSE )
    {
        $url = 'https://api.twitter.com/1/statuses/mentions.json';
        $value = array( 'include_rts' => $include_rts ? 1 : 0 );
        $response = $this->oAuthConsumer->sendRequest( $url, $value, 'GET' );

        return json_decode( $response->getBody() );
    }
}
