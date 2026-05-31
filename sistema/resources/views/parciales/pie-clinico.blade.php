@php($withSidebar = $withSidebar ?? false)

<footer class="fixed bottom-0 left-0 right-0 z-40 border-t border-border px-3 lg:px-5 py-2 surface-card-glass">
    <div class="{{ $withSidebar ? 'lg:ml-64' : '' }} flex items-center justify-center gap-2 text-center flex-wrap">
        <p class="text-[11px] leading-4 text-muted-foreground">
            Herramienta de apoyo. No reemplaza la evaluacion ni el criterio del medico cardiologo.
        </p>

        <a href="#"
           @click.prevent="showClinicalDisclaimer = true"
           class="text-[11px] font-medium text-primary underline underline-offset-2 transition-opacity hover:opacity-70">
            Mas informacion
        </a>
    </div>
</footer>

<div x-show="showClinicalDisclaimer"
     x-transition.opacity
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display:none;">
    <div class="absolute inset-0 bg-black/40" @click="showClinicalDisclaimer = false"></div>

    <div class="relative w-full max-w-2xl rounded-2xl border border-border px-8 py-7 shadow-elevated surface-card">
        <div class="relative mb-6 border-b border-border pb-5 text-center">
            <div class="mx-auto max-w-xl">
                <h2 class="text-xl font-bold tracking-wide text-foreground">IMPORTANTE</h2>
                <p class="text-sm leading-6 text-muted-foreground mt-3">
                    El sistema ayuda a apoyar la lectura del ECG, pero no sustituye la decision clinica final.
                </p>
            </div>

            <button type="button"
                    @click="showClinicalDisclaimer = false"
                    class="absolute right-0 top-0 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border hover:bg-secondary transition-colors">
                <span class="sr-only">Cerrar</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <ol class="mx-auto max-w-xl space-y-4 text-sm leading-7 text-muted-foreground text-justify">
            <li>
                <span class="font-semibold text-foreground">1.</span>
                Este sistema utiliza tecnicas de inteligencia artificial, especificamente redes neuronales, para analizar electrocardiogramas (ECG) y apoyar en la clasificacion de posibles arritmias cardiacas.
            </li>
            <li>
                <span class="font-semibold text-foreground">2.</span>
                El modelo ha sido entrenado con un conjunto de datos clinicos previamente etiquetados (PTB-XL), lo que le permite reconocer patrones en senales ECG y sugerir una posible clasificacion.
            </li>
            <li>
                <span class="font-semibold text-foreground">3.</span>
                Este sistema es una herramienta de apoyo y puede presentar errores; los resultados deben ser siempre interpretados por un medico especialista.
            </li>
            <li>
                <span class="font-semibold text-foreground">4.</span>
                Esta orientado a apoyar al personal de salud en la evaluacion preliminar de electrocardiogramas y agilizar el proceso de analisis.
            </li>
            <li>
                <span class="font-semibold text-foreground">5.</span>
                Este sistema forma parte de un proyecto de investigacion academica sobre el uso de redes neuronales en el diagnostico de arritmias cardiacas.
            </li>
        </ol>

        <div class="mt-6 flex justify-center">
            <button type="button"
                    @click="showClinicalDisclaimer = false"
                    class="btn-primary">
                Cerrar
            </button>
        </div>
    </div>
</div>
