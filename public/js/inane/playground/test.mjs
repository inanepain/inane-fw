/**
 * A simple example of how to use the Dumper class levels and control it's inheritance in children.
 */
import { Dumper } from '../dumper.mjs';

// Let's create out main Dumper
const logger = Dumper.get('App', {
    level: 'debug'
});

Dumper.log('Dumper version', Dumper.VERSION);

logger.log('My Level:', logger.level, 'Our first level dumper');

logger.log('');
logger.log('Create a few children with various levels');

// And some children and adjust their level by chaining commands.
logger.get('AreaA').log('Level', logger.get('AreaA').level);
logger.get('AreaB', {
    level: 'info'
}).log('Level', logger.get('AreaB').level);
logger.get('AreaC').setLevel('warn').log('Level', logger.get('AreaC').level);

logger.log('');
logger.log('Set `AreaD` to `NOT` inherit the parent level on change by setting `optionBubble` to `false`');
logger.log('Then set parent level to `error`');

// But this one we tell not to listen for level changes
// logger.get('AreaD').optionBubble = false;
logger.get('AreaD').optionBubbleFromParent = false;

// Lets set the parent level
logger.setLevel('error');

logger.log('');
logger.log('All childeren have taken the new level except `AreaD`');

// All the childeren have taken the new level
logger.get('AreaA').log('Level', logger.get('AreaA').level);
logger.get('AreaB').log('Level', logger.get('AreaB').level);
logger.get('AreaC').log('Level', logger.get('AreaC').level);

logger.log('which is still set to `debug`');
// Almost all
logger.get('AreaD').log('Level', logger.get('AreaD').level);

logger.log('');

logger.log('My Children', Object.keys(logger.children()));
