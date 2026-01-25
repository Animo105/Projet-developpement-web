<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Element extends Model
{
    protected $fillable = [
        'name',
        'name_f',
        'description',
        'tag_id',
        'icon_id',
    ];

    public function tagElements() : HasMany {
        return $this->hasMany(Tag_Element::class, 'element_id');
    }

    public function tags(): BelongsToMany{
        return $this->belongsToMany(Tag::class, 'tag_elements', 'element_id', 'tag_id');
    }

    public function elements(): BelongsToMany
    {
        return $this->belongsToMany(Element::class, 'element_element', 'element_id', 'another_element_id');
    }

    public function cardElements(): HasMany
    {
        return $this->hasMany(Card_Element::class, 'element_id');
    }


    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class, 'card_elements', 'element_id', 'card_id')
            ->withPivot('type_id', 'amount');
    }

    public function icon()
    {
        return $this->belongsTo(Icon::class);
    }

    /**
     * Find elements that have exactly the given children.
     */
    public static function findParentWithExactChildren($childIds): string
    {
        $childIds = collect($childIds)->map(fn($c) => $c instanceof self ? $c->id : $c)->sort()->values();

        // Get potential parents that contain all of these children
        $parents = DB::table('element_element')
            ->select('element_id')
            ->whereIn('another_element_id', $childIds)
            ->groupBy('element_id')
            ->havingRaw('COUNT(DISTINCT another_element_id) = ?', [$childIds->count()])
            ->pluck('element_id');

        if ($parents->isEmpty()) {
            return self::whereIn('id', $childIds)->pluck('name')->implode(', ');
        }

        // Now check that each parent has *only* these children (not extra ones)
        $matching = self::whereIn('id', $parents)
            ->get()
            ->filter(function ($parent) use ($childIds) {
                $ids = $parent->elements()->pluck('id')->sort()->values();
                return $ids->count() === $childIds->count() && $ids->diff($childIds)->isEmpty();
            });

        return $matching->isNotEmpty()
            ? $matching->pluck('name')->implode(', ')
            : self::whereIn('id', $childIds)->pluck('name')->implode(', ');
    }

    /**
     * Find elements that have the expected tag
     */
    public static function findWithTag(int|array $tagIds): Collection
    {
        $tagIds = collect($tagIds)->map(fn($id) => (int)$id)->unique();
        return self::whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))->get();
    }

}
