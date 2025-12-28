// example.mjs
import {Rebecca} from '../../../../public/js/ws/rebecca.mjs';
import {Message} from '../../../../public/js/ws/Message.mjs';

// Create a new client
const client = new Rebecca('ws://localhost:9501', {
    autoReconnect: true,
    reconnectInterval: 3000,
    maxReconnectAttempts: 5,
    debug: true
});

// Register event handlers
client.on('open', () => {
    console.log('✅ Connected to server');
});

client.on('close', () => {
    console.log('❌ Disconnected from server');
});

client.on('error', (error) => {
    console.error('⚠️ Error:', error);
});

client.on('reconnecting', ({attempt, maxAttempts}) => {
    console.log(`🔄 Reconnecting... (${attempt}/${maxAttempts})`);
});

client.on('reconnectFailed', () => {
    console.log('💔 Failed to reconnect after maximum attempts');
});

// Handle incoming messages
client.on('message', (message) => {
    const command = message.getCommand();
    console.log(`📨 Received: ${command}`, message.getArgs());

    switch (command) {
        case 'welcome':
            console.log(`👋 Welcome! FD: ${client.getFd()}, Rank: ${client.getRank()}`);
            // console.log('Available commands:', client.getAvailableCommands());

            // Send a ping command
            setTimeout(() => {
                console.log('📤 Sending ping...');
                client.sendCommand('ping');
            }, 1000);
            break;

        case 'pong':
            console.log(`🏓 Pong received at ${message.getArg('timestamp')}`);

            // Send an echo command
            setTimeout(() => {
                console.log('📤 Sending echo...');
                client.sendCommand('echo', {message: 'Hello, Server!'});
            }, 1000);
            break;

        case 'echo':
            console.log(`🔊 Echo: ${message.getArg('message')}`);

            // Get info
            setTimeout(() => {
                console.log('📤 Getting info...');
                client.sendCommand('info');
            }, 1000);
            break;

        case 'info':
            console.log('ℹ️ Rebecca Info:', {
                fd: message.getArg('fd'),
                rank: message.getArg('rank'),
                uptime: message.getArg('uptime')
            });

            // Try to broadcast (will fail if rank < 5)
            // setTimeout(() => {
            //     console.log('📤 Attempting broadcast...');
            //     if (client.canExecuteCommand('broadcast')) {
            //         client.sendCommand('broadcast', {
            //             message: 'Hello everyone!'
            //         });
            //     } else {
            //         console.log('⚠️ Cannot execute broadcast (insufficient rank)');
            //         // Try anyway to see error message
            //         client.sendCommand('broadcast', {
            //             message: 'This should fail'
            //         });
            //     }
            // }, 1000);
            break;

        case 'broadcast':
            console.log(`📢 Broadcast from #${message.getArg('from')}: ${message.getArg('message')}`);
            break;

        case 'rank_updated':
            console.log(`⭐ Rank updated: ${message.getArg('old_rank')} → ${message.getArg('new_rank')}`);
            break;

        case 'error':
            console.error('❌ Server error:', message.getArg('error'));
            break;

        default:
            console.log(`Unknown command: ${command}`);
    }
});

// Connect to the server
console.log('🔌 Connecting to server...');
client.connect()
    .then(() => {
        console.log('✅ Connection established');
    })
    .catch((error) => {
        console.error('❌ Connection failed:', error);
    });

// Example: Using Message class directly
setTimeout(() => {
    const customMessage = new Message('echo', {message: 'Using Message class'});
    console.log('📤 Sending custom message:', customMessage.toString());
    client.send(customMessage);
}, 5000);

// Handle process termination
process.on('SIGINT', () => {
    console.log('\n👋 Disconnecting...');
    client.disconnect();
    process.exit(0);
});

// Keep the script running
console.log('Press Ctrl+C to disconnect and exit');