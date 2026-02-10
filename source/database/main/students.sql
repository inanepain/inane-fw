CREATE TABLE students (
	data json,
	CHECK (jsonschema_matches(
			JSON('
        {
          "type": "object",
          "properties": {
            "firstName": {
              "type": "string"
            },
            "lastName": {
              "type": "string"
            },
            "age": {
              "type": "integer",
              "minimum": 0
            }
          }
        }
        '),
			data
		   )));

