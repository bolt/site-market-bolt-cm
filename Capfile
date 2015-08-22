# Load DSL and Setup Up Stages
require 'capistrano/setup'
require 'capistrano/simpledeploy'

set :application,   "bolt-extensions"
set :deploy_to,     "domains/new-extensions.bolt.cm/private_html_real"
set :repo_url,      "git@github.com:bolt/bolt-extensions.git"
set :stage,         "production" ### Default stage

set :composer_roles, :web
set :composer_install_flags, '--no-dev --prefer-dist --no-interaction --quiet'

set :build_commands,    [
    'composer install --no-dev --prefer-dist --optimize-autoloader',
    'cp ../../config/github.json ./config/',
    'cp ../../config/github ./config/',
    'cp ../../config/github-config.json ./config/',
    'cp ../../config/env.php ./config/'
]


task :production do
    set :branch,        "feature/new-layout"
    server 'bolt.cm', user: 'bolt', roles: %w{web}
end

namespace :deploy do
    
    desc "Updates the code on the remote container"
    task :start do
        on roles :web do |host|
            begin execute "pkill -f 'start.sh'" rescue nil end 
            execute "cd #{fetch(:deploy_to)}; ((nohup ./start.sh &>/dev/null) &)" 
        end
    end
    
    task :secrets do
        on roles :web do
            upload! "config/env.php", "#{fetch(:deploy_to)}/config/env.php"
            upload! "config/github", "#{fetch(:deploy_to)}/config/github"
            upload! "config/github-config.json", "#{fetch(:deploy_to)}/config/github-config.json"
            upload! "config/github.json", "#{fetch(:deploy_to)}/config/github.json"
        end
    end
    
    task :symlink do
        on roles :all do
            execute "ln -fs production.php #{fetch(:deploy_to)}/config/config.php"
        end
    end
    
end

namespace :composer do
    task :symlink do 
        on roles :web do
           execute "cd #{fetch(:deploy_to)}; ln -fs ~/composer composer.phar" 
        end
    end
end
    
before "deploy", "composer:install_executable"
before "deploy", "composer:symlink"
after "deploy", "deploy:symlink"
after "deploy", "deploy:secrets"
#after "deploy", "deploy:start"

