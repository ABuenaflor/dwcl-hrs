<?php

namespace App\Models;

use App\Enums\Level;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubric extends Model
{
    protected $fillable = ['level', 'name', 'description', 'total_points', 'is_active'];

    protected function casts(): array
    {
        return ['level' => Level::class, 'is_active' => 'boolean', 'total_points' => 'float'];
    }

    public static function activeFor(Level $level): ?self
    {
        return static::where('level', $level)->where('is_active', true)->latest('id')->first();
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(FacultyRanking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RubricItem::class)->orderBy('sort_order');
    }

    /**
     * All items arranged as a tree in one query, each node with a
     * `children` relation already populated.
     *
     * @return Collection<int, RubricItem>
     */
    public function tree(): Collection
    {
        $items = $this->items()->get();
        $byParent = $items->groupBy(fn (RubricItem $i) => $i->parent_id ?? 0);

        foreach ($items as $item) {
            $item->setRelation('children', $byParent->get($item->id, new Collection));
        }

        return $byParent->get(0, new Collection);
    }
}
