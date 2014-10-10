require 'rubygems'
require 'rake'
require 'bundler/setup'
require 'net/http'
require 'json'

# Load DSL and Setup Up Stages
require 'capistrano/setup'
require 'capistrano/docker'

set :namespace,         "bolt"
set :application,       -> {"runner_"+rand(36**6).to_s(36)}
set :password,          "bolt30080"
set :ports,             ["80"]
set :volumes,           []
set :links,             []
set :stage,             "production" ### Default stage

set :package, ENV['package']
set :version, ENV['version']

set :docker_image,      "rossriley/docker-bolt"
set :env_vars,          {
    'BOLT_EXT'=>"#{fetch(:package)} #{fetch(:version)}"
}




task :production do
    set :branch,        "master"
    server 'bolt.rossriley.co.uk', user: 'docker', roles: %w{host}
end


namespace :docker do
    task :run do 
        on roles :host do
            execute build_run_command
            port = capture "docker port #{fetch(:docker_appname)} 80"
            puts port
        end
    end
end