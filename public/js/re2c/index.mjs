import { RE2JS } from "./re2c.esm.mjs";


const verifyLicense = RE2JS.compile('lic[0-9]{3,}');

console.log(verifyLicense.matches('bob9383'));
console.log(verifyLicense.matches('lictv'));
console.log(verifyLicense.matches('lic'));
console.log(verifyLicense.matches('licc'));
console.log(verifyLicense.matches('licckil'));
console.log(verifyLicense.matches('lic1'));
console.log(verifyLicense.matches('lic12'));
console.log(verifyLicense.matches('lic123'));
console.log(verifyLicense.matches('lic1234'));

console.debug(verifyLicense);