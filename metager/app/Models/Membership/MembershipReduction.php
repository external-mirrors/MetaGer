<?php

namespace App\Models\Membership;

use File;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $file_path
 * @property string $file_mimetype
 * @property \Carbon\Carbon $expires_at
 * @property MembershipApplication $application
 * @property string $application_id
 */
class MembershipReduction extends Model
{

    use HasUuids;
    protected $fillable = ["file_path", "file_mimetype", "expires_at", "application_id"];
    protected $casts = [
        'expires_at' => 'date:Y-m-d',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(MembershipApplication::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (MembershipReduction $reduction) {
            if ($reduction->file_path !== null && file_exists($reduction->file_path))
                File::delete($reduction->file_path);
        });
    }
}
