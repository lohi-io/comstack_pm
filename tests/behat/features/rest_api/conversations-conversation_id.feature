Feature: Test the endpoint for specific conversations with the available HTTP methods - GET and DELETE.

 Scenario: Authenticated user session.
   When I request "GET /api/v1/cs-pm/conversations/1"
   Then I should get a 200 HTTP response
   And scope into the "data" property
   And the properties exist:
    """
    id
    participants
    historical_participants
    started_by
    last_updated_by
    started
    updated
    messages_count
    unread_count
    """

 Scenario: Authenticated user session with wrong conversation ID
   When I request "GET /api/v1/cs-pm/conversations/452145"
   Then I should get a 404 HTTP response

 Scenario: Authenticated user session.
   When I request "DELETE /api/v1/cs-pm/conversations/1"
   Then I should get a 200 HTTP response

Scenario: Authenticated user session with wrong conversation ID
   When I request "DELETE /api/v1/cs-pm/conversations/23512"
   Then I should get a 404 HTTP response
