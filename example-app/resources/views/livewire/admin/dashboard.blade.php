<div class="space-y-6">
    <h1 class="text-2xl font-semibold">Dashboard</h1>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Orders Today</div>
            <div class="text-2xl font-bold">{{ $kpis['todayOrders'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Orders This Week</div>
            <div class="text-2xl font-bold">{{ $kpis['weekOrders'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Revenue Today (Rs)</div>
            <div class="text-2xl font-bold">{{ number_format($kpis['todayRevenue'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Avg Basket (30d)</div>
            <div class="text-2xl font-bold">{{ $kpis['avgBasket'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-6">Most Ordered Products (30 days)</h2>
            <div class="mb-6">
                <canvas id="mostOrderedChart" height="220"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-6">Top Viewed Products (30 days)</h2>
            <div class="mb-6">
                <canvas id="topViewsChart" height="220"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Orders (Today vs Week)</h2>
            <canvas id="ordersChart" height="140"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function horizontalBar(ctxId, labels, data) {
                const ctx = document.getElementById(ctxId);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: ['#8B4513', '#C19A6B', '#DEB887', '#E6BE8A', '#F5DEB3'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { display: false } },
                            y: { ticks: { display: false } }
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            horizontalBar('mostOrderedChart', @json($mostOrdered['labels'] ?? []), @json($mostOrdered['data'] ?? []));
            horizontalBar('topViewsChart', @json($topViews['labels'] ?? []), @json($topViews['data'] ?? []));

            const ordersCtx = document.getElementById('ordersChart');
            new Chart(ordersCtx, {
                type: 'bar',
                data: {
                    labels: ['Today','Week'],
                    datasets: [{
                        label: 'Orders',
                        data: [{{ $kpis['todayOrders'] ?? 0 }}, {{ $kpis['weekOrders'] ?? 0 }}],
                        backgroundColor: ['#dc9572','#8B4513']
                    }]
                },
                options: { plugins: { legend: { display:false } } }
            });
        });
    </script>
</div>
                   
</div>
