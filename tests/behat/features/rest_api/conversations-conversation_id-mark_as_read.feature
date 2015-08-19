Feature: Mark a conversation as read, no unread messages within, as Authenticated user.

 @api
 Scenario: The conversation successfully marked as read.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/1/mark-as-read"
    Then The REST API returns a 200 response

 @api
 Scenario: Attempt to mark a conversation which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/54/mark-as-read"
    Then The REST API returns a 404 response
