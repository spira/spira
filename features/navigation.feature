# ./features/navigation.feature

Feature: Navigation
  As any user
  I want to be able to navigate the site

  Scenario: View navigation - desktop
    Given I am an anonymous user on a "desktop" browser
    When I am on the home page
    Then I should see a navigation section

  Scenario: View navigation - mobile
    Given I am an anonymous user on a "mobile" browser
    When I am on the home page
    Then I should be able to access the navigation from a button action

  Scenario: Use navigation
    Given I am an anonymous user on a "desktop" browser
    When I am on the home page
    And I click on a navigation item
    Then I should see that the page has changed
    And I should see the page I am on highlighted in the navigation