<?php

namespace Tests\Concerns;

use App\Search\BlocklistStore;

/**
 * Puts entries into MetaGer's list the way `blocklist:sync` does: as a
 * snapshot in Valkey, with `blocklist:version` bumped. Values are taken as the
 * blocklist service would send them, already normalised.
 *
 * The suite shares one Valkey, so the snapshot is removed before and after
 * every test that uses this.
 */
trait FakesBlocklist
{
    /** @var list<array{id: int, kind: string, value: string, terms: ?list<string>}> */
    private array $blocklistEntries = [];

    private int $blocklistNextId = 1;

    protected function setUpFakesBlocklist(): void
    {
        BlocklistStore::forget();
    }

    protected function tearDownFakesBlocklist(): void
    {
        BlocklistStore::forget();
    }

    /**
     * @param ?string $terms space-separated, for url_terms
     * @return int the entry's id
     */
    protected function blocklistEntry(string $kind, string $value, ?string $terms = null): int
    {
        $id = $this->blocklistNextId++;
        $this->blocklistEntries[] = [
            "id" => $id,
            "kind" => $kind,
            "value" => $value,
            "terms" => $kind === "url_terms" ? array_values(array_filter(explode(" ", (string) $terms), fn($t) => $t !== "")) : null,
        ];
        $this->publishBlocklist();

        return $id;
    }

    protected function removeBlocklistEntry(int $id): void
    {
        $this->blocklistEntries = array_values(array_filter($this->blocklistEntries, fn(array $e) => $e["id"] !== $id));
        $this->publishBlocklist();
    }

    private function publishBlocklist(): void
    {
        static $version = 0;
        BlocklistStore::put([
            "format" => 1,
            "service" => "metager",
            "version" => ++$version,
            "changed_at" => null,
            "entries" => $this->blocklistEntries,
        ], null);
    }
}
