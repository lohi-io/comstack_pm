Feature: List the available users to start conversations with, as Authenticated user.

  Background: Logged in as Basic user

  #Before run this the user should add friends manually.
  @api
  Scenario: The available users list successfully displayed.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/users/available-users"
    Then The REST API returns a 200 response
    And scope into the first "data" property
    And the properties exist:
    """
    type
    id
    name
    """
    And the "type" property is a string equalling "user"
    And the "id" property is an integer equalling "??-user id"
    And the "name" property is a string equalling "user name"
    # We don't test for the "avatars" property as this can be false if the user
    # doesn't have one.
  @api
  Scenario: No content (no available users).
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/users/available-users"
    Then The REST API returns a 204 response
