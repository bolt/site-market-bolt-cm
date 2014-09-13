require 'rubygems'
require 'rake'
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
set :deploy_path,        "domains/extensions.bolt.cm/private_html"
set :build_commands,    [
    'composer install --no-dev --prefer-dist --optimize-autoloader',
    'cp ../../config/github.json ./config/',
    'cp ../../config/github ./config/',
    'cp ../../config/github-config.json ./config/'
]
set :start_commands,    [
    "ln -sf `pwd`/config/#{fetch(:stage)}.php `pwd`/config/config.php",
    "curl -sS https://getcomposer.org/installer | php",
    "composer selfupdate -q",
    "./console migrations:migrate --no-interaction",
    "composer config -g github-oauth.github.com `head config/github`",
    "./console orm:generate-proxies",
    "./console bolt:builder",
]



task :production do
    set :branch,        "master"
    server 'bolt.cm', user: 'bolt', roles: %w{host}
end

namespace :deploy do
    desc "Updates the code on the remote container"
    task :push do
        on roles :host do |host|
            info " Running Rsync to: #{host.user}@#{host.hostname}"
            run_locally do
                execute "rsync -rupl --exclude '.git' tmp/build/* #{host.user}@#{host.hostname}:#{fetch(:deploy_path)}/"
            end
        end
    end
    
    desc "Updates the code on the remote container"
    task :start do
        on roles :host do |host|
            execute "cd #{fetch(:deploy_path)} && nohup ./start.sh > /dev/null 2>&1 &" 
        end
    end
end

Rake::Task[:deploy].clear_actions
desc 'Deploy a new release.'
task :deploy do
  set(:deploying, true)
  %w{ build push start finished }.each do |task|
    invoke "deploy:#{task}"
  end
end
task default: :deploy
invoke 'load:defaults'

