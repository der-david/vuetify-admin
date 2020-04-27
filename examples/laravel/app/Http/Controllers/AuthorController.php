<?php

namespace App\Http\Controllers;

use App\Author;
use App\Http\Requests\StoreAuthor;
use App\Http\Requests\UpdateAuthor;
use App\Http\Resources\Author as AuthorResource;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Vtec\Crud\Filters\SearchFilter;

class AuthorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Author::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return AuthorResource::collection(
            QueryBuilder::for(Author::class)
                ->allowedFields(['id', 'name'])
                ->allowedFilters([
                    AllowedFilter::custom('q', new SearchFilter(['name', 'description'])),
                    AllowedFilter::exact('id'),
                    'name',
                    AllowedFilter::callback('books', function (Builder $query, $value) {
                        $query->whereHas('books', function (Builder $query) use ($value) {
                            $query->whereIn('id', is_array($value) ? $value : [$value]);
                        });
                    }),
                ])
                ->allowedSorts(['id', 'name'])
                ->allowedIncludes(['books', 'media'])
                ->hasUser(auth()->user())
                ->exportOrPaginate()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Author  $author
     * @return AuthorResource
     */
    public function show(Author $author)
    {
        return new AuthorResource($author->load(['books', 'media', 'users']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return AuthorResource
     */
    public function store(StoreAuthor $request)
    {
        $author = Author::create($request->all());
        $author->users()->sync($request->input('user_ids'));

        return new AuthorResource($author);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Author  $author
     * @return AuthorResource
     */
    public function update(UpdateAuthor $request, Author $author)
    {
        $author->update($request->all());
        $author->users()->sync($request->input('user_ids'));

        return new AuthorResource($author);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Author $author
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Author $author)
    {
        $author->delete();

        return response()->noContent();
    }
}
