Feature: Add a reply to a conversation, as Authenticated user.

  @api
  Scenario: Valid content is posted and successfully saved.
    Given I am logged in as a user with the authenticated role
    And I have an access token
     Given I have the payload:
     """
     {
       "text": "Blah blah"
     }
     """
    When I request "POST /api/v1/cs-pm/conversations/1/reply"
    Then The REST API returns a 201 response
  
  @api
  Scenario: Attempt to add a reply without any text.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/reply"
    Then The REST API returns a 400 response

  @api
  Scenario: Attempt to add a reply to a conversation that doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/999999/reply"
    Then The REST API returns a 404 response
