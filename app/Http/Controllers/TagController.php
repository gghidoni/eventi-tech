<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function findTags(Request $request): JsonResponse
    {
        $selected = trim((string) $request->query('selected', ''));
        $search = trim((string) $request->query('search', ''));
        $page = max((int) $request->query('page', 1), 1);
        $perPage = max(min((int) $request->query('per_page', 20), 50), 1);

        // Supporto lookup valori già selezionati (richiesta fatta dal componente async-select).
        if ($selected !== '') {
            $selectedIds = collect(explode(',', $selected))
                ->map(fn (string $id): int => (int) $id)
                ->filter(fn (int $id): bool => $id > 0)
                ->unique()
                ->values();

            if ($selectedIds->isEmpty()) {
                return response()->json(['data' => []]);
            }

            $data = Tag::query()
                ->whereIn('id', $selectedIds->all())
                ->get(['id', 'name', 'icon', 'badge_color', 'label_color'])
                ->map(fn (Tag $tag): array => $this->toOption($tag))
                ->values()
                ->all();

            return response()->json(['data' => $data]);
        }

        if ($search === '') {
            return response()->json(['data' => [], 'has_more' => false]);
        }

        $paginator = Tag::query()
            ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
            ->orderBy('name')
            ->paginate($perPage, ['id', 'name', 'icon', 'badge_color', 'label_color'], 'page', $page);

        $data = collect($paginator->items())
            ->map(fn (Tag $tag): array => $this->toOption($tag))
            ->values()
            ->all();

        return response()->json([
            'data'     => $data,
            'has_more' => $paginator->hasMorePages(),
        ]);
    }

    /**
     * @return array{
     *   value:string,
     *   label:string,
     *   icon:string,
     *   badge_color:string,
     *   label_color:string
     * }
     */
    private function toOption(Tag $tag): array
    {
        return [
            'value'       => (string) $tag->id,
            'label'       => $tag->name,
            'icon'        => (string) $tag->icon,
            'badge_color' => (string) $tag->badge_color,
            'label_color' => (string) $tag->label_color,
        ];
    }
}
