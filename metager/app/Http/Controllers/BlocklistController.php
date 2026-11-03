<?php

namespace App\Http\Controllers;

use App\Models\BlocklistEntry;
use App\Search\Blacklists;
use App\Search\BlocklistNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * /api/blocklist — suma-crm's view of the operator blocklist.
 *
 * docs/blocklist-api.md is the contract, and Tests\Feature\BlocklistApiTest
 * asserts its shapes. Every write goes through the model, whose events bump
 * `blocklist:version`, so a change applies to the next search on every pod.
 */
class BlocklistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            "kind" => ["nullable", Rule::in(BlocklistEntry::KINDS)],
            "reason" => ["nullable", Rule::in(BlocklistEntry::REASONS)],
            "q" => ["nullable", "string", "max:2048"],
            "external_ref" => ["nullable", "string", "max:191"],
            "review_due" => ["nullable", "boolean"],
            "trashed" => ["nullable", "boolean"],
            "page" => ["nullable", "integer", "min:1"],
            "per_page" => ["nullable", "integer", "min:1", "max:200"],
        ]);

        $query = BlocklistEntry::query()
            ->when($request->boolean("trashed"), fn(Builder $q) => $q->withTrashed())
            ->when($filters["kind"] ?? null, fn(Builder $q, string $kind) => $q->where("kind", $kind))
            ->when($filters["reason"] ?? null, fn(Builder $q, string $reason) => $q->where("reason", $reason))
            ->when($filters["external_ref"] ?? null, fn(Builder $q, string $ref) => $q->where("external_ref", $ref))
            ->when($request->boolean("review_due"), fn(Builder $q) => $q->reviewDue())
            ->when($filters["q"] ?? null, function (Builder $q, string $needle) {
                $like = "%" . mb_strtolower($needle) . "%";
                $q->where(fn(Builder $q) => $q
                    ->whereRaw("LOWER(value) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(note) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(external_ref) LIKE ?", [$like]));
            })
            ->orderByDesc("id");

        $page = $query->paginate((int) ($filters["per_page"] ?? 50), ["*"], "page", (int) ($filters["page"] ?? 1));

        return response()->json([
            "data" => array_map(fn(BlocklistEntry $e) => $e->toApiArray(), $page->items()),
            "meta" => [
                "current_page" => $page->currentPage(),
                "last_page" => $page->lastPage(),
                "per_page" => $page->perPage(),
                "total" => $page->total(),
                "review_due" => BlocklistEntry::query()->reviewDue()->count(),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $entry = $this->find($id, withTrashed: true);
        if ($entry === null) {
            return $this->notFound();
        }

        return response()->json(["data" => $entry->toApiArray()]);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->validate([
            "kind" => ["required", Rule::in(BlocklistEntry::KINDS)],
            "value" => ["required", "string", "max:2048"],
            "terms" => ["nullable", "array", "prohibited_unless:kind," . BlocklistEntry::KIND_URL_TERMS, "required_if:kind," . BlocklistEntry::KIND_URL_TERMS],
            "terms.*" => ["string", "max:200"],
            "reason" => ["required", Rule::in(BlocklistEntry::REASONS)],
            "note" => ["nullable", "string", "max:2000"],
            "external_ref" => ["nullable", "string", "max:191"],
            "created_by" => ["nullable", "string", "max:191"],
            "review_at" => ["nullable", "date_format:Y-m-d"],
        ]);

        $kind = $input["kind"];
        $value = BlocklistEntry::normalizeValue($kind, $input["value"]);
        $terms = BlocklistEntry::normalizeTerms($kind, $input["terms"] ?? null);

        if ($value === "") {
            throw ValidationException::withMessages(["value" => ["The value does not contain a host."]]);
        }
        if ($kind === BlocklistEntry::KIND_URL_TERMS && ($terms === null || $terms === "")) {
            throw ValidationException::withMessages(["terms" => ["The terms contain no words."]]);
        }
        if (($duplicate = BlocklistEntry::activeDuplicate($kind, $value, $terms)) !== null) {
            throw ValidationException::withMessages(["value" => ["An identical entry already exists (id {$duplicate->id})."]]);
        }

        $entry = BlocklistEntry::create([
            "kind" => $kind,
            "value" => $value,
            "terms" => $terms,
            "reason" => $input["reason"],
            "note" => $input["note"] ?? null,
            "external_ref" => $input["external_ref"] ?? null,
            "created_by" => $input["created_by"] ?? null,
            "review_at" => $input["review_at"] ?? null,
        ]);

        return response()->json(["data" => $entry->refresh()->toApiArray()], 201);
    }

    /**
     * Only the bookkeeping fields. kind, value and terms are refused rather
     * than ignored: changing what an entry blocks is a delete and a new
     * entry, so the history says what changed.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $entry = $this->find($id);
        if ($entry === null) {
            return $this->notFound();
        }

        $input = $request->validate([
            "kind" => ["prohibited"],
            "value" => ["prohibited"],
            "terms" => ["prohibited"],
            "reason" => ["sometimes", "required", Rule::in(BlocklistEntry::REASONS)],
            "note" => ["sometimes", "nullable", "string", "max:2000"],
            "external_ref" => ["sometimes", "nullable", "string", "max:191"],
            "review_at" => ["sometimes", "nullable", "date_format:Y-m-d"],
        ]);

        $entry->fill($input)->save();

        return response()->json(["data" => $entry->refresh()->toApiArray()]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $entry = $this->find($id);
        if ($entry === null) {
            return $this->notFound();
        }

        $input = $request->validate([
            "deleted_by" => ["nullable", "string", "max:191"],
        ]);

        if (($input["deleted_by"] ?? null) !== null) {
            $entry->deleted_by = $input["deleted_by"];
            $entry->save();
        }
        $entry->delete();

        return response()->json(["data" => $entry->refresh()->toApiArray()]);
    }

    /**
     * Would this result be dropped for this query right now, and by what?
     */
    public function test(Request $request, Blacklists $blacklists): JsonResponse
    {
        $input = $request->validate([
            "url" => ["required", "string", "max:4096"],
            "query" => ["present", "nullable", "string", "max:2048"],
        ]);

        $query = (string) ($input["query"] ?? "");
        $host = BlocklistNormalizer::host($input["url"]);
        $link = BlocklistNormalizer::link($input["url"]);

        $blocking = $blacklists->blockingEntryIds($host, $link, $query);
        $description = $blacklists->descriptionEntryIds($link);

        $ids = array_values(array_unique(array_merge($blocking, $description)));
        $matches = $ids === [] ? [] : BlocklistEntry::query()->whereIn("id", $ids)->orderBy("id")->get()
            ->map(fn(BlocklistEntry $e) => $e->toApiArray())
            ->all();

        return response()->json([
            "data" => [
                "url" => $link,
                "query" => $query,
                "blocked" => $blocking !== [],
                "description_blanked" => $description !== [],
                "matches" => $matches,
            ],
        ]);
    }

    private function find(string $id, bool $withTrashed = false): ?BlocklistEntry
    {
        if (!ctype_digit($id)) {
            return null;
        }

        return BlocklistEntry::query()
            ->when($withTrashed, fn(Builder $q) => $q->withTrashed())
            ->find((int) $id);
    }

    private function notFound(): JsonResponse
    {
        return response()->json(["error" => "Not found"], 404);
    }
}
