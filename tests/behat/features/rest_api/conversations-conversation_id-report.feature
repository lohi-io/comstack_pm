Feature: Report a conversation as Authenticated user.

 @api
 Scenario: Successfully reported a conversation
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
      """
     {
     "reasons": [3],
     "other_reason": "no reason"
     "posts": [1, 2, 3],
     }
     """
    When I request "POST /api/v1/cs-pm/conversations/1/report"
    Then The REST API returns a 201 response

 @api
 Scenario: When user not selected any option, Validation issue
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/report"
    Then The REST API returns a 400 response

 @api
 Scenario: Attempt to report a conversation which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/99999/report"
    Then The REST API returns a 404 response
