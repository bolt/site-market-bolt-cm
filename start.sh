mkdir -p /tmp/.composer
cp config/github.json /tmp/.composer/auth.json
./console migrations:migrate --no-interaction
./composer config -g github-oauth.github.com `head config/github`
./console orm:generate-proxies
./console bolt:extension-tester