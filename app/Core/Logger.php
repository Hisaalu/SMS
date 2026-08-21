<?php

namespace NexaT\Core;

class Logger
{
    private $logPath;
    private $logLevel;
    private $levels = [
        'debug' => 100,
        'info' => 200,
        'notice' => 250,
        'warning' => 300,
        'error' => 400,
        'critical' => 500,
        'alert' => 550,
        'emergency' => 600
    ];
    
    public function __construct(string $logPath = null, string $level = null)
    {
        $this->logPath = $logPath ?? STORAGE_PATH . '/logs/app.log';
        $this->logLevel = $level ?? getenv('LOG_LEVEL') ?: 'info';
        
        // Ensure log directory exists
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }
    
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }
    
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }
    
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }
    
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }
    
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->levels[$level] < $this->levels[$this->logLevel]) {
            return;
        }
        
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $contextString
        );
        
        file_put_contents($this->logPath, $logLine, FILE_APPEND | LOCK_EX);
    }
}