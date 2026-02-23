// re2js $INPUT -o $OUTPUT

function lex(yyinput) {
    let yycursor = 0;
    /*!re2c
        re2c:yyfill:enable = 0;

        [1-9][0-9]* { return true; }
        *           { return false; }
    */
}

function verifyLicense(yyinput) {
    // ([l][i][c])[0-9]{3,}
    let yycursor = 0;
    /*!re2c
        re2c:yyfill:enable = 0;

        "lic"[0-9]{3,} { return true; }
        *           { return false; }
    */
}

if (!lex("1234\0")) {
    throw "error!"
}

console.log(lex('0'));
console.log(lex('1'));
console.log(lex('120'));

console.log('verifyLicense');

console.log(verifyLicense('bob9383'));
console.log(verifyLicense('lictv'));
console.log(verifyLicense('lic'));
// console.log(verifyLicense('licc'));
// console.log(verifyLicense('licckil'));
// console.log(verifyLicense('lic1'));
console.log(verifyLicense('lic12'));
console.log(verifyLicense('lic123'));
console.log(verifyLicense('lic1234'));