Feature: List the available users to start conversations with, as Authenticated user.

 Scenario: To verify the "200" response.
   When I request "GET  /cs-pm-api/v1/users/available-users"
   Then I should get a 200 HTTP response
   And scope into the "data" property
   And the properties exist:
    """
    type
    id
    name
    avatars
    """
    
 Scenario: No content (no available users) then the user should get a 204 HTTP response
   When I request "GET  /cs-pm-api/v1/users/available-users"
   Then I should get a 204 HTTP response
