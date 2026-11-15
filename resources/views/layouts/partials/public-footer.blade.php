<footer class="public-footer w-full bg-surface-base border-t border-border-subtle mt-space-xl">
    <div class="w-full px-gutter-desktop py-space-xl">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-space-lg">

            <!-- Brand Column -->
            <div class="space-y-space-sm">
                <div class="flex items-center gap-space-sm">
                    <img src="{{ asset('images/idraak-lab-logo.png') }}" alt="شعار إدراك Lab" width="56" height="56" loading="lazy" decoding="async" class="w-14 h-14 shrink-0 object-cover">
                    <div>
                        <div class="font-headline-sm text-headline-sm text-text-primary">إدراك Lab</div>
                        <div class="font-label-code text-label-code text-text-tertiary">Idraak Lab</div>
                    </div>
                </div>
                <p class="font-body-sm text-body-sm text-text-secondary leading-relaxed">
                    المنصة المرجعية الأولى المتخصصة في قياس دقة واقتصاديات نماذج الذكاء الاصطناعي التوليدي للغة العربية وفق معايير الحوسبة السحابية المتقدمة.
                </p>
                <div class="flex items-center gap-space-xs font-label-code text-label-code text-text-tertiary">
                    <span class="material-symbols-outlined text-[14px]">insights</span>
                    كفاءة التوكينز: معيار Cl100k_base القياسي
                </div>
            </div>

            <!-- Standards & Documentation -->
            <div>
                <h4 class="font-headline-sm text-headline-sm text-text-primary mb-space-sm">المعايير والأبحاث</h4>
                <ul class="space-y-space-xs font-body-sm text-body-sm text-text-secondary">
                    <li><a href="{{ route('blog.index') }}" class="hover:text-primary transition-colors">المدونة والأبحاث التقنية</a></li>
                    <li><a href="{{ route('blog.show', 'arabic-token-efficiency-analysis') }}" class="hover:text-primary transition-colors">دراسة كفاءة التوكن العربي</a></li>
                    <li><a href="{{ route('home') }}#methodology" class="hover:text-primary transition-colors">منهجية التقييم القياسي (MMLU-AR)</a></li>
                    <li><a href="{{ route('home') }}#calculator-widget" class="hover:text-primary transition-colors">حاسبة تكلفة التوكن العربي</a></li>
                    <li><a href="{{ route('home') }}#faq" class="hover:text-primary transition-colors">الأسئلة الشائعة</a></li>
                </ul>
            </div>

            <!-- Data Sources -->
            <div>
                <h4 class="font-headline-sm text-headline-sm text-text-primary mb-space-sm">مصادر البيانات والتكامل</h4>
                <ul class="space-y-space-xs font-body-sm text-body-sm text-text-secondary">
                    <li><a href="#" class="hover:text-primary transition-colors">OpenRouter Telemetry Data</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">LMSYS Chatbot Arena Weights</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Hugging Face Arabic Benchmark Hub</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">حالة خوادم الفحص (System Status)</a></li>
                </ul>
            </div>

            <!-- Compliance -->
            <div>
                <h4 class="font-headline-sm text-headline-sm text-text-primary mb-space-sm">الامتثال والاتصال</h4>
                <ul class="space-y-space-xs font-body-sm text-body-sm text-text-secondary">
                    <li><a href="#" class="hover:text-primary transition-colors">اتفاقيات مستوى الخدمة (SLA)</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">سياسة الخصوصية وحوكمة البيانات</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">تسجيل مزود نموذج جديد</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">فريق الباحثين والشركاء الأكاديميين</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-space-xl pt-space-md border-t border-border-subtle flex flex-col md:flex-row items-center justify-between gap-space-sm">
            <div class="font-body-sm text-body-sm text-text-tertiary text-center md:text-right">
                جميع الحقوق محفوظة © 2025 منصة إدراك Lab للذكاء الاصطناعي (Idrak Lab). تم التصميم لبيئات الذكاء الاصطناعي فائقة الدقة.
            </div>
            <div class="flex items-center gap-space-md font-label-code text-label-code text-text-tertiary">
                <span>v2.4.8-telemetry</span>
                <span class="w-px h-3 bg-border-subtle"></span>
                <span>RTL-Enabled</span>
                <span class="w-px h-3 bg-border-subtle"></span>
                <span class="text-metric-efficiency">LATENCY: 42MS OK</span>
            </div>
        </div>
    </div>
</footer>
