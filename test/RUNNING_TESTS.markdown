You can execute tests by simply running the php file:

    php test/rails/active_support/cases/inflector.php

or by using ./mrails ("php mrails" on Windows)

    ./mrails test:case active_support/inflector

The advantage of using mrails is that you can run multiple
cases at once providing the suite name like:

    ./mrails test:case active_support


This will run all the cases available at

    test/appname/active_support/cases/*.php

Using mrails you can also override default constants like

    ./mrails test:case active_support ENVIRONMENT=development

and even define your custom test reporter

    ./mrails test:case active_support reporter=JUnitXMLReporter


## Testing models

By default Rails will use the details under the testing section of your
config/database.yml file. You might use another database configuration
file by passing the db option to "mrails tests"

    ./mrails test:case active_record db=sqlite

will look for the database settings defined in config/sqlite.yml


## Testing controllers with Apache2

When testing controllers, Rails will use the default host
"rails.tests", you can override this by defining TESTING_URL in
your config/environment.php file.

You will have to add the line:

    127.0.0.1   rails.tests

To your hosts file which might be located at:

*Windows*   C:\WINDOWS\drivers\etc\hosts
*MacOS*     /private/etc/hosts
*Linux*     /etc/hosts

Then you'll have to add a virtual host in your apache config:

    NameVirtualHost *:80

    <VirtualHost *:80>
        DocumentRoot "path/to/rails/test"
        ServerName rails.tests
        ServerAlias rails.tests
    </VirtualHost>

Using Apache the testing folder is can only be accessed from
the locahost machine as defined in test/.httaccess


## Running rails core tests

You can Run Rails core tests on a fresh copy by calling

    php mrails tests:units

ActiveRecord tests will use sqlite by default.

You will need to point your web server to the framework path and you should be able to access http://rails.tests/rails/ping.php