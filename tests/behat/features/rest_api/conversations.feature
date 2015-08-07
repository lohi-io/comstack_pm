Feature: Homepage GET Request
  In order to make sure the REST elements of the feature context work
  As a user
  I want to make a GET request of the homepage
  
  Scenario: Basic user access   
    When I request "GET /cs-pm-api/v1/conversations" 
    Then I should get a "200" HTTP response 
    
    #204 No content (no conversations exist for this user).
    
  Scenario: Create a new conversation.
    Given I have the payload:
    """
    {
    "recipients": "1988",
    "text": "Sample text"
    }
    """
    When I request "POST /cs-pm-api/v1/conversations"
    Then The REST API returns a 201 response

  Scenario: Validation issue or otherwise, see issue text.
    Given I have the payload:
    """
    {
    "recipients": "text",
    "text": "       "
    }
    """
    When I request "POST /cs-pm-api/v1/conversations"
    Then The REST API returns a 400 response
