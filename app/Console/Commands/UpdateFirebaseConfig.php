<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateFirebaseConfig extends Command
{
    protected $signature = 'firebase:update {--json= : Firebase JSON content as string}';

    protected $description = 'Update Firebase credentials JSON file';

    public function handle(): int
    {
        $this->info('🔄 Updating Firebase Configuration...');
        $this->newLine();

        $jsonContent = $this->option('json');

        if (!$jsonContent) {
            $this->error('❌ No JSON content provided!');
            $this->newLine();
            $this->info('💡 Usage:');
            $this->line('php artisan firebase:update --json=\'{"type":"service_account",...}\'');
            $this->newLine();
            $this->warn('⚠️  Alternatively, you can paste the JSON content when prompted:');

            $jsonContent = $this->ask('Paste your Firebase JSON content here');

            if (!$jsonContent) {
                $this->error('❌ No content provided. Aborting.');
                return self::FAILURE;
            }
        }

        // Clean up the JSON content
        $jsonContent = $this->cleanJsonContent($jsonContent);

        // Validate JSON
        $jsonData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('❌ Invalid JSON format!');
            $this->error('Error: ' . json_last_error_msg());
            return self::FAILURE;
        }

        // Validate required fields
        $requiredFields = [
            'type', 'project_id', 'private_key_id', 'private_key',
            'client_email', 'client_id', 'auth_uri', 'token_uri',
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($jsonData[$field]) || empty($jsonData[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $this->error('❌ Missing required fields:');
            foreach ($missingFields as $field) {
                $this->line("   - {$field}");
            }
            return self::FAILURE;
        }

        $this->info('✅ JSON is valid');
        $this->newLine();

        // Display what will be saved
        $this->info('📋 Firebase Configuration to save:');
        $this->line("   Project ID: {$jsonData['project_id']}");
        $this->line("   Client Email: {$jsonData['client_email']}");
        $this->newLine();

        // Confirm before saving
        if (!$this->confirm('Do you want to save this configuration?', true)) {
            $this->warn('❌ Operation cancelled.');
            return self::FAILURE;
        }

        // Create storage/app directory if it doesn't exist
        $storagePath = storage_path('app');
        if (!File::isDirectory($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        // Pretty print JSON
        $prettyJson = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Save the file
        $credentialsPath = config('fcm.credentials_path');

        try {
            File::put($credentialsPath, $prettyJson);

            // Set proper permissions
            chmod($credentialsPath, 0644);

            $this->info('✅ Firebase configuration saved successfully!');
            $this->info("📁 Location: {$credentialsPath}");
            $this->newLine();

            // Run the check command
            $this->info('🔍 Verifying configuration...');
            $this->call('firebase:check');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to save configuration!');
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function cleanJsonContent(string $content): string
    {
        // Remove BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Remove control characters except newlines, carriage returns, and tabs
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);

        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Trim whitespace
        $content = trim($content);

        return $content;
    }
}
