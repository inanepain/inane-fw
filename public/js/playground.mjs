import {ActivityPicker} from './playground/ActivityPicker.mjs';
import {ActivityListSexual} from './playground/ActivityListSexual.mjs';

import readline from 'node:readline/promises';
import {stdin, stdout} from 'node:process';

const rl = readline.createInterface({
    input: stdin,
    output: stdout,
});

const setDebug = false;
// const setLogLevel = Dumper.DEBUG;
const ap = new ActivityPicker(ActivityListSexual, {
    rangeSize: 5,
    step: 10,
    // logLevel: 'DEBUG',
    debug: { pick: setDebug, lastActivity: setDebug, options: setDebug, duplicate: setDebug, numberOfPicks: setDebug, }, king: 'kong'
});

// ap.setNumberOfPicks(40);
ap.setNumberOfPicks(15, true);

let i = 0;
let games = 0, limit = 1;
while (!ap.end && games < limit) {
  await rl.question('Press Enter for an activity...');
    console.debug("\t" + (games + 1) + '.' + ++i + ':', ap.pick());
    if (ap.end) {
        games++;
        ap.pick();
    }
}

rl.close();
