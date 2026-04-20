/**
 * Reports & Analytics JS
 * Initializes Chart.js visualizations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Utility for colors
    const colors = {
        primary:   '#0ea5a5',
        primaryBg: 'rgba(14, 165, 165, 0.7)',
        blue:      '#3b82f6',
        blueBg:    'rgba(59, 130, 246, 0.7)',
        green:     '#22c55e',
        greenBg:   'rgba(34, 197, 94, 0.7)',
        purple:    '#8b5cf6',
        purpleBg:  'rgba(139, 92, 246, 0.7)',
        danger:    '#ef4444',
        dangerBg:  'rgba(239, 68, 68, 0.7)',
        slate:     '#94a3b8',
        slateBg:   'rgba(148, 163, 184, 0.7)',
    };

    // ── 1. Event Status Chart (Doughnut) ──────────────────────────────────────
    const ctxEvent = document.getElementById('eventChart');
    if (ctxEvent) {
        new Chart(ctxEvent, {
            type: 'doughnut',
            data: {
                labels: chartData.eventLabels,
                datasets: [{
                    data: chartData.events,
                    backgroundColor: [
                        colors.blueBg,    // Upcoming
                        colors.primaryBg, // Ongoing
                        colors.greenBg,   // Completed
                        colors.dangerBg   // Cancelled
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true, font: { size: 12 } }
                    }
                }
            }
        });
    }

    // ── 2. Patient Gender Chart (Pie) ─────────────────────────────────────────
    const ctxPatient = document.getElementById('patientChart');
    if (ctxPatient) {
        new Chart(ctxPatient, {
            type: 'pie',
            data: {
                labels: chartData.patientLabels,
                datasets: [{
                    data: chartData.patients,
                    backgroundColor: [
                        colors.blueBg,   // Male
                        colors.purpleBg, // Female
                        colors.slateBg   // Other
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true, font: { size: 12 } }
                    }
                }
            }
        });
    }

    // ── 3. Volunteer Profession Chart (Horizontal Bar) ────────────────────────
    const ctxVolunteer = document.getElementById('volunteerChart');
    if (ctxVolunteer) {
        new Chart(ctxVolunteer, {
            type: 'bar',
            data: {
                labels: chartData.volunteerLabels,
                datasets: [{
                    label: 'Count',
                    data: chartData.volunteers,
                    backgroundColor: colors.primaryBg,
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { stepSize: 1 }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ── 4. Growth Trend Chart (Line) ──────────────────────────────────────────
    const ctxGrowth = document.getElementById('growthChart');
    if (ctxGrowth) {
        new Chart(ctxGrowth, {
            type: 'line',
            data: {
                labels: chartData.growthLabels,
                datasets: [{
                    label: 'New Patients',
                    data: chartData.growth,
                    borderColor: colors.purple,
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
