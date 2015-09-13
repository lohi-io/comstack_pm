Feature: Return information about the current user, including the permissions they have.

  @api @restapi @get @expectsvalid
  Scenario: Authenticated user session.
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/users/current-user"
    Then The REST API returns a 200 response
    And scope into the "data" property
    And the properties exist:
    """
    user
    permissions
    preferences
    """

  @api @restapi @get @expectsinvalid
  Scenario: No authorisation response
    Given I am not logged in
    When I request "GET /api/v1/cs-pm/users/current-user"
    Then The REST API returns a 401 response

