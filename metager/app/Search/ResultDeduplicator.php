<?php

namespace App\Search;

use App\Models\Result;
use Illuminate\Support\Arr;

/**
 * One page, one result — however many engines returned it.
 *
 * Engines overlap heavily, so the same page routinely arrives three or four
 * times with three or four spellings of its URL. Showing it once, credited to
 * every engine that found it, is the visible difference between a metasearch
 * engine and a list of other people's results glued together.
 *
 * ## What counts as the same page
 *
 * The URL with scheme, `www.` and a trailing slash normalised away, url-decoded
 * first. Nothing else: `?utm_source=…` still makes two URLs two pages, which is
 * characterized — deliberately, as a thing someone may fix on purpose — in
 * tests/Feature/Search/ResultMergingTest::testAQueryStringDefeatsDeduplication.
 *
 * On an image search the thumbnail URL is compared instead of the link, because
 * the same picture is what a duplicate means there.
 *
 * ## What merging keeps
 *
 * The first result wins the position and the ranking; every later copy of it
 * contributes its engine name and link (the "found by" line), its image and
 * inherited results if it has them, and its deep-result buttons, which are
 * appended rather than replaced.
 *
 * ## Why this is not the loop it used to be
 *
 * MetaGer::duplicationCheck walked the list by index and called array_splice()
 * for every duplicate it found — each of which shifts the whole tail down one
 * slot, so a result list of n with d duplicates cost O(n·d) element moves plus a
 * reallocation per removal, and the loop counter had to be walked backwards by
 * hand to stay in step. Building the kept list instead is one pass and no
 * shifting, and the index arithmetic disappears with it.
 *
 * It also dropped a reference (`$arr[$link] = &$this->results[$i]`) that was
 * never needed: the entries are objects, so mutating their properties is
 * already visible through every handle to them.
 */
class ResultDeduplicator
{
    /**
     * @param Result[] $results in ranked order
     * @return Result[] the same list with duplicates merged into their first occurrence
     */
    public function deduplicate(array $results, string $fokus): array
    {
        $firstSeenAt = [];
        $kept = [];

        foreach ($results as $result) {
            $key = $this->normalize($fokus === "bilder" ? $result->image->thumbnail : $result->link);

            if (!isset($firstSeenAt[$key])) {
                $firstSeenAt[$key] = $result;
                $kept[] = $result;
                continue;
            }

            $this->mergeInto($firstSeenAt[$key], $result, $kept === [] ? null : $kept[count($kept) - 1]);
        }

        return $kept;
    }

    /**
     * Two spellings of the same page have to reduce to the same string.
     *
     * Written out as four sequential steps rather than one regex because that
     * is what it is: the order matters (`https://www.` needs both strips) and
     * each step is exactly what it says.
     */
    private function normalize(string $link): string
    {
        $link = urldecode($link);

        if (str_starts_with($link, "http://")) {
            $link = substr($link, 7);
        }

        if (str_starts_with($link, "https://")) {
            $link = substr($link, 8);
        }

        if (str_starts_with($link, "www.")) {
            $link = substr($link, 4);
        }

        return trim($link, "/");
    }

    /**
     * Fold a duplicate into the copy that is being kept.
     *
     * `$previouslyKept` is the result before this one in the kept list. It has
     * nothing to do with the duplicate and is only read to set `changed` —
     * see the note on that below.
     */
    private function mergeInto(Result $kept, Result $duplicate, ?Result $previouslyKept): void
    {
        // Two parallel arrays rather than one list of pairs, which is why they
        // are appended together and never separately: the view walks them by
        // index to build the "found by" line.
        $kept->gefVon[] = $duplicate->gefVon[0];
        $kept->gefVonLink[] = $duplicate->gefVonLink[0];

        if (!empty($duplicate->image)) {
            $kept->image = $duplicate->image;
        }

        if (!empty($duplicate->inheritedResults)) {
            $kept->inheritedResults = $duplicate->inheritedResults;
        }

        // Buttons are merged, not overwritten: two engines can each know a
        // different sub-page of the same site.
        if (!empty(Arr::get($duplicate->deepResults, "buttons", []))) {
            $kept->deepResults = Arr::set(
                $kept->deepResults,
                "buttons",
                array_merge(
                    Arr::get($kept->deepResults, "buttons", []),
                    Arr::get($duplicate->deepResults, "buttons", [])
                )
            );
        }

        // Preserved exactly as MetaGer::duplicationCheck computed it, including
        // the part that looks wrong: the second operand reads the *previous
        // kept* result rather than the duplicate being merged. In the original
        // that fell out of array_splice() plus a hand-rolled `$i--`, which left
        // the index pointing one before the removed element — almost certainly
        // not what was meant, since the duplicate is the only result whose
        // newness could say anything about this one.
        //
        // It is kept because nothing reads `changed`: no blade, no stylesheet
        // and no line of resources/js refers to it. It survives only as a field
        // of the load-more JSON payload, via get_object_vars() in
        // MetaGerSearch::loadMore, so changing it changes a published payload
        // to no purpose. Whoever removes that field can delete this with it.
        if ($kept->new === true || $previouslyKept?->new === true) {
            $kept->changed = true;
        }
    }
}
