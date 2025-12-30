<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckFirebaseConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Firebase configuration and credentials file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking Firebase Configuration...');
        $this->newLine();

        // Check project ID
        $projectId = config('fcm.project_id');
        $this->info("📝 Project ID: {$projectId}");

        // Check credentials path
        $credentialsPath = config('fcm.credentials_path');
        $this->info("📁 Credentials Path: {$credentialsPath}");
        $this->newLine();

        // Check if file exists
        if (!file_exists($credentialsPath)) {
            $this->error('❌ Firebase credentials file NOT FOUND!');
            $this->warn("Expected location: {$credentialsPath}");
            $this->newLine();
            $this->info('💡 Solution:');
            $this->line('1. Download your firebase.json from Firebase Console');
            $this->line('2. Upload it to: storage/app/firebase.json');
            $this->line('3. Make sure the file has proper permissions (644)');
            return self::FAILURE;
        }

        $this->info('✅ Firebase credentials file exists');

        // Check file permissions
        $permissions = substr(sprintf('%o', fileperms($credentialsPath)), -4);
        $this->info("🔐 File permissions: {$permissions}");

        if (!is_readable($credentialsPath)) {
            $this->error('❌ File is NOT readable!');
            $this->warn('Run: chmod 644 storage/app/firebase.json');
            return self::FAILURE;
        }

        $this->info('✅ File is readable');

        // Read and validate JSON
        $jsonContent = file_get_contents($credentialsPath);
        $this->info('📄 File size: ' . strlen($jsonContent) . ' bytes');

        $jsonData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('❌ Invalid JSON format!');
            $this->error('Error: ' . json_last_error_msg());
            $this->newLine();
            $this->info('💡 Solution:');
            $this->line('1. Download a fresh firebase.json from Firebase Console');
            $this->line('2. Make sure the file is not corrupted during upload');
            $this->line('3. Validate the JSON format using a JSON validator');
            return self::FAILURE;
        }

        $this->info('✅ JSON format is valid');
        $this->newLine();

        // Validate required fields
        $requiredFields = [
            'type',
            'project_id',
            'private_key_id',
            'private_key',
            'client_email',
            'client_id',
            'auth_uri',
            'token_uri',
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($jsonData[$field]) || empty($jsonData[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $this->error('❌ Missing required fields in firebase.json:');
            foreach ($missingFields as $field) {
                $this->line("   - {$field}");
            }
            return self::FAILURE;
        }

        $this->info('✅ All required fields present');
        $this->newLine();

        // Display configuration details
        $this->info('📋 Firebase Configuration:');
        $this->line("   Type: {$jsonData['type']}");
        $this->line("   Project ID: {$jsonData['project_id']}");
        $this->line("   Client Email: {$jsonData['client_email']}");
        $this->line("   Client ID: {$jsonData['client_id']}");
        $this->newLine();

        // Check if project IDs match
        if ($projectId !== $jsonData['project_id']) {
            $this->warn('⚠️  Project ID mismatch!');
            $this->line("   Config: {$projectId}");
            $this->line("   JSON: {$jsonData['project_id']}");
            $this->newLine();
        }

        $this->info('✅ Firebase configuration is valid and ready to use!');
        $this->newLine();

        return self::SUCCESS;
    }
}
