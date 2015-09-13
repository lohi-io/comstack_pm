Feature: List the available users to start conversations with, as Authenticated user.

  @api @restapi @get @expectsvalid
  Scenario: The available users list successfully displayed.
    Given I'm logged in as testy
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
    And the "id" property is a integer equalling "1"
    And the "name" property is a string equalling "user name"
    # We don't test for the "avatars" property as this can be false if the user
    # doesn't have one.

  #@api @restapi @get @expectsinvalid
  #Scenario: No content (no available users).
  #  Given I'm logged in as testy
  #  And I have an access token
  #  When I request "GET /api/v1/cs-pm/users/available-users"
  #  Then The REST API returns a 204 response
