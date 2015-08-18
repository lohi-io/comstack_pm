Feature: Report a conversation as Authenticated user.

 Scenario: Successfully reported a conversation
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

 Scenario: When user not selected any option, Validation issue
   When I request "POST /api/v1/cs-pm/conversations/1/report"
   Then The REST API returns a 400 response

Scenario: Attempt to report a conversation which doesn't exist.
   When I request "POST /api/v1/cs-pm/conversations/99999/report"
   Then The REST API returns a 404 response
