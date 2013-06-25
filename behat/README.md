# Functional testing with Behat

## Installation

Download the Selenium server ([download link](http://selenium.googlecode.com/files/selenium-server-standalone-2.33.0.jar))
and save it in this directory as:

    selenium-server-standalone.jar

Install PHP curl extension and enable it in php.ini:

    extension=php_curl.dll

Update composer if needed

    composer update

## Run the tests

Launch the Selenium server:

    java -jar behat/selenium-server-standalone.jar

In another console, launch the tests:

    vendor/behat/behat/bin/behat --config behat/behat.yml --ansi

You can launch a specific "Feature" or "Scenario" by its name:

    vendor/behat/behat/bin/behat --config behat/behat.yml --ansi --name "Login redirection"

## Write the tests

Here are all the standard Behat-Mink commands: [Behat-Mink reference](https://gist.github.com/mnapoli/5848556).

Custom commands are:

```cucumber
Given I am logged in
Given I wait for [the] page to finish loading
When I wait [for] 5 seconds

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
