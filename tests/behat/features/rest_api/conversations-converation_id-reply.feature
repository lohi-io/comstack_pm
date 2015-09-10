Feature: Add a reply to a conversation, as Authenticated user.

  Background: Logged in as Basic user

  @api
  Scenario: Valid content is posted and successfully saved.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    And I have the payload:
    """
    {
      "text": "Blah blah"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/1/reply"
    Then The REST API returns a 201 response
    And scope into the "data" property
    And the properties exist:
    """
    type
    conversation_id
    sender
    sent
    updated
    text
    weight
    edits
    deleted
    """
    And the "type" property is a string equalling "message"
    And the "conversation_id" property is an integer equalling "1"
    And the "text" property is a string equalling "Blah blah"
  
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
    And I have the payload:
    """
    {
      "text": "Blah blah"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/999999/reply"
    Then The REST API returns a 404 response
