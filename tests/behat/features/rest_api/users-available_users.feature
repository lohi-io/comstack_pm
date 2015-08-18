Feature: List the available users to start conversations with, as Authenticated user.

  @api
  Scenario: The available users list successfully displayed.
    Given I am logged in as a user with the authenticated role
    When I request "GET  /api/v1/cs-pm/users/available-users"
    Then The REST API returns a 200 response
    And scope into the first "data" property
    And the properties exist:
    """
    type
    id
    name
    avatars
    """

  @api
  Scenario: No content (no available users)
    Given I am logged in as a user with the authenticated role
    When I request "GET  /api/v1/cs-pm/users/available-users"
    Then The REST API returns a 204 response
