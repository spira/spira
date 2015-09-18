# Virtual Hosts
Ensure when you add a feature to a vhost that you check the other folders if they need to have the config added there
too. In general they will be identical, but there are some distinct differences between production and dev such as
production doesn't have the api mock proxy pass, and none of the dev vhosts.