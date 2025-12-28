const mergeOptions=(target,...source)=>{var key,i;for(i=0;i<source.length;i++)
for(key in source[i])
if(!(key in target)&&source[i].hasOwnProperty(key))target[key]=source[i][key];else
try{if(target[key].constructor===Object&&source[i][key].constructor===Object)mergeOptions(target[key],source[i][key]);}catch(error){if(error.message.includes('target[key].constructor'))target[key]=source[i][key];}};class Throwable extends Error{#name='Throwable';#namespace='';#date=new Date();#detail={};constructor(message,{type,namespace,date,detail}={}){super(message);if(type)this.#name=type;if(namespace)this.#namespace=namespace;if(date)this.#date=date;if(detail)mergeOptions(this.#detail,detail);}
get name(){return this.#name;}
get namespace(){return this.#namespace;}
get date(){return this.#date;}
get timeStamp(){return this.date.getTime();}
get detail(){return this.#detail;}}
export{Throwable};