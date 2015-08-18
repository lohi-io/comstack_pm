Feature: Add a reply to a conversation, as Authenticated user.

  Scenario: Valid content is posted and successfully saved.
     Given I have the payload:
     """
     {
       "text": "Blah blah"
     }
     """
     When I request "POST /api/v1/cs-pm/conversations/1/reply"
     Then The REST API returns a 201 response

  Scenario: Attempt to add a reply without any text.
    When I request "POST /api/v1/cs-pm/conversations/1/reply"
    Then The REST API returns a 400 response

  Scenario: Attempt to add a reply to a conversation that doesn't exist.
    When I request "POST /api/v1/cs-pm/conversations/999999/reply"
    Then The REST API returns a 404 response
