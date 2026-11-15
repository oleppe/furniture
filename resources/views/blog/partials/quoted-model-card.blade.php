<div class="bg-surface-panel border border-primary/30 rounded-xl p-space-md space-y-space-sm my-space-sm">
    <div class="flex flex-wrap items-center justify-between gap-space-xs">
        <div class="flex items-center gap-2">
            <span class="px-space-sm py-0.5 rounded bg-primary/15 text-primary font-label-code text-label-code">{{ $quote['badge'] }}</span>
            <span class="font-label-code text-label-code text-text-tertiary">{{ $quote['provider'] }}</span>
        </div>
        @if (! empty($quote['has_profile']))
            <a href="{{ route('models.show', $quote['slug']) }}" class="inline-flex items-center gap-1 font-label-code text-label-code text-primary hover:underline">
                ملف {{ $quote['name'] }}
                <span class="material-symbols-outlined text-[14px]" aria-hidden="true">arrow_back</span>
            </a>
        @endif
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm">
        <div class="space-y-1">
            <div class="font-headline-sm text-headline-sm text-text-primary">{{ $quote['name'] }}</div>
            @if (! empty($quote['quote']))
                <blockquote class="font-body-sm text-body-sm text-text-secondary border-r-2 border-primary pr-3 leading-relaxed">
                    «{{ $quote['quote'] }}»
                </blockquote>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:flex sm:items-center gap-space-xs shrink-0">
            @if ($quote['score'] !== null)
                <div class="px-space-sm py-1.5 rounded-lg bg-surface-base border border-border-subtle text-center">
                    <div class="font-label-code text-label-code text-text-tertiary">العربي</div>
                    <div class="font-metric-cell text-metric-cell text-text-primary" dir="ltr">{{ $quote['score'] }}/156</div>
                </div>
            @endif
            @if ($quote['tpw'] !== null)
                <div class="px-space-sm py-1.5 rounded-lg bg-surface-base border border-border-subtle text-center">
                    <div class="font-label-code text-label-code text-text-tertiary">التقطيع</div>
                    <div class="font-metric-cell text-metric-cell text-metric-efficiency" dir="ltr">{{ number_format($quote['tpw'], 2) }}</div>
                </div>
            @endif
            @if ($quote['in_price'] !== null)
                <div class="px-space-sm py-1.5 rounded-lg bg-surface-base border border-border-subtle text-center">
                    <div class="font-label-code text-label-code text-text-tertiary">إدخال/1M</div>
                    <div class="font-metric-cell text-metric-cell text-metric-cost" dir="ltr">${{ number_format($quote['in_price'], 2) }}</div>
                </div>
            @endif
            @if ($quote['tps'] !== null)
                <div class="px-space-sm py-1.5 rounded-lg bg-surface-base border border-border-subtle text-center">
                    <div class="font-label-code text-label-code text-text-tertiary">السرعة</div>
                    <div class="font-metric-cell text-metric-cell text-metric-speed" dir="ltr">{{ number_format($quote['tps'], 0) }} t/s</div>
                </div>
            @endif
        </div>
    </div>
</div>

