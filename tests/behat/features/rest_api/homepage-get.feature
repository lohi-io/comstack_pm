Feature: Homepage GET Request
  In order to make sure the REST elements of the feature context work
  As a user
  I want to make a GET request of the homepage
  Scenario: Anonymous access
    When I request "GET /node"
    Then I get a "200" response
