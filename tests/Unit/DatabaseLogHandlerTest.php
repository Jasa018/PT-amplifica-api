<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Logging\Handlers\DatabaseLogHandler;
use App\Models\Log as LogModel;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Level;

class DatabaseLogHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_writes_log_records_to_the_database()
    {
        // Create a dummy store for the foreign key constraint
        $store = \App\Models\Store::factory()->create();

        $handler = new DatabaseLogHandler();

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'This is a test error message.',
            context: ['user_id' => 1, 'store_id' => $store->id],
            extra: [],
            formatted: 'formatted message',
        );

        $handler->handle($record);

        $this->assertDatabaseHas('logs', [
            'level' => 'error',
            'message' => 'This is a test error message.',
            'store_id' => $store->id,
        ]);

        $logEntry = LogModel::first();
        $this->assertNotNull($logEntry);
        $this->assertJson((string) $logEntry->context);
        $contextData = json_decode($logEntry->context, true);
        $this->assertArrayHasKey('user_id', $contextData);
        $this->assertEquals(1, $contextData['user_id']);
        $this->assertArrayNotHasKey('store_id', $contextData); // store_id should be moved out of context
    }

    /** @test */
    public function it_handles_log_records_without_store_id_in_context()
    {
        $handler = new DatabaseLogHandler();

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Info message without store ID.',
            context: ['data' => 'some_value'],
            extra: [],
            formatted: 'formatted message',
        );

        $handler->handle($record);

        $this->assertDatabaseHas('logs', [
            'level' => 'info',
            'message' => 'Info message without store ID.',
            'store_id' => null,
        ]);

        $logEntry = LogModel::first();
        $this->assertNotNull($logEntry);
        $this->assertJson((string) $logEntry->context);
        $contextData = json_decode($logEntry->context, true);
        $this->assertArrayHasKey('data', $contextData);
        $this->assertEquals('some_value', $contextData['data']);
    }

    /** @test */
    public function it_handles_empty_context()
    {
        $handler = new DatabaseLogHandler();

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Warning,
            message: 'Warning message with empty context.',
            context: [],
            extra: [],
            formatted: 'formatted message',
        );

        $handler->handle($record);

        $this->assertDatabaseHas('logs', [
            'level' => 'warning',
            'message' => 'Warning message with empty context.',
            'store_id' => null,
            'context' => null,
        ]);
    }
}
