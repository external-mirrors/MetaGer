<?php

namespace App\Models\Assoc;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One generated pain.008.001.02 SEPA collection batch — see
 * SepaDirectDebitBatchGenerator. Every Debit it covers is flipped to
 * "submitted" and linked back here via sepa_batch_id, purely for admin
 * traceability ("which file did this go out in").
 *
 * @property string $id
 * @property string $message_id
 * @property \Carbon\Carbon $generated_at
 * @property int $debit_count
 * @property string $total_amount
 * @property string $xml_path
 */
class SepaBatch extends Model
{
    use HasUuids;

    protected $table = "assoc_sepa_batches";

    protected $fillable = ["message_id", "generated_at", "debit_count", "total_amount", "xml_path"];

    protected $casts = [
        "generated_at" => "datetime",
        "total_amount" => "decimal:2",
    ];

    public function debits(): HasMany
    {
        return $this->hasMany(Debit::class, "sepa_batch_id");
    }
}
