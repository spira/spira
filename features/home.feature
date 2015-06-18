# tests/features/home.feature

Feature: Home page
  As any user
  I want to be able to visit the home page and see a title

  Scenario: Visiting home page
    Given I am an anonymous user
    When I go to the home page
    Then I should see "spira - AngularJS Seed App" as the page title
    And I should see "Spira" as the main heading