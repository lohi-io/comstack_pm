Feature: List the available users to start conversations with, as Authenticated user.

 Scenario: The available users list successfully displayed.
   When I request "GET  /api/v1/cs-pm/users/available-users"
   Then The REST API returns a 200 response
   And scope into the "data" property
   And the properties exist:
    """
    type
    id
    name
    avatars
    """

 Scenario: No content (no available users)
   When I request "GET  /api/v1/cs-pm/users/available-users"
   Then The REST API returns a 204 response
