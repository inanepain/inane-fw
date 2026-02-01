import {WikiShowParser} from './lib/WikiShowParser.mjs';
import {Dumper as Dump} from './inane/dumper.mjs';


const wsp = new WikiShowParser();
wsp.parseWiki();

Dump.log(wsp.data);

// WikiShowParser.createParser().parseWiki().data;

// let wp = new WikiShowParser();
// wp.parseWiki().data;
// wp.parseWiki();
// wp.data;
