@json_inspection
Feature: Test json inspection payload
    In order to verify my json response
    As a developper
    I want to be able to check json node content

    Background: load JSON
        When I load JSON:
            """
            {
                "foo": "bar",
                "footrue": true,
                "foofalse": false,
                "foonull": null,
                "fooint": 1337,
                "foos": [
                    {"foo": "bar", "bar": "bar"},
                    {"foo2": "bar2"}
                ],
                "fooo": {
                    "foo": "bar"
                },
                "fooarray": [
                    "bar1",
                    "bar2"
                ]
            }
            """

    Scenario: Json nodes should be equal to specific values
        Then the JSON node "foo" should be equal to "bar"
        And the JSON node "footrue" should be equal to "true"
        And the JSON node "foofalse" should be equal to "false"
        And the JSON node "foonull" should be equal to "null"
        And the JSON node "fooint" should be equal to "1337"
        And the JSON node "foos[0].foo" should be equal to "bar"
        And the JSON node "fooo.foo" should be equal to "bar"

    Scenario: Json array should have expected size
        Then the JSON node "foos" should have 2 elements

    Scenario: Json array should contain specific values
        Then the JSON array node "fooarray" should contain "bar1" element
        And the JSON array node "fooarray" should contain "bar2" element

    Scenario: Json array should not contain specific values
        Then the JSON array node "fooarray" should not contain "bar3" element

    Scenario: Json nodes should contain specific values
        Then the JSON node "foo" should contain "a"
        And the JSON node "foos[0].foo" should contain "ba"
        And the JSON node "fooo.foo" should contain "ar"

    Scenario: Json nodes should not contain specific values
        Then the JSON node "foo" should not contain "z"
        And the JSON node "foos[0].foo" should not contain "baaaar"
        And the JSON node "fooo.foo" should not contain "bas"

    Scenario: Json nodes should exist
        Then the JSON node "foo" should exist
        And the JSON node "footrue" should exist
        And the JSON node "foofalse" should exist
        And the JSON node "foonull" should exist
        And the JSON node "fooint" should exist
        And the JSON node "foos[0].foo" should exist
        And the JSON node "foos" should exist
        And the JSON node "foos[0]" should exist
        And the JSON node "foos[1]" should exist
        And the JSON node "fooo.foo" should exist

    Scenario: Json nodes should not exist
        Then the JSON node "foo2" should not exist
        And the JSON node "foos[2]" should not exist
        And the JSON node "fooo.bar" should not exist

    Scenario: JSON should be valid against inline json schema
        Then the JSON should be valid according to this schema:
        """
            {
                "type": "object",
                "$schema": "http://json-schema.org/draft-03/schema",
                "required": [
                    "foo"
                ],
                "properties": {
                    "foo": {
                        "type": "string",
                        "required": true
                    },
                    "footrue": {
                        "type": "boolean",
                        "required": true
                    },
                    "foofalse": {
                        "type": "boolean",
                        "required": true
                    },
                    "foonull": {
                        "type": "null",
                        "required": true
                    },
                    "fooint": {
                        "type": "integer",
                        "required": true
                    },
                    "foos": {
                        "type": "array",
                        "required":true,
                        "maxItems": 2
                    },
                    "fooo": {
                        "type": "object"
                    }
                }
            }
            """

    Scenario: JSON should be valid against json schema file
        Then the JSON should be valid according to the schema "fixtures/json-schema.json"

    Scenario: JSON should be equal to given json
        Then the JSON should be equal to:
        """
        {
            "foo": "bar",
            "footrue": true,
            "foofalse": false,
            "foonull": null,
            "fooint": 1337,
            "foos": [
                {"foo": "bar", "bar": "bar"},
                {"foo2": "bar2"}
            ],
            "fooo": {
                "foo": "bar"
            },
            "fooarray": [
                "bar1",
                "bar2"
            ]
        }
        """

    Scenario: JSON path expression equal to inline json
        Then the JSON path expression "foos[?foo == 'bar'].bar" should be equal to json '["bar"]'
        Then the JSON path expression "foos[?foo == 'nobar'].bar" should be equal to json '[]'

    Scenario: JSON path expression equal to given json
        Then the JSON path expression "foos[?foo == 'bar'].bar" should be equal to:
        """
        ["bar"]
        """

    Scenario: JSON path expression have result
        Then the JSON path expression "fooo.foo" should have result

    Scenario: JSON path expression should not have result
        Then the JSON path expression "fooo.bar" should not have result
        Then the JSON path expression "foos[?foo == 'nobar'].bar" should not have result
