<?php

class Config
{
    const REPLY_LOOP_LIMIT = 5;
    const OLD_REPLY_LIMIT = 5;

    public static function tweetsFile()
    {
        return self::getFilePath( 'tweets.txt' );
    }
    public static function repliesFile()
    {
        return self::getFilePath( 'replies.txt' );
    }
    public static function foodsFile()
    {
        return self::getFilePath( 'foods.php' );
    }
    public static function alreadyRepliedIdFile()
    {
        return self::getFilePath( 'already_replied.id' );
    }

    protected static function getFilePath( $file_name )
    {
        return TUNACO_BASE_DIR . '/data/' . $file_name;
    }
}