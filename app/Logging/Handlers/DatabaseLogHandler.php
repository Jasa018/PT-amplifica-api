<?php

namespace App\Logging\Handlers;

use App\Models\Log as LogModel;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    public function __construct($level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write(LogRecord $record): void
    {
        $context = $record->context;
        $storeId = null;

        // Check if a store_id is passed in the context
        if (isset($context['store_id'])) {
            $storeId = $context['store_id'];
            unset($context['store_id']); // Remove it from context to avoid duplication
        }

        LogModel::create([
            'store_id' => $storeId,
            'level' => strtolower($record->level->getName()),
            'message' => $record->message,
            'context' => !empty($context) ? json_encode($context) : null,
        ]);
    }
}
