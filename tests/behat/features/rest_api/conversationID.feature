Feature: GET a specific conversation using ID, and DELETE a specific conversation.
 
 Scenario: Authenticated user session.
   When I request "GET /cs-pm-api/v1/conversations/18"
   Then I should get a "200" HTTP response 
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
   When I request "GET /cs-pm-api/v1/conversations/452145"
   Then I should get a "404" HTTP response 
   
 Scenario: Authenticated user session.
   When I request "DELETE /cs-pm-api/v1/conversations/18"
   Then I should get a "200" HTTP response 
   
Scenario: Authenticated user session with wrong conversation ID
   When I request "DELETE /cs-pm-api/v1/conversations/23512"
   Then I should get a "404" HTTP response
