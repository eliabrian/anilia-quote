<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuoteRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * 
     * @return object
     */
    public function index(Request $request): object
    {
        $quotes = Quote::paginate(10);

        if ($request->hasAny(['anime', 'character'])) {
            $quotes = $this->getQuoteByFilter($request);
        }

        return QuoteResource::collection($quotes);
    }

    /**
     * Display a specified resource in random order.
     * 
     * @param Request $request
     * 
     * @return object
     */
    public function random(Request $request): object
    {
        $limit = (int) $request->query('limit', 1);
        return QuoteResource::collection(
            Quote::all()->random($limit)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuoteRequest $request)
    {
        $anime = $this->searchOrNew('anime', $request);
        $character = $this->searchOrNew('character', $request);

        $quote = Quote::create([
            'anime_id' => $anime->id,
            'character_id' => $character->id,
            'quote' => $request->quote
        ]);

        return new QuoteResource($quote);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get the quote by the query parameter.
     * 
     * @param Request $request Query parameter
     * 
     * @return object
     */
    private function getQuoteByFilter(Request $request): object
    {
        if ($request->has('anime')) {
            $column = 'anime_id';
            $model = Anime::search($request->query('anime'))
                ->get(); 
        } elseif ($request->has('character')) {
            $column = 'character_id';
            $model = Character::search($request->query('character'))
                ->get(); 
        }

        $ids = $model->pluck('id');
        $quotes = Quote::whereIn($column, $ids)
            ->paginate(10)
            ->withQueryString();

        return $quotes;
    }

    private function searchOrNew(string $model, Request $request)
    {
        $modelName = "App\\Models\\" . ucfirst(strtolower($model));
        $columnName = $model . '_name';
        $model = new $modelName;
        $data = $model::search($request->$columnName)->first();

        if (!$data) {
            $data = $model::create([
                'name' => $request->$columnName
            ]);
        }

        return $data;
    }
}
