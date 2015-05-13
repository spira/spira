# Apache Virtual hosts (Only relevant if not using Docker)
To use the Apache virtual hosts you must have the following mods enabled:
* mod_rewrite
* mod_proxy
* mod_proxy_http
* mod_php5

To install nglume with apache, simply symlink the nglume*.conf files in this directory to your sites-available directory
(Usually /etc/apache2/sites-available), or if unable to symlink, simply copy the files.

1. Edit the nglume-config.conf file to use your desired parameters
2. Run `sudo a2ensite nglume-api nglume-app` (or change if you renamed the file)
3. Restart apache `sudo apachectl restart`
