<?php

namespace App\Console\Commands;

use App\Models\MarketplaceSetting;
use Illuminate\Console\Command;

class AddDatafastApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datafast:add-api-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add DataFast API key to marketplace settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = 'df_6080d6bb349a8f3c43150ae39c0ab36f66cfaeb7326e29d2';
        
        try {
            MarketplaceSetting::setValue('datafast_api_key', $apiKey);
            $this->info('DataFast API key has been successfully added to the database.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error adding DataFast API key: ' . $e->getMessage());
            return 1;
        }
    }
}
