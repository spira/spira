<?php

/**
 * Writes an anchor tag
 */
if (!function_exists('anchor')) {
    /**
     * Builds and returns an anchor tag.
     */
    function anchor($Text, $Destination = '', $CssClass = '', $Attributes = array(), $ForceAnchor = false) {
        if (!is_array($CssClass) && $CssClass != '') {
            $CssClass = array('class' => $CssClass);
        }

        // $Destination = str_replace('/forum/', '', parse_url($Destination)['path']);

        if ($Destination == '' && $ForceAnchor === false) {
            return $Text;
        }


        if (!is_array($Attributes)) {
            $Attributes = array();
        }

        $SSL = null;
        if (isset($Attributes['SSL'])) {
            $SSL = $Attributes['SSL'];
            unset($Attributes['SSL']);
        }

        $WithDomain = false;
        if (isset($Attributes['WithDomain'])) {
            $WithDomain = $Attributes['WithDomain'];
            unset($Attributes['WithDomain']);
        }

        $Prefix = substr($Destination, 0, 7);
        if (!in_array($Prefix, array('https:/', 'http://', 'mailto:')) && ($Destination != '' || $ForceAnchor === false)) {
            $Destination = Gdn::Request()->Url($Destination, $WithDomain, $SSL);
        }

        return '<a href="'.htmlspecialchars($Destination, ENT_COMPAT, C('Garden.Charset', 'UTF-8')).'"'.Attribute($CssClass).Attribute($Attributes).'>'.$Text.'</a>';
    }
}

$databaseConfig = [
    'Database.Name' => getenv('VANILLADATABASE_ENV_MYSQL_DATABASE'),
    'Database.Host' => getenv('VANILLADATABASE_PORT_3306_TCP_ADDR'),
    'Database.User' => getenv('VANILLADATABASE_ENV_MYSQL_USER'),
    'Database.Password' => getenv('VANILLADATABASE_ENV_MYSQL_PASSWORD'),
];

saveToConfig($databaseConfig, null, ['Save' => false]);
