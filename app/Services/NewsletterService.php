<?php

namespace App\Services;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class NewsletterService
{
    public function send(NewsletterCampaign $campaign): void
    {
        $campaign->update(['status' => 'sending']);

        $query = NewsletterSubscriber::active();

        if ($campaign->recipient_type === 'sellers') {
            $query->where('type', 'seller');
        } elseif ($campaign->recipient_type === 'buyers') {
            $query->where('type', 'buyer');
        }

        $subscribers = $query->get();
        $sent = 0;

        foreach ($subscribers as $subscriber) {
            $locale  = $subscriber->locale ?? 'es';
            $subject = $campaign->getSubjectForLocale($locale);
            $body    = $campaign->getBodyForLocale($locale);

            try {
                Mail::send([], [], function ($message) use ($subscriber, $subject, $body, $campaign) {
                    $unsubUrl = route('newsletter.unsubscribe', $subscriber->unsubscribe_token);
                    $html = $body . "<br><br><small><a href='{$unsubUrl}'>" . __('newsletter.unsubscribe') . "</a></small>";

                    $message->to($subscriber->email, $subscriber->name)
                            ->subject($subject)
                            ->html($html);
                });
                $sent++;
            } catch (\Exception $e) {
                \Log::error("Newsletter send failed for {$subscriber->email}: " . $e->getMessage());
            }

            // Small delay to avoid rate limits
            if ($sent % 50 === 0) {
                usleep(500000); // 0.5s every 50 emails
            }
        }

        $campaign->update([
            'status'          => 'sent',
            'sent_at'         => now(),
            'recipient_count' => $subscribers->count(),
            'sent_count'      => $sent,
        ]);
    }
}
