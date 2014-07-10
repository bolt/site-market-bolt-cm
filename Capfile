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
set :ports,             {"30080"=>"80"}
set :stage,             "production" ### Default stage
set :build_commands,    [
    'composer install --no-dev --optimize-autoloader'
]
set :start_commands,    [
    "ln -sf `pwd`/config/#{fetch(:stage)}.php `pwd`/config/config.php",
    "./console migrations:migrate --no-interaction"
]
set :volumes, {
    'postgresql'=>'/data/pgsql/',
    'satis'=>'/var/www/public/satis'
}

set :proxies, {
    "bolt.rossriley.co.uk" => "30080"
}


task :production do
    set :branch,        "master"
    server 'docker.oneblackbear.com', user: 'docker', roles: %w{host}
end

