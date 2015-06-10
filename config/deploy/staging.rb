set :user, "jlara"
set :branch, "master"
set :environment, "stage"
role :web, "192.168.55.120" # Your HTTP server, Apache/etc
role :app, "192.168.55.120" # This may be the same as your `Web` server
#role :db, "192.168.55.120", :primary => true # This is where Rails migrations will run