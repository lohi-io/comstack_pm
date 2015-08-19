Feature: Delete messages that belong to a conversation, as Authenticated user.

 @api
 Scenario: Messages successfully deleted.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
      "ids": [1, 2, 3],
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/1/messages/delete"
    Then The REST API returns a 200 response

 @api
 Scenario: Attempt to delete messages without sending any ids, an invalid request.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/messages/delete"
    Then The REST API returns a 400 response

 @api
 Scenario: Attempt to delete messages from a conversation which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
      "ids": [1, 2, 3],
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/99999/messages/delete"
    Then The REST API returns a 404 response
