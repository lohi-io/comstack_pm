Feature: Homepage GET Request
  In order to make sure the REST elements of the feature context work
  As a user
  I want to make a GET request of the homepage
  Scenario:Anonymous user access  
    When I request "GET /cs-pm-api/v1/conversations" 
    Then I should get a "200" HTTP response 
