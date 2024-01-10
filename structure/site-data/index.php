<?php
function getSiteDataDir(){return __DIR__ . '/../../private/data';}
function setupSiteDataDir(){ $dir= getSiteDataDir();if(!is_dir( getSiteDataDir())) mkdir( getSiteDataDir(),true);}
function getSiteData(string $name)
{
    global $siteData;
    return $siteData[$name] = $siteData[$name] ?? readJson(setupSiteDataDir() . '/' . $name . '.json');
}
function setSiteData(string $name)
{
    global $siteData;
    serializeJson($siteData[$name],setupSiteDataDir() . '/' . $name . '.json');
    reloadSiteData();
}