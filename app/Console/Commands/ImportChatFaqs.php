<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ChatFaq;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportChatFaqs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'chat:import-faqs {path : Path to the JSON file containing FAQs}';

    /**
     * The console command description.
     */
    protected $description = 'Import FAQs from a JSON file into the chat_faqs table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path');

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        try {
            $contents = File::get($path);
            $faqs = json_decode($contents, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON file: ' . json_last_error_msg());
                return self::FAILURE;
            }

            if (! is_array($faqs)) {
                $this->error('JSON file must contain an array of FAQs');
                return self::FAILURE;
            }

            $this->info("Importing {count($faqs)} FAQs...");
            $bar = $this->output->createProgressBar(count($faqs));
            $bar->start();

            $imported = 0;
            $skipped = 0;

            foreach ($faqs as $faqData) {
                if (! isset($faqData['question']) || ! isset($faqData['answer'])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                ChatFaq::create([
                    'question' => $faqData['question'],
                    'answer' => $faqData['answer'],
                    'tags' => $faqData['tags'] ?? [],
                    'usage_count' => $faqData['usage_count'] ?? 0,
                ]);

                $imported++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("✓ Successfully imported {$imported} FAQs");

            if ($skipped > 0) {
                $this->warn("⚠ Skipped {$skipped} invalid FAQs");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error importing FAQs: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

