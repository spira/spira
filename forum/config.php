<?php

if (! defined('APPLICATION')) {
    exit();
}

// Database
$Configuration['Database']['Dummy'] = true;

// EnabledApplications
$Configuration['EnabledApplications']['Conversations'] = 'conversations';
$Configuration['EnabledApplications']['Vanilla'] = 'vanilla';

// EnabledPlugins
$Configuration['EnabledPlugins']['jsconnect'] = true;

// Garden
$Configuration['Garden']['Title'] = 'Spira';
$Configuration['Garden']['Cookie']['Salt'] = '';
$Configuration['Garden']['Cookie']['Domain'] = '';
$Configuration['Garden']['Registration']['ConfirmEmail'] = false;
$Configuration['Garden']['Registration']['Method'] = 'Connect';
$Configuration['Garden']['Email']['SupportName'] = 'Spira';
$Configuration['Garden']['InputFormatter'] = 'Html';
$Configuration['Garden']['RewriteUrls'] = true;
$Configuration['Garden']['Installed'] = false;
