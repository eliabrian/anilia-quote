<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Quote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SeedQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:quotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Feed the quotes table.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /**
         * Fetch the quotes from Animechan
         */
        try {
            $response = Http::get($this->buildUrl('quotes'))->json();
        } catch (HttpException $e) {
            $this->error('ERR: Cannot fetch the response from API. Check logs!');
            Log::error('ERR: Cannot fetch the response from API.', [
                'message' => $e->getMessage,
            ]);
        }

        foreach ($response as $data) {
            $anime = Anime::create([
                'name' => $data['anime'],
            ]);

            $character = Character::create([
                'name' => $data['character'],
            ]);

            $quote = Quote::upsert([
                'animechan_id' => $data['id'],
                'anime_id' => $anime->id,
                'character_id' => $character->id,
                'quote' => $data['quote'],
            ], uniqueBy: ['aninmechan_id'], update: ['quote']);

            $this->info('Quote ' . $data['id'] . ' successfully seeded!');
        }

    }

    /**
     * Build the Animechan API URL.
     * 
     * @param string $path Path of the request.
     * 
     * @return string
     */
    private function buildUrl(string $path): string
    {
        $url = config('animechan.protocol') . '://' . config('animechan.domain');

        return $url . '/' . config('animechan.apipath') . '/' . $path;
    }
}
