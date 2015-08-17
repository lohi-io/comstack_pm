Feature: List a users conversations and Create a new conversation.
  
  Scenario: Valid response.  
    When I request "GET /cs-pm-api/v1/conversations" 
    Then The REST API returns a 200 response
    And scope into the "data" property
    And the properties exist:
    """
    type
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
    
    #204 No content (no conversations exist for this user).
    
  Scenario: Content successfully created.
    Given I have the payload:
    """
    {
    "recipients": "1988",
    "text": "Sample text"
    }
    """
    When I request "POST /cs-pm-api/v1/conversations"
    Then The REST API returns a 201 response

  Scenario: Start a new conversation without a text.
    When I request "POST /cs-pm-api/v1/conversations"
    Then The REST API returns a 400 response
