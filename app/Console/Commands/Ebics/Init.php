<?php

namespace App\Console\Commands\Ebics;

use App\Services\Ebics;
use Illuminate\Console\Command;
use Throwable;

class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ebics:init --force';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise EBICS mutual agreement (key generation, sending INI/HIA, bank letter)';

    /**
     * Execute the console command.
     *
     * @param  Ebics  $ebics
     * @return mixed
     */
    public function handle(Ebics $ebics)
    {
        $this->info('Initializing EBICS');

        if (file_exists(storage_path('app/ebics/keyring.json'))) {
            $this->warn('Keyring already exists, skipping');
        } else {
            try {
                $this->getOutput()->write('1/3 Sending client keys (INI / HIA): ');
                $ebics->sendClientKeys();
                $this->info('OK');

                $this->getOutput()->write('2/3 Generating bank letter: ');
                $ebics->generateInitLetter();
                $this->info('OK');

                $this->getOutput()->write('3/3 Fetching and verifying bank keys (HPB): ');
                $ebics->fetchBankKeys();
                $this->info('OK');
            } catch (Throwable $e) {
                $this->error('EBICS init failed: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
