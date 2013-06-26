# Functional testing with Behat

## Installation

Download the Selenium server ([download link](http://selenium.googlecode.com/files/selenium-server-standalone-2.33.0.jar))
and save it in this directory as:

    selenium-server-standalone.jar

Download the [Chrome driver](https://code.google.com/p/selenium/wiki/ChromeDriver) into your path, for example for Windows:

    C:\cygwin\usr\local\bin

Install PHP curl extension and enable it in php.ini:

    extension=php_curl.dll

Update composer if needed

    composer update

## Run the tests

Launch the Selenium server:

    cd behat/
    ./start-selenium.sh

In another console, launch the tests:

    cd behat/
    ./tests.sh

You can launch a specific "Feature" or "Scenario" by its name:

    cd behat/
    ../vendor/behat/behat/bin/behat --config behat.yml --ansi --name "Login redirection"

## Write the tests

Here are all the standard Behat-Mink commands: [Behat-Mink reference](https://gist.github.com/mnapoli/5848556).

Custom commands are:

```cucumber
Given I am logged in
Given I wait for [the] page to finish loading
When I wait [for] 5 seconds

# Form
Then the field "field" should have error: "Some error"

# Popup
Then I should see the popup "Popup title"

# Datagrid
Then I should see the "users" datagrid
Then the "users" datagrid should contain 1 row
Then the row 1 of the "users" datagrid should contain:
  | nom            | email | detailsUser |
  | Administrateur | admin | Éditer      |
Then the column "name" of the row 2 of the "users" datagrid should contain "Bob"
```
