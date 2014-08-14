require 'rubygems'
require 'bundler/setup'
require 'net/http'
require 'json'

# Load DSL and Setup Up Stages
require 'capistrano/setup'
require 'capistrano/docker'

set :namespace,         "bolt"
set :application,       "extensions"
set :password,          "bolt30080"
set :ports,             ["80"]
set :stage,             "production" ### Default stage
set :build_commands,    [
    'composer install --no-dev --prefer-dist --optimize-autoloader',
    'cp ../../config/github.json ./config/'
]
set :start_commands,    [
    "ln -sf `pwd`/config/#{fetch(:stage)}.php `pwd`/config/config.php",
    "composer selfupdate -q",
    "./console migrations:migrate --no-interaction",
    "./console orm:generate-proxies",
    "./console bolt:builder",
    "./console bolt:update",
]
set :volumes, {
    'postgresql'=>'/data/pgsql/',
    'satis'=>'/var/www/public/satis',
    'sessions'=>'/var/lib/php5'
}

set :proxies, {
    "bolt.rossriley.co.uk" => "80",
    "beta.extensions.bolt.cm" => "80"
}


task :production do
    set :branch,        "master"
    server 'bolt.rossriley.co.uk', user: 'docker', roles: %w{host}
end

