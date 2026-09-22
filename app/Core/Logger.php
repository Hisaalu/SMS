<?php
// File: /app/Core/Logger.php

namespace NexaT\Core;

class Logger
{
    private const LEVELS = [
        'debug'     => 100,
        'info'      => 200,
        'notice'    => 250,
        'warning'   => 300,
        'error'     => 400,
        'critical'  => 500,
        'alert'     => 550,
        'emergency' => 600,
    ];

    private string $logPath;
    private int $minLevel;

    public function __construct(?string $logPath = null, ?string $level = null)
    {
        $this->logPath = $logPath ?? STORAGE_PATH . '/logs/app.log';

        $levelName = strtolower($level ?? (getenv('LOG_LEVEL') ?: 'info'));
        $this->minLevel = self::LEVELS[$levelName] ?? self::LEVELS['info'];

        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function emergency(string $message, array $context = []): void { $this->log('emergency', $message, $context); }
    public function alert(string $message, array $context = []): void     { $this->log('alert', $message, $context); }
    public function critical(string $message, array $context = []): void  { $this->log('critical', $message, $context); }
    public function error(string $message, array $context = []): void     { $this->log('error', $message, $context); }
    public function warning(string $message, array $context = []): void   { $this->log('warning', $message, $context); }
    public function notice(string $message, array $context = []): void    { $this->log('notice', $message, $context); }
    public function info(string $message, array $context = []): void      { $this->log('info', $message, $context); }
    public function debug(string $message, array $context = []): void     { $this->log('debug', $message, $context); }

    private function log(string $level, string $message, array $context): void
    {
        if ((self::LEVELS[$level] ?? 0) < $this->minLevel) {
            return;
        }

        $contextString = $context === []
            ? ''
            : ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $contextString
        );

        file_put_contents($this->logPath, $line, FILE_APPEND | LOCK_EX);
    }
}