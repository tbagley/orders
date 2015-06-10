set :user, "jlara"
set :branch, "master"
set :environment, "prod"
role :web, "10.1.1.155" # Your HTTP server, Apache/etc
role :app, "10.1.1.155" # This may be the same as your `Web` server
#role :db, "10.1.1.155", :primary => true # This is where Rails migrations will run