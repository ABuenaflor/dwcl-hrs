import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement, BarController, BarElement, CategoryScale, DoughnutController, Filler,
    Legend, LinearScale, LineController, LineElement, PointElement, Tooltip,
);

const css = getComputedStyle(document.documentElement);
const token = (name) => css.getPropertyValue(name).trim();

/* Categorical palette derived from the brand tokens, in a fixed order. */
const palette = () => [
    token('--color-brand-600'),
    token('--color-gold-500'),
    '#0f9f8f',
    token('--color-brand-300'),
    '#c2410c',
    '#7c3aed',
    token('--color-gold-300'),
    '#64748b',
];

Chart.defaults.font.family = token('--font-sans');
Chart.defaults.font.size = 12;
Chart.defaults.color = '#64748b';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;
Chart.defaults.plugins.tooltip.backgroundColor = token('--color-brand-950');
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.animation.duration = 700;

function build(canvas) {
    const { type, labels, data, label } = JSON.parse(canvas.dataset.chart);
    const colors = palette();
    const isRound = type === 'doughnut';
    const horizontal = canvas.dataset.horizontal !== undefined;

    const dataset = {
        label: label ?? 'Applications',
        data,
        borderWidth: isRound ? 2 : 0,
        borderColor: isRound ? '#fff' : colors[0],
        backgroundColor: isRound ? colors : colors[0],
        borderRadius: type === 'bar' ? 6 : 0,
        maxBarThickness: 28,
    };

    if (type === 'line') {
        Object.assign(dataset, {
            borderWidth: 2.5,
            tension: 0.35,
            fill: true,
            pointRadius: 3,
            pointBackgroundColor: colors[0],
            backgroundColor: (ctx) => {
                const { chartArea, ctx: c } = ctx.chart;
                if (!chartArea) return 'transparent';
                const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                g.addColorStop(0, colors[0] + '33');
                g.addColorStop(1, colors[0] + '00');
                return g;
            },
        });
    }

    return new Chart(canvas, {
        type,
        data: { labels, datasets: [dataset] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: horizontal ? 'y' : 'x',
            cutout: isRound ? '68%' : undefined,
            plugins: { legend: { display: isRound, position: 'bottom' } },
            scales: isRound
                ? {}
                : {
                      x: { grid: { display: horizontal, color: '#eef2f7' }, border: { display: false }, ticks: { precision: 0 } },
                      y: { grid: { display: !horizontal, color: '#eef2f7' }, border: { display: false }, ticks: { precision: 0 } },
                  },
        },
    });
}

export function initCharts() {
    document.querySelectorAll('canvas[data-chart]').forEach(build);
}
