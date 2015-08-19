Feature: Removing the valid user from the conversation, as Authenticated user.

 @api
 Scenario: The user was removed successfully from the conversation.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/1/leave"
    Then The REST API returns a 200 response

 @api
 Scenario: Attempt to remove user from a conversation which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/99999/leave"
    Then The REST API returns a 404 response
