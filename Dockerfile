FROM        rossriley/docker-nginx-pg-php
MAINTAINER  Ross Riley "riley.ross@gmail.com"

# Copy across the local files to the root directory
ADD . /var/www/