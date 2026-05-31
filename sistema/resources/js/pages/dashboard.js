export function metricsPage(config = {}) {
    const dashboardData = config.dashboardData ?? {};

    return {
        animated: false,
        metrics: dashboardData.metricCards ?? [],
        secondary: dashboardData.secondaryCards ?? [],
        metricDisplay: {},
        secondaryDisplay: {},

        init() {
            this.metrics.forEach((metric) => {
                this.metricDisplay[metric.key] = '0.0%';
            });

            this.secondary.forEach((card) => {
                this.secondaryDisplay[card.key] = card.format === 'score' ? '0.000' : '0.0%';
            });

            setTimeout(() => {
                this.animated = true;
                this.animateMetrics();
                this.animateSecondary();
            }, 250);
        },

        animateMetrics() {
            const duration = 1200;
            const start = performance.now();
            const targets = Object.fromEntries(this.metrics.map((metric) => [metric.key, Number(metric.value) || 0]));

            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);

                Object.keys(targets).forEach((key) => {
                    this.metricDisplay[key] = `${(targets[key] * ease).toFixed(1)}%`;
                });

                if (t < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },

        animateSecondary() {
            const duration = 1200;
            const start = performance.now();

            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);

                this.secondary.forEach((card) => {
                    const target = Number(card.value) || 0;

                    if (card.format === 'score') {
                        this.secondaryDisplay[card.key] = (target * ease).toFixed(3);
                    } else {
                        this.secondaryDisplay[card.key] = `${(target * 100 * ease).toFixed(1)}%`;
                    }
                });

                if (t < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        },
    };
}
