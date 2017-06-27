<?php

class SiteConfigExtension extends DataExtension
{
    private static $db = array(
        'FBAppID' => 'Varchar(255)',
        'FBAppSecret' => 'Varchar(255)',
        'FBAccessToken' => 'Varchar(255)',
        'FBPageID' => 'Varchar(255)',
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab("Root.Facebook", array(
                TextField::create("FBAppID", "Facebook App ID"),
                TextField::create("FBAppSecret", "Facebook App Secret"),
                TextField::create("FBAccessToken", "Facebook Access Token"),
                TextField::create("FBPageID", "Facebook Page ID")
        ));
    }
}
