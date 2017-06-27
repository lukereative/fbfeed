<?php

class FacebookPost extends DataObject
{
    private static $db = array(
        'FBID' => 'Varchar(100)',
        'TimePosted' => 'Datetime',
        'URL' => 'Varchar(255)',
        'ImageSource' => 'Varchar(255)',
        'Content' => 'Text'
    );

    private static $casting = array(
        'PostSummary' => 'HTMLText'
    );

    private static $default_sort = 'TimePosted DESC';

    private function hyperlinkURLs($content)
    {
        $content = preg_replace(
            '$(\s|^)(https?://[a-z0-9_./?=&-]+)(?![^<>]*>)$i',
            '<a href="$2" target="_blank">$2</a>',
            DBField::create_field('Text', $content)->FirstParagraph(0)
        );
        $content = preg_replace(
            '$(\s|^)(www\.[a-z0-9_./?=&-]+)(?![^<>]*>)$i',
            '<a target="_blank" href="http://$2"  target="_blank">$2</a>',
            DBField::create_field('Text', $content)->FirstParagraph(0)
        );
        return $content;
    }

    public function getPostSummary()
    {
        return $this->hyperlinkURLs($this->Content);
    }
}
