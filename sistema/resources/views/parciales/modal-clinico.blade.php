<div x-show="showClinicalDisclaimer"
     x-transition.opacity
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showClinicalDisclaimer = false"></div>

    <div class="relative w-full max-w-2xl rounded-2xl border border-border px-8 py-7 shadow-2xl"
         style="background:hsl(var(--card));">
        
        <div class="relative mb-6 border-b border-border pb-5 text-center">
            <h2 class="text-xl font-bold tracking-wide text-foreground">AVISO LEGAL Y CLÍNICO</h2>
            <p class="text-xs uppercase tracking-widest text-primary mt-2 font-bold">Importante para el profesional</p>

            <button type="button"
                    @click="showClinicalDisclaimer = false"
                    class="absolute right-0 top-0 inline-flex items-center justify-center w-8 h-8 rounded-lg hover:bg-secondary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-4 text-sm leading-relaxed text-muted-foreground">
            <p>Este sistema utiliza técnicas de <strong class="text-foreground">Inteligencia Artificial (IA)</strong> para procesar señales ECG y sugerir clasificaciones de arritmias basadas en el dataset PTB-XL.</p>
            
            <div class="bg-muted/50 p-4 rounded-xl border-l-4 border-primary italic">
                "Los resultados presentados son sugerencias algorítmicas y no constituyen un diagnóstico definitivo."
            </div>

            <ul class="space-y-2">
                <li class="flex gap-3">
                    <span class="text-primary font-bold">●</span>
                    <span>El diagnóstico final es responsabilidad exclusiva del <strong class="text-foreground">médico cardiólogo</strong>.</span>
                </li>
                <li class="flex gap-3">
                    <span class="text-primary font-bold">●</span>
                    <span>No nos hacemos responsables por decisiones tomadas basándose únicamente en los resultados de la IA.</span>
                </li>
            </ul>
        </div>

        <div class="mt-8 flex justify-center">
            <button type="button" @click="showClinicalDisclaimer = false" class="btn-primary px-10"> Entendido </button>
        </div>
    </div>
</div>
