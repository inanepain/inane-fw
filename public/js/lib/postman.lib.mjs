/**
 * test-response
 *
 * Wrap some response tests in easy to use function calls.
 *
 * Usage:
 * `const tr = pm.require('test-response');`
 * // test response time
 * tr.testResponseTimeLessThan() // 200ms
 * tr.testResponseTimeLessThan(300) // 300ms
 *
 * @version 4
 */

let req = Object.create(null);
req.json = JSON.parse(pm.request.body.raw);

/**
 * Tests if the active Postman environment matches the specified environment name.
 *
 * @param {string} [environment=undefined] - The name of the environment to check against the active environment.
 */
function testEnvironment(environment = undefined) {
    pm.test(`Check the active environment is "${environment}"`, () => {
        pm.expect(pm.environment.name).to.eql(environment);
        // pm.environment.name.to.eql(environment);
    });
}

/**
 * Tests if the response time is less than the specified limit.
 *
 * @param {number} [limit=200] - The maximum response time in milliseconds.
 */
function testResponseTimeLessThan(limit = 200) {
    pm.test(`Response time ${pm.response.responseTime}ms is less than ${limit}ms`, () => {
        pm.expect(pm.response.responseTime).to.be.below(limit);
    });
}

//#region HEADERS
/**
 * Tests if the response status code matches the expected code.
 *
 * @param {number} [code=200] - The expected status code to test against the response.
 */
function testStatusCode(code = 200) {
    pm.test(`Status code is ${code}`, () => {
        pm.response.to.have.status(code);
    });
}

/**
 * Tests if the response status code matches the given status string.
 *
 * @param {string} [status='OK'] - The expected status code string.
 */
function testStatusCodeString(status = 'OK') {
    pm.test(`Status code name has string "${status}"`, () => {
        pm.response.to.have.status(status);
    });
}

/**
 * Tests if a specified header is present in the response.
 *
 * @param {string} [header='Content-Type'] - The name of the header to check for. Defaults to 'Content-Type'.
 */
function testForHeader(header = 'Content-Type') {
    pm.test(`Header "${header}" is present`, () => {
        pm.response.to.have.header(header);
    });
}
//#endregion HEADERS


/**
 * Tests if the response body matches the given text.
 *
 * @param {string} [text=''] - The text to compare with the response body.
 */
function testBodyText(text = '') {
    pm.test(`Body is correct "${text}"`, () => {
        pm.response.to.have.body(text);
    });
}

//#region JSON
/**
 * Verifies that the response matches the provided JSON schema.
 *
 * @param {Object} schema - The JSON schema to validate the response against.
 */
function verifySchema(schema) {
    pm.test("Schema is valid", function () {
        // pm.expect(pm.response.json()).to.have.jsonSchema(schema);
        pm.response.to.have.jsonSchema(schema);
    });
}

/**
 * Verifies that the response matches the provided JSON schema.
 *
 * @since 4
 *
 * @param {string} [dotPath=undefined] - The path to test for validity
 * @param {string} [separator='.'] - The notation path separator
 */
function testJSONPathExists(dotPath, separator = '.') {
    pm.test(`Check json path "${dotPath}" exists"`, () => {
        const val = dotPath.split(separator).reduce((obj, key) => obj[key], pm.response.json());
        pm.expect(val, `Expected "${dotPath}" to exist`).to.not.equal(undefined);
    });
}

/**
 * Tests the path of a JSON object matches expected value
 *
 * @since 2
 * @since 3 added separator
 * @since 4 renamed from `testJSOVValue` to `testJSOVValueEqual`
 *
 * @param {string} [dotPath=undefined] - The path the value to test in dot notation
 * @param {bool|string|int} [value=undefined] - The value to test for
 * @param {string} [separator='.'] - The notation path separator
 */
function testJSONValueEqual(dotPath, value, separator = '.') {
    pm.test(`Check json path "${dotPath}" value is "${value}"`, () => {
        const val = dotPath.split(separator).reduce((obj, key) => obj[key], pm.response.json());
        pm.expect(value).to.eql(val);
    });
}


/**
 * Tests the path of a JSON object not empty
 *
 * @since 4
 *
 * @param {string} [dotPath=undefined] - The path the value to test in dot notation
 * @param {string} [separator='.'] - The notation path separator
 */
function testJSONValueNotEmpty(dotPath, separator = '.') {
    pm.test(`Check json path "${dotPath}" value is not null`, () => {
        const value = dotPath.split(separator).reduce((obj, key) => obj[key], pm.response.json());
        pm.expect(value).to.be.a('string').and.to.have.lengthOf.at.least(1, "Value should not be empty");
    });
}
//#endregion JSON

module.exports = {
    req,
    testEnvironment,

    testResponseTimeLessThan,

    testStatusCode,
    testStatusCodeString,
    testForHeader,

    testBodyText,

    verifySchema,
    testJSONPathExists,
    testJSONValueEqual,
    testJSONValueNotEmpty
}
