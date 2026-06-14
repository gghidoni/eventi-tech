<?php

namespace App\Models;

use App\Enums\CfpSubmissionStatus;
use Database\Factories\CfpSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CfpSubmission extends Model
{
    /** @use HasFactory<CfpSubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'cfp_id',
        'user_id',
        'title',
        'abstract',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'status'       => CfpSubmissionStatus::class,
        'submitted_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Cfp, $this>
     */
    public function cfp(): BelongsTo
    {
        return $this->belongsTo(Cfp::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CfpSubmissionAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(CfpSubmissionAnswer::class);
    }
}
