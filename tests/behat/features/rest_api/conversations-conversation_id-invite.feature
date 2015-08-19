Feature: Invite a user to the conversation, as Authenticated user.

 @api
 Scenario: The user should be able to invite valid user to the existing conversation.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
      "ids": [1, 2, 3],
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/1/invite"
    Then The REST API returns a 200 response

 @api
 Scenario: Invalid request to invite users to a conversation without sending ids.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/invite"
    Then The REST API returns a 400 response
 
 @api
 Scenario: Invite users to a conversation which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
      "ids": [1, 2, 3],
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/99999/invite"
    Then The REST API returns a 404 response
