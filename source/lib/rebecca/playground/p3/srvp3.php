<?php

// Initialize and start the server
$wsServer = new WebSocketServer('0.0.0.0', 9501);

// Register commands with different rank requirements
$wsServer->registerCommand(new PingCommand());        // Rank 0 - Everyone
$wsServer->registerCommand(new EchoCommand());        // Rank 0 - Everyone
$wsServer->registerCommand(new InfoCommand());        // Rank 0 - Everyone
$wsServer->registerCommand(new BroadcastCommand());   // Rank 5 - Moderators
$wsServer->registerCommand(new SetRankCommand());     // Rank 10 - Admins

// Start the server
$wsServer->start();
