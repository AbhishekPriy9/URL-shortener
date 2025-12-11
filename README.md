## To run this project we need to
 -- install the project to local machine from github using "git clone https://github.com/AbhishekPriy9/URL-shortener.git"

 -- run "cd URL-shortener" to go inside root dir of the project.

 -- run "composer install" to install all the required packages.

 -- run "touch database/database.sqlite" to create an empty sqlite database file.

 -- run "cp .env.example .env" to copy env file from example env file.

 -- run "php artisan key:generate" to set the application key in the .env file.

 -- run "php artisan migrate:fresh --seed" to create the scheme and seed the db.

 -- run "php artisan test" to run the automated tests.

## Note

Setup the SMTP details in the .env file inside the root folder of the project before using it. I used gmail SMTP. all the keys are starting from MAIL_ in the env file.


## Requirement

php min version 8.2 (recommended 8.4)