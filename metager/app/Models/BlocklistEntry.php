<?php

namespace App\Models;

use App\Search\BlocklistNormalizer;
use App\Search\BlocklistVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

/**
 * One line of the operator blocklist (App\Search\Blacklists).
 *
 * The API shape is docs/blocklist-api.md, and toApiArray() is its only
 * spelling. `value` and `terms` are stored normalised (BlocklistNormalizer),
 * so what is in the table is exactly what a lookup compares against.
 *
 * @property int $id
 * @property string $kind
 * @property string $value
 * @property ?string $terms space-joined, see the migration
 * @property string $reason
 * @property ?string $note
 * @property ?string $external_ref
 * @property ?string $created_by
 * @property ?string $deleted_by
 * @property ?\Illuminate\Support\Carbon $review_at
 */
class BlocklistEntry extends Model
{
    use SoftDeletes;

    /** Every result on the host is dropped. */
    public const KIND_DOMAIN = "domain";
    /** That page is dropped. */
    public const KIND_URL = "url";
    /** That page is dropped when every term is in the query. */
    public const KIND_URL_TERMS = "url_terms";
    /** The result stays, its description is blanked. */
    public const KIND_DESCRIPTION = "description";

    public const KINDS = [self::KIND_DOMAIN, self::KIND_URL, self::KIND_URL_TERMS, self::KIND_DESCRIPTION];
    public const REASONS = ["spam", "legal", "removal_request", "other"];

    protected $table = "blocklist_entries";

    protected $fillable = ["kind", "value", "terms", "reason", "note", "external_ref", "created_by", "review_at"];

    protected function casts(): array
    {
        return [
            "review_at" => "date",
        ];
    }

    protected static function booted(): void
    {
        // After the commit, not before: a worker that sees the new version
        // has to find the new rows when it reloads.
        static::saved(fn() => static::bumpVersion());
        static::deleted(fn() => static::bumpVersion());
        static::restored(fn() => static::bumpVersion());
    }

    /**
     * Tell every worker its snapshot is stale.
     *
     * A failure is logged and swallowed: the row is already written, and
     * failing the request would only make the caller retry a write that
     * succeeded. Workers then keep their old snapshot until the next
     * successful bump, which is the same state an outage of Redis puts them
     * in anyway.
     */
    public static function bumpVersion(): void
    {
        try {
            BlocklistVersion::bump();
        } catch (\Throwable $e) {
            Log::warning("blocklist: could not bump " . BlocklistVersion::KEY . ": " . $e->getMessage());
        }
    }

    /**
     * Normalise $value for $kind the way lookups will see it.
     */
    public static function normalizeValue(string $kind, string $value): string
    {
        return $kind === self::KIND_DOMAIN
            ? BlocklistNormalizer::host($value)
            : BlocklistNormalizer::link($value);
    }

    /** @return list<string> */
    public function termList(): array
    {
        return $this->terms === null || $this->terms === "" ? [] : explode(" ", $this->terms);
    }

    /** @param Builder<BlocklistEntry> $query */
    public function scopeReviewDue(Builder $query): void
    {
        $query->whereNotNull("review_at")->whereDate("review_at", "<=", now()->toDateString());
    }

    /**
     * The entry as docs/blocklist-api.md spells it.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            "id" => $this->id,
            "kind" => $this->kind,
            "value" => $this->value,
            "terms" => $this->kind === self::KIND_URL_TERMS ? $this->termList() : null,
            "reason" => $this->reason,
            "note" => $this->note,
            "external_ref" => $this->external_ref,
            "created_by" => $this->created_by,
            "review_at" => $this->review_at?->format("Y-m-d"),
            "created_at" => $this->created_at?->toIso8601String(),
            "updated_at" => $this->updated_at?->toIso8601String(),
            "deleted_at" => $this->deleted_at?->toIso8601String(),
        ];
    }
}
