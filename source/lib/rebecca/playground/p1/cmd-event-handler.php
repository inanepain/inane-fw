<?php

declare(strict_types=1);

interface CommandInterface {
    public function getName(): string;

    public function execute(): void;

    public function validate(): bool;

    public function setHandler(CommandHandler $handler): void;

    public function setArguments(array $arguments): void;

    public function getArguments(): array;
}

interface CommandExecutionEventInterface {
    public function getCommandName(): string;

    public function getClientId(): string;

    public function getArguments(): array;
}

class CommandExecutionEvent implements CommandExecutionEventInterface {
    private string $commandName;
    private string $clientId;
    private array $arguments;

    public function __construct(string $commandName, string $clientId, array $arguments = []) {
        $this->commandName = $commandName;
        $this->clientId = $clientId;
        $this->arguments = $arguments;
    }

    public function getCommandName(): string {
        return $this->commandName;
    }

    public function getClientId(): string {
        return $this->clientId;
    }

    public function getArguments(): array {
        return $this->arguments;
    }
}

class CommandHandler {
    private array $commands = [];
    private string $clientId;
    private array $eventListeners = [];

    public function addCommand(CommandInterface $command): void {
        $command->setHandler($this);
        $this->commands[$command->getName()] = $command;
    }

    public function executeCommand(string $commandName, array $arguments = []): bool {
        if (!isset($this->commands[$commandName])) {
            return false;
        }

        $command = $this->commands[$commandName];
        $command->setArguments($arguments);

        if (!$command->validate()) {
            return false;
        }

        $command->execute();
        $this->dispatchEvent(new CommandExecutionEvent($commandName, $this->clientId, $arguments));

        return true;
    }

    public function setClientId(string $clientId): void {
        $this->clientId = $clientId;
    }

    public function addEventListener(callable $listener): void {
        $this->eventListeners[] = $listener;
    }

    private function dispatchEvent(CommandExecutionEventInterface $event): void {
        foreach ($this->eventListeners as $listener) {
            $listener($event);
        }
    }

    public function hasCommand(string $commandName): bool {
        return isset($this->commands[$commandName]);
    }

    public function getCommands(): array {
        return $this->commands;
    }
}
