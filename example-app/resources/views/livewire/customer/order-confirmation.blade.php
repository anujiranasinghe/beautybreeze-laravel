<div class="min-h-screen bg-[#D1BB91]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-24">
        <div class="bg-white/95 rounded-2xl shadow-lg p-8">
            <h1 class="text-2xl font-semibold text-green-700">Payment Successful</h1>
            <p class="text-sm text-gray-600 mt-1">Order #{{ $order->OrderId }} — {{ $order->created_at?->format('Y-m-d H:i') }}</p>

            <div class="mt-6">
                <h2 class="text-lg font-semibold">Billing</h2>
                <div class="mt-2 text-sm text-gray-700">
                    <div>{{ $order->CustomerName }}</div>
                    <div>{{ $order->Email }}</div>
                    <div>{{ $order->PhoneNo }}</div>
                    <div class="whitespace-pre-line">{{ $order->Address }}</div>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-semibold">Items</h2>
                <div class="mt-2 divide-y">
                    @php $total = 0; @endphp
                    @foreach($order->items as $item)
                        @php $total += (float)$item->TotalPrice; @endphp
                        <div class="py-3 flex justify-between text-sm">
                            <div>
                                <div class="font-medium">{{ $item->ProductName }}</div>
                                <div class="text-gray-600">Qty: {{ $item->Quantity }} Rs {{ number_format($item->UnitPrice,2) }}</div>
                            </div>
                            <div class="font-semibold">Rs {{ number_format($item->TotalPrice,2) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 border-t pt-3 flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span>Rs {{ number_format($total,2) }}</span>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <a href="{{ route('orders') }}" class="px-4 py-2 rounded-md border">Track Order</a>
                <button id="download-pdf" class="px-4 py-2 rounded-md bg-[#8B4513] text-white">Download Receipt (PDF)</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script>
        document.getElementById('download-pdf')?.addEventListener('click', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Set colors and fonts
            const primaryColor = '#8B4513';
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(24);
            doc.setTextColor(primaryColor);

            // Header - Centered
            doc.text('BeautyBreeze', doc.internal.pageSize.width/2, 25, { align: 'center' });
            doc.setFontSize(14);
            doc.text('Skincare Store', doc.internal.pageSize.width/2, 35, { align: 'center' });

            // Divider
            doc.setDrawColor(primaryColor);
            doc.setLineWidth(0.5);
            doc.line(20, 45, 190, 45);

            // Receipt Details
            doc.setFontSize(12);
            doc.setTextColor(0);
            doc.text('PAYMENT RECEIPT', 20, 60);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.text(`Receipt No: #{{ $order->OrderId }}`, 20, 70);
            doc.text(`Date: {{ $order->created_at?->format('F d, Y - h:i A') }}`, 20, 77);

            // Customer Details
            doc.setFont('helvetica', 'bold');
            doc.text('BILLED TO:', 20, 95);
            doc.setFont('helvetica', 'normal');
            doc.text([
                '{{ $order->CustomerName }}',
                '{{ $order->Email }}',
                '{{ $order->PhoneNo }}',
                '{{ str_replace(["\r","\n"], " ", $order->Address) }}'
            ], 20, 105);

            // Items Table Header
            doc.setFont('helvetica', 'bold');
            doc.text('PURCHASE DETAILS:', 20, 140);
            doc.setFillColor(248, 248, 248);
            doc.rect(20, 145, 170, 10, 'F');
            doc.text('Item', 25, 151);
            doc.text('Qty', 120, 151);
            doc.text('Price', 140, 151);
            doc.text('Total', 170, 151);

            // Items
            let y = 160;
            doc.setFont('helvetica', 'normal');
            @foreach($order->items as $item)
                doc.text('{{ $item->ProductName }}', 25, y);
                doc.text('{{ $item->Quantity }}', 120, y);
                doc.text('Rs {{ number_format($item->UnitPrice,2) }}', 140, y);
                doc.text('Rs {{ number_format($item->TotalPrice,2) }}', 170, y);
                y += 10;
            @endforeach

            // Total
            doc.setLineWidth(0.3);
            doc.line(20, y, 190, y);
            y += 10;
            doc.setFont('helvetica', 'bold');
            doc.text('Total Amount:', 140, y);
            doc.text('Rs {{ number_format($order->items->sum('TotalPrice'),2) }}', 170, y);

            // Footer
            y += 30;
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(9);
            doc.setTextColor(128);
            doc.text('Thank you for shopping with BeautyBreeze Skincare Store!', doc.internal.pageSize.width/2, y, { align: 'center' });
            doc.text('For any queries, please contact us at support@beautybreeze.com', doc.internal.pageSize.width/2, y+7, { align: 'center' });

            // Save the PDF
            doc.save('BeautyBreeze-Receipt-{{ $order->OrderId }}.pdf');
        });
    </script>
</div>
