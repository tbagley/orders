set :application, "Crossbones"

set :user, "jlara"
set :stages, %w(production staging)
set :default_stage, "staging"
set :deploy_to, "/var/www/crossbones-git"

require 'capistrano/ext/multistage'

#set :composer_path, "./composer.phar"

#require 'capistrano/composer'

#set :composer_path, "./composer.phar"

set :repository,  "git@git.gtcwork.net:gtc/crossbones.git"

set :scm_username, "jlara"

set :ssh_options, {:forward_agent => true}

set :use_sudo, true

default_run_options[:pty] = true

set :deploy_via, :remote_cache

set :keep_releases,   3

set :normalize_asset_timestamps, false

set :copy_exclude, [".git", ".DS_Store", ".gitignore", ".gitmodules", "Capfile", "config/deploy.rb", "REVISION", "controllers/testing"]

set :scm, :git

role(:web)
role(:app)
#role(:db, :primary => true)

before "deploy:symlink", "directories:symlink"
before "deploy:symlink", "composer:ask_composer_run"

before "deploy:symlink", "composer:install"

#after "deploy:symlink", "deploy:cleanup"

namespace :directories do
    task :symlink, :roles => :app do
        run "ln -nfs #{shared_path}/logs #{release_path}/logs"
        run "ln -nfs #{shared_path}/temp_files #{release_path}/temp_files"
        #run "ln -nfs #{shared_path}/vendor #{release_path}/vendor"
    end
end

namespace :composer do

    task :ask_composer_run do
        set(:confirmed) do
            answer = Capistrano::CLI.ui.ask "Run Capistrano? (Y) "
            if answer == 'Y' then true else false end
        end

        if fetch(:confirmed) then
            puts "\nCapistrano Ready!"
        else
            puts "\nCapistrano Skipped!"
        end

    end

    task :install do
        if fetch(:confirmed)
            run "sh -c 'cd #{latest_release} && curl -s http://getcomposer.org/installer | php'"
            run "sh -c 'cd #{release_path} && php composer.phar install'"
        end
    end
end

