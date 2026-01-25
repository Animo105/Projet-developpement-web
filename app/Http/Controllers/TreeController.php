<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Element;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class TreeController extends Controller
{
    // Constants for magic numbers
    private const TAG_MANA = 3;
    private const TAG_CLASS = 4;
    private const TYPE_ESSENCE = 3;
    private const DEFAULT_CLASS_ID = -1;
    private const DEFAULT_MANA_ID = 1;

    // Mana color mapping
    private const MANA_COLORS = [
        1 => '#30B4FF',
        2 => '#48B54A',
        3 => '#FF4D4D',
        4 => '#86949f',
        5 => '#FFFFFF',
    ];
    private const DEFAULT_COLOR = '#F4F89C';

    /**
     * Display the skill tree for a given class, mana, and character.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, int $classId = self::DEFAULT_CLASS_ID, int $manaId = self::DEFAULT_MANA_ID)
    {
        $tree = $this->buildTreeData($request, $classId, $manaId);

        if (!is_array($tree)) {
            return $tree;
        }

        $manas = $this->getElementsWithTag(self::TAG_MANA);
        $classes = $this->getElementsWithTag(self::TAG_CLASS);

        $defaultClass = collect([
            (object)[
                'id' => self::DEFAULT_CLASS_ID,
                'name' => 'Général',
                'icon' => (object)['path' => 'game-icons:crystal-bars'],
            ],
        ]);

        $classes = $defaultClass->merge($classes);

        return inertia('SkillTree/Show', [
            'tree' => $tree,
            'manas' => $this->mapElementsForResponse($manas),
            'classes' => $this->mapElementsForResponse($classes),
            'selectedManaId' => $manaId,
            'selectedClassId' => $classId,
        ]);
    }

    /**
     * Unlock a node for the authenticated character.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $nodeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function nodeUnlock(Request $request, int $classId, int $manaId, int $nodeId)
    {
        $character = Auth::guard('character')->user();

        if (!$character || !$character->skill_point) {
            return back()->with('error', 'Vous n\'avez pas assez de points de compétence.');
        }

        $treeData = $this->buildTreeData($request, $classId, $manaId);
        $targetNode = collect($treeData['nodes'])->firstWhere('nodeId', $nodeId);

        if (!$targetNode || ($targetNode['state'] ?? null) !== 'available') {
            return back()->with('error', 'Ce nœud n\'est pas disponible pour le déverrouillage.');
        }

        $character->decrement('skill_point');

        $cardIds = collect($targetNode['cards'])
            ->reject(fn($card) => $card['acquired'])
            ->pluck('cardId')
            ->toArray();

        if (!empty($cardIds)) {
            $character->cards()->syncWithoutDetaching($cardIds);
        }

        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Admin: Unlock a node immediately (remove lock).
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $nodeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminUnlockNode(Request $request, int $classId, int $manaId, int $nodeId)
    {
        $this->ensureAdmin();

        $node = Node::find($nodeId);

        if (!$node) {
            return back()->with('error', 'Noeud introuvable.');
        }

        $node->locked_by_id = null;
        $node->save();

        return back();
    }

     /**
     * Admin: Lock a node by another node.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $nodeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminLockNode(Request $request, int $classId, int $manaId, int $nodeId)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'locking_node_id' => 'required|integer|exists:nodes,id',
        ]);

        $node = Node::find($nodeId);
        $lockingNode = Node::find($validated['locking_node_id']);

        if (!$node || !$lockingNode) {
            return back()->with('error', 'Noeud introuvable.');
        }

        if ($node->id === $lockingNode->id) {
            return back()->with('error', 'Un nœud ne peut pas se verrouiller lui-même.');
        }

        $node->locked_by_id = $lockingNode->id;
        $node->save();

        return back();
    }

    /**
     * Admin: Delete a node.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $nodeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminDeleteNode(Request $request, int $classId, int $manaId, int $nodeId)
    {
        $this->ensureAdmin();

        $node = Node::findOrFail($nodeId);

        if (is_null($node->parent_id)) {
            return back()->with('error', 'Impossible de supprimer un nœud racine.');
        }

        $node->delete();

        return back()->with('success', 'Nœud et ses enfants supprimés avec succès.');
    }

    /**
     * Admin: Remove a card from a node.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $nodeId
     * @param int $cardId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminRemoveCard(Request $request, int $classId, int $manaId, int $nodeId, int $cardId)
    {
        $this->ensureAdmin();

        $card = Card::findOrFail($cardId);

        if ($card->node_id !== $nodeId) {
            return back()->with('error', 'Cette carte n\'appartient pas à ce nœud.');
        }

        $card->node_id = null;
        $card->save();

        return back()->with('success', 'Carte retirée avec succès.');
    }

    /**
     * Admin: Add a new node at a relative position from a parent node.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $parentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminAddNode(Request $request, int $classId, int $manaId, int $parentId)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'dx' => 'required|integer',
            'dy' => 'required|integer',
        ]);

        $parentNode = Node::find($parentId);
        if (!$parentNode) {
            return back()->with('error', 'Nœud parent introuvable.');
        }

        $newX = $parentNode->x + $validated['dx'];
        $newY = $parentNode->y + $validated['dy'];

        $existingNode = Node::where('element_id', $parentNode->element_id)
            ->where('x', $newX)
            ->where('y', $newY)
            ->first();

        if ($existingNode) {
            return back()->with('error', 'Un nœud existe déjà à cette position.');
        }

        Node::create([
            'x' => $newX,
            'y' => $newY,
            'parent_id' => $parentNode->id,
            'element_id' => $parentNode->element_id,
        ]);

        return back()->with('success', 'Nœud créé avec succès.');
    }

    /**
     * Admin: Attach a card to a node.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $nodeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminAttachCard(Request $request, int $cardId, int $nodeId)
    {
        $this->ensureAdmin();

        $node = Node::find($nodeId);
        $card = Card::find($cardId);

        if (!$node || !$card) {
            return back()->with('error', 'Nœud ou carte introuvable.');
        }

        $card->node_id = $node->id;
        $card->save();

        $class = $node->element ?? -1;
        $mana = $card->cardManas()[0] ?? -1;

        return redirect()->route('admin.skilltree.show', [
            'classId' => $class,
            'manaId'  => $mana,
        ])->with('success', 'Carte attachée avec succès !');
    }

    /**
     * Admin: Get available cards for attachment
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminGetAvailableCards(Request $request, int $classId, int $manaId)
    {
        $this->ensureAdmin();

        $cards = Card::whereNull('node_id')
            ->where(fn($query) => $this->filterByMana($query, $manaId))
            ->select('id', 'title', 'image_path')
            ->get()
            ->map(fn($card) => [
                'id' => $card->id,
                'title' => $card->title,
                'image_path' => $card->image_path,
            ]);

        return response()->json(['cards' => $cards]);
    }

    /**
     * Admin: Get all nodes in the current tree for lock selection.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @param int $currentNodeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminGetLockableNodes(Request $request, int $classId, int $manaId, int $currentNodeId)
    {
        $this->ensureAdmin();

        $classId = $classId === self::DEFAULT_CLASS_ID ? null : $classId;

        $nodes = Node::when(
            $classId,
            fn($query) => $query->where('element_id', $classId),
            fn($query) => $query->whereNull('element_id')
        )
            ->where('id', '!=', $currentNodeId)
            ->select('id', 'x', 'y', 'parent_id')
            ->get()
            ->map(fn($node) => [
                'id' => $node->id,
                'label' => "Nœud ({$node->x}, {$node->y})",
                'isRoot' => is_null($node->parent_id),
            ]);

        return response()->json(['nodes' => $nodes]);
    }

    /*
    |--------------------------------------------------------------------------
    | Private Methods - Core Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Build the complete tree data structure.
     *
     * @param Request $request
     * @param int $classId
     * @param int $manaId
     * @return array|\Illuminate\Http\RedirectResponse
     */
    private function buildTreeData(Request $request, int $classId, int $manaId)
    {
        $classId = $classId === self::DEFAULT_CLASS_ID ? null : $classId;

        $character = Auth::guard('character')->user();
        $isAdmin = Auth::guard('web')->user()?->isAdmin() ?? false;

        if (!$isAdmin && !$character) {
            return back()->with('error', 'No character linked');
        }

        // Fetch nodes and cards
        $nodes = $this->fetchNodes($classId);
        $cards = $this->fetchCards($nodes, $manaId);

        // Calculate canvas bounds
        $canvas = $this->calculateCanvasBounds($nodes);

        // Get character ownership data
        [$ownedCardIds, $ownedManaIds] = $this->getCharacterOwnership($character, $isAdmin);

        // Pre-calculate mana IDs per node
        $nodeManaIds = $this->calculateNodeManaIds($cards, $ownedManaIds);

        // Determine node states
        $nodeStates = $this->calculateNodeStates(
            $nodes,
            $cards,
            $ownedCardIds,
            $ownedManaIds,
            $nodeManaIds,
            $isAdmin
        );

        // Build frontend-ready node data
        $nodesData = $this->buildNodesData($nodes, $cards, $manaId, $ownedCardIds, $nodeStates);

        return [
            'title' => Element::findParentWithExactChildren([$classId, $manaId]),
            'canvas' => $canvas,
            'nodes' => $nodesData,
            'skill_point' => $character?->skill_point ?? 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Private Methods - Data Fetching
    |--------------------------------------------------------------------------
    */

    /**
     * Fetch elements with a specific tag.
     *
     * @param int $tagId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getElementsWithTag(int $tagId): Collection
    {
        return Element::with('icon')
            ->whereHas('tags', fn($query) => $query->where('tags.id', $tagId))
            ->get(['id', 'name', 'icon_id']);
    }

    /**
     * Fetch nodes filtered by class.
     *
     * @param int|null $classId
     * @return \Illuminate\Support\Collection
     */
    private function fetchNodes(?int $classId): Collection
    {
        return Node::when(
            $classId,
            fn($query) => $query->where('element_id', $classId),
            fn($query) => $query->whereNull('element_id')
        )
            ->get()
            ->keyBy('id');
    }

    /**
     * Fetch cards linked to nodes with mana filtering.
     *
     * @param Collection $nodes
     * @param int $manaId
     * @return \Illuminate\Support\Collection
     */
    private function fetchCards(Collection $nodes, int $manaId): Collection
    {
        return Card::with(['cardElements.element.tags'])
            ->whereIn('node_id', $nodes->keys())
            ->where(fn($query) => $this->filterByMana($query, $manaId))
            ->get()
            ->groupBy('node_id');
    }

    /**
     * Get character's owned cards and mana IDs.
     *
     * @param mixed $character
     * @param bool $isAdmin
     * @return array
     */
    private function getCharacterOwnership($character, bool $isAdmin): array
    {
        if ($isAdmin || !$character) {
            return [[], []];
        }

        $characterCards = $character->cards()
            ->with(['cardElements' => function ($query) {
                $query->where('type_id', self::TYPE_ESSENCE)
                    ->whereHas('element.tags', fn($tagQuery) =>
                    $tagQuery->where('tags.id', self::TAG_MANA)
                    )
                    ->with('element');
            }])
            ->get();

        $ownedCardIds = $characterCards->pluck('id')->map(fn($id) => (int) $id)->toArray();

        $ownedManaIds = $characterCards
            ->filter(fn($card) => !is_null($card->node_id))
            ->flatMap(fn($card) => $card->cardElements->pluck('element_id'))
            ->unique()
            ->values()
            ->toArray();

        return [$ownedCardIds, $ownedManaIds];
    }

    /*
    |--------------------------------------------------------------------------
    | Private Methods - Calculations
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate canvas bounds from node positions.
     *
     * @param Collection $nodes
     * @return array
     */
    private function calculateCanvasBounds(Collection $nodes): array
    {
        $minX = $nodes->min('x') ?? 0;
        $maxX = $nodes->max('x') ?? 0;
        $minY = $nodes->min('y') ?? 0;
        $maxY = $nodes->max('y') ?? 0;

        return [
            'minX' => $minX,
            'minY' => $minY,
            'width' => $maxX - $minX,
            'height' => $maxY - $minY,
        ];
    }

    /**
     * Pre-calculate mana IDs for each node.
     *
     * @param Collection $cards
     * @param array $ownedManaIds
     * @return array
     */
    private function calculateNodeManaIds(Collection $cards, array $ownedManaIds): array
    {
        if (empty($ownedManaIds)) {
            return [];
        }

        $nodeManaIds = [];

        foreach ($cards as $nodeId => $nodeCards) {
            $manaIds = $nodeCards
                ->flatMap(function ($card) {
                    return $card->cardElements
                        ->filter(fn($ce) =>
                            $ce->type_id === self::TYPE_ESSENCE &&
                            $ce->element->tags->contains('id', self::TAG_MANA)
                        )
                        ->pluck('element_id');
                })
                ->unique()
                ->toArray();

            $nodeManaIds[$nodeId] = $manaIds;
        }

        return $nodeManaIds;
    }

    /**
     * Calculate states for all nodes.
     *
     * @param Collection $nodes
     * @param Collection $cards
     * @param array $ownedCardIds
     * @param array $ownedManaIds
     * @param array $nodeManaIds
     * @param bool $isAdmin
     * @return array
     */
    private function calculateNodeStates(
        Collection $nodes,
        Collection $cards,
        array $ownedCardIds,
        array $ownedManaIds,
        array $nodeManaIds,
        bool $isAdmin
    ): array {
        // First pass: determine unlocked status
        $cardsByNode = $cards->map(fn($nodeCards) =>
        $nodeCards->pluck('id')->map(fn($id) => (int) $id)->toArray()
        );

        $nodeStates = [];
        foreach ($nodes as $node) {
            $nodeId = $node->id;
            $nodeCardIds = $cardsByNode->get($nodeId, []);

            $nodeStates[$nodeId]['unlocked'] = !empty($nodeCardIds) &&
                count(array_intersect($nodeCardIds, $ownedCardIds)) === count($nodeCardIds);
        }

        // Second pass: calculate final state
        foreach ($nodes as $node) {
            $nodeId = $node->id;
            $isUnlocked = $nodeStates[$nodeId]['unlocked'];
            $isLocked = $node->locked_by_id && empty($nodeStates[$node->locked_by_id]['unlocked']);
            $isAvailable = $this->allParentsUnlocked($node, $nodes, $nodeStates);
            $hasPollutedMana = $this->hasPollutedMana($nodeId, $ownedManaIds, $nodeManaIds);

            $state = $this->determineNodeState($isUnlocked, $isLocked, $isAvailable, $hasPollutedMana, $isAdmin);

            $nodeStates[$nodeId]['state'] = $state;
        }

        return $nodeStates;
    }

    /**
     * Build frontend-ready node data.
     *
     * @param Collection $nodes
     * @param Collection $cards
     * @param int $manaId
     * @param array $ownedCardIds
     * @param array $nodeStates
     * @return \Illuminate\Support\Collection
     */
    private function buildNodesData(
        Collection $nodes,
        Collection $cards,
        int $manaId,
        array $ownedCardIds,
        array $nodeStates
    ): Collection {
        return $nodes->map(function ($node) use ($manaId, $cards, $ownedCardIds, $nodeStates) {
            $nodeId = $node->id;
            $hasMana = $this->nodeContainsManaId($nodeId, $cards, $manaId);

            $nodeCards = $cards->get($nodeId, collect())->map(function ($card) use ($ownedCardIds) {
                return [
                    'cardId' => $card->id,
                    'img' => $card->image_path,
                    'title' => $card->title,
                    'acquired' => in_array((int) $card->id, $ownedCardIds, true),
                ];
            });

            return [
                'nodeId' => $nodeId,
                'parentId' => $node->parent_id,
                'position' => ['x' => $node->x, 'y' => $node->y],
                'blockedBy' => $node->locked_by_id,
                'state' => $nodeStates[$nodeId]['state'],
                'color' => $this->getManaColor($manaId, $hasMana),
                'cards' => $nodeCards,
            ];
        })->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Private Methods - Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Ensure the current user is an admin.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function ensureAdmin(): void
    {
        $user = Auth::guard('web')->user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Action non autorisée.');
        }
    }

    /**
     * Map elements for response format.
     *
     * @param Collection $elements
     * @return \Illuminate\Support\Collection
     */
    private function mapElementsForResponse(Collection $elements): Collection
    {
        return $elements->map(fn($element) => [
            'id' => $element->id,
            'name' => $element->name,
            'icon' => $element->icon?->path,
        ]);
    }

    /**
     * Check if all parent nodes are unlocked.
     *
     * @param Node $node
     * @param Collection $nodes
     * @param array $nodeStates
     * @return bool
     */
    private function allParentsUnlocked(Node $node, Collection $nodes, array $nodeStates): bool
    {
        $current = $node;

        while ($current && $current->parent_id) {
            $parent = $nodes->get($current->parent_id);

            if (!$parent || empty($nodeStates[$parent->id]['unlocked'])) {
                return false;
            }

            $current = $parent;
        }

        return true;
    }

    /**
     * Check if node has incompatible mana.
     *
     * @param int $nodeId
     * @param array $ownedManaIds
     * @param array $nodeManaIds
     * @return bool
     */
    private function hasPollutedMana(int $nodeId, array $ownedManaIds, array $nodeManaIds): bool
    {
        if (empty($ownedManaIds) || !isset($nodeManaIds[$nodeId])) {
            return false;
        }

        $nodeManas = $nodeManaIds[$nodeId];
        return !empty(array_diff($nodeManas, $ownedManaIds));
    }

    /**
     * Determine the final state of a node.
     *
     * @param bool $isUnlocked
     * @param bool $isLocked
     * @param bool $isAvailable
     * @param bool $hasPollutedMana
     * @param bool $isAdmin
     * @return string
     */
    private function determineNodeState(
        bool $isUnlocked,
        bool $isLocked,
        bool $isAvailable,
        bool $hasPollutedMana,
        bool $isAdmin
    ): string {
        if ($isAdmin) {
            return $isLocked ? 'locked' : 'neutral';
        }

        return match (true) {
            $isUnlocked && !$isAvailable => 'neutral-acquired',
            $isUnlocked => 'acquired',
            $isLocked => 'locked',
            $hasPollutedMana => 'neutral',
            $isAvailable => 'available',
            default => 'neutral',
        };
    }

    /**
     * Filter cards by mana ID or cards without mana.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $manaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function filterByMana($query, int $manaId)
    {
        return $query->whereHas('cardElements', fn($q) =>
        $q->where('type_id', self::TYPE_ESSENCE)
            ->where('element_id', $manaId)
        )->orWhereDoesntHave('cardElements', fn($q) =>
        $q->where('type_id', self::TYPE_ESSENCE)
            ->whereHas('element.tags', fn($q2) => $q2->where('tag_id', self::TAG_MANA))
        );
    }

    /**
     * Check if a node contains at least one card with the given mana ID.
     *
     * @param int $nodeId
     * @param Collection $cards
     * @param int $manaId
     * @return bool
     */
    private function nodeContainsManaId(int $nodeId, Collection $cards, int $manaId): bool
    {
        $nodeCards = $cards->get($nodeId, collect());

        if ($nodeCards->isEmpty()) {
            return false;
        }

        return $nodeCards->contains(function ($card) use ($manaId) {
            return $card->cardElements->contains(fn($el) =>
                $el->type_id === self::TYPE_ESSENCE && $el->element_id === $manaId
            );
        });
    }

    /**
     * Get the color for a mana ID.
     *
     * @param int|null $manaId
     * @param bool $hasMana
     * @return string
     */
    private function getManaColor(?int $manaId, bool $hasMana): string
    {
        if (!$hasMana) {
            return self::DEFAULT_COLOR;
        }

        return self::MANA_COLORS[$manaId] ?? self::DEFAULT_COLOR;
    }
}
