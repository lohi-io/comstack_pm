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
    """
    And scope into the "user" property
    And the properties exist:
    """
    type
    id
    name
    """
    And the "type" property is a string equalling "user"
    And I reset scope
    And scope into the "data" property
    And scope into the "permissions" property
    And the properties exist:
    """
    conversations
    messages
    """

  @api @restapi @get @expectsinvalid
  Scenario: No authorisation response
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/users/current-user"
    Then The REST API returns a 401 response

