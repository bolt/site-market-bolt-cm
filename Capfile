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
set :deploy_to,         "domains/extensions.bolt.cm/private_html/"
set :build_commands,    [
    'composer install --no-dev --prefer-dist --optimize-autoloader',
    'cp ../../config/github.json ./config/',
    'cp ../../config/github ./config/',
    'cp ../../config/github-config.json ./config/'
]
# set :start_commands,    [
#     "ln -sf `pwd`/config/#{fetch(:stage)}.php `pwd`/config/config.php",
#     "composer selfupdate -q",
#     "./console migrations:migrate --no-interaction",
#     "./console orm:generate-proxies",
#     "cp config/github.json /root/.composer/auth.json",
#     "cp config/github-config.json /root/.composer/config.json",
#     "chmod -R 0777 /tmp",
#     "./console bolt:satis",
#     "./vendor/bin/satis build --skip-errors"
# ]



task :production do
    set :branch,        "master"
    server 'bolt.cm', user: 'bolt', roles: %w{host}
end

namespace :deploy do
    desc "Updates the code on the remote container"
    task :update do
        on roles :host do |host|
            info " Running Rsync to: #{host.user}@#{host.hostname}"
            run_locally do
                execute "rsync -rupl --exclude '.git' tmp/build/* #{host.user}@#{host.hostname}:#{fetch(:deploy_to)}/"
            end
        end
    end
end

desc 'Deploy a new release.'
task :deploy do
  set(:deploying, true)
  %w{ build update finished }.each do |task|
    invoke "deploy:#{task}"
  end
end
task default: :deploy
invoke 'load:defaults'

