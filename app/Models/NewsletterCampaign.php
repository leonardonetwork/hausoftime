<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'name', 'subject_es', 'subject_en', 'body_es', 'body_en',
        'recipient_type', 'custom_emails', 'status',
        'recipient_count', 'sent_count', 'scheduled_at', 'sent_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'custom_emails' => 'array',
            'scheduled_at'  => 'datetime',
            'sent_at'       => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSubjectForLocale(string $locale): string
    {
        return $locale === 'en' ? $this->subject_en : $this->subject_es;
    }

    public function getBodyForLocale(string $locale): string
    {
        return $locale === 'en' ? $this->body_en : $this->body_es;
    }
}
