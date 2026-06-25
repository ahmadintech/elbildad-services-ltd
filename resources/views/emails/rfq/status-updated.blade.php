<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFQ Status Update</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f1f5f9;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            padding: 40px 16px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        /* HEADER */
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            padding: 36px 40px 32px;
            text-align: center;
            position: relative;
        }
        .header-logo {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }
        .header-tagline {
            font-size: 12px;
            color: #bfdbfe;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        /* STATUS BADGE */
        .status-banner {
            padding: 24px 40px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .status-label {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 9999px;
            font-size: 15px;
            font-weight: 700;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        /* Status color variants */
        .status-quoted         { background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; }
        .status-quoted .status-dot         { background: #2563eb; }
        .status-awaiting_payment { background: #fffbeb; color: #92400e; border: 1.5px solid #fde68a; }
        .status-awaiting_payment .status-dot { background: #f59e0b; }
        .status-payment_received { background: #f0fdf4; color: #166534; border: 1.5px solid #bbf7d0; }
        .status-payment_received .status-dot { background: #16a34a; }
        .status-sourcing        { background: #faf5ff; color: #6b21a8; border: 1.5px solid #e9d5ff; }
        .status-sourcing .status-dot        { background: #9333ea; }
        .status-purchased       { background: #eff6ff; color: #1e40af; border: 1.5px solid #bfdbfe; }
        .status-purchased .status-dot       { background: #3b82f6; }
        .status-shipped         { background: #fff7ed; color: #9a3412; border: 1.5px solid #fed7aa; }
        .status-shipped .status-dot         { background: #ea580c; }
        .status-completed       { background: #f0fdf4; color: #14532d; border: 1.5px solid #86efac; }
        .status-completed .status-dot       { background: #22c55e; }
        .status-not_found       { background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; }
        .status-not_found .status-dot       { background: #ef4444; }
        /* CONTENT */
        .content {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 17px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 14px;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        /* RFQ DETAILS CARD */
        .rfq-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 28px;
        }
        .rfq-card-header {
            background: linear-gradient(90deg, #1e3a8a, #2563eb);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .rfq-card-header h3 {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .rfq-card-body {
            padding: 20px;
        }
        .rfq-product-name {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e2e8f0;
        }
        .rfq-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .rfq-item {}
        .rfq-item-label {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 3px;
        }
        .rfq-item-value {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }
        .rfq-specs {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
        }
        .rfq-specs-label {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }
        .rfq-specs-value {
            font-size: 13px;
            color: #334155;
            line-height: 1.65;
            white-space: pre-wrap;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
        }
        /* STATUS-SPECIFIC MESSAGE */
        .status-message {
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 28px;
            font-size: 14px;
            line-height: 1.65;
        }
        .status-message-quoted         { background: #eff6ff; border-left: 4px solid #2563eb; color: #1e40af; }
        .status-message-awaiting_payment { background: #fffbeb; border-left: 4px solid #f59e0b; color: #92400e; }
        .status-message-payment_received { background: #f0fdf4; border-left: 4px solid #16a34a; color: #166534; }
        .status-message-sourcing        { background: #faf5ff; border-left: 4px solid #9333ea; color: #6b21a8; }
        .status-message-purchased       { background: #eff6ff; border-left: 4px solid #3b82f6; color: #1e40af; }
        .status-message-shipped         { background: #fff7ed; border-left: 4px solid #ea580c; color: #9a3412; }
        .status-message-completed       { background: #f0fdf4; border-left: 4px solid #22c55e; color: #14532d; }
        .status-message-not_found       { background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; }
        .status-message p { margin: 0; }
        /* CTA BUTTON */
        .btn-container {
            text-align: center;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        /* TRACKING TOKEN */
        .tracking-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 14px 20px;
            text-align: center;
            margin-bottom: 28px;
        }
        .tracking-box p {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .tracking-token {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a8a;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        /* FOOTER */
        .footer {
            background: #f8fafc;
            padding: 28px 40px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.7;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        @media (max-width: 480px) {
            .content, .header, .status-banner, .footer { padding-left: 20px; padding-right: 20px; }
            .rfq-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">Elbildad Services</div>
            <div class="header-tagline">Your Trusted China Sourcing Partner</div>
        </div>

        <!-- Status Banner -->
        @php
            $statusKey = $newStatus->value;
            $statusLabel = match($newStatus) {
                \App\Enums\RfqStatusEnum::QUOTED           => 'Quoted',
                \App\Enums\RfqStatusEnum::AWAITING_PAYMENT => 'Awaiting Payment',
                \App\Enums\RfqStatusEnum::PAYMENT_RECEIVED => 'Payment Received',
                \App\Enums\RfqStatusEnum::SOURCING         => 'Sourcing',
                \App\Enums\RfqStatusEnum::PURCHASED        => 'Purchased',
                \App\Enums\RfqStatusEnum::SHIPPED          => 'Shipped',
                \App\Enums\RfqStatusEnum::COMPLETED        => 'Completed',
                \App\Enums\RfqStatusEnum::NOT_FOUND        => 'Not Found',
                default                                    => ucfirst($newStatus->value),
            };
            $statusMessage = match($newStatus) {
                \App\Enums\RfqStatusEnum::QUOTED           => 'Great news! We have reviewed your request and prepared a quote for you. Please review the details and proceed with payment to move your order forward.',
                \App\Enums\RfqStatusEnum::AWAITING_PAYMENT => 'Your RFQ has been quoted and is now awaiting payment. Please complete your payment at the earliest to avoid any delays in processing your order.',
                \App\Enums\RfqStatusEnum::PAYMENT_RECEIVED => 'We have successfully received your payment. Your order is now being actively processed by our sourcing team. We will keep you updated on every step.',
                \App\Enums\RfqStatusEnum::SOURCING         => 'Our sourcing team is actively working on your request, contacting suppliers and negotiating the best prices and quality for you.',
                \App\Enums\RfqStatusEnum::PURCHASED        => 'Your items have been successfully purchased from the supplier. We are now preparing your order for shipment.',
                \App\Enums\RfqStatusEnum::SHIPPED          => 'Exciting news! Your order is on its way. Our logistics team is handling the shipping process and you will receive further tracking details soon.',
                \App\Enums\RfqStatusEnum::COMPLETED        => 'Your order has been successfully completed! Thank you for trusting Elbildad Services. We look forward to serving you again.',
                \App\Enums\RfqStatusEnum::NOT_FOUND        => 'Unfortunately, we were unable to source the exact items you requested at this time. Please contact our team to discuss alternative options.',
                default                                    => 'Your RFQ status has been updated. Please log in to your dashboard for more details.',
            };
        @endphp

        <div class="status-banner">
            <div class="status-label">Current Status</div><br>
            <span class="status-badge status-{{ $statusKey }}">
                <span class="status-dot"></span>
                {{ $statusLabel }}
            </span>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="greeting">Hello {{ $customerName }},</div>
            <p class="intro-text">
                We have an update on your Request for Quote. Here are the current details of your request:
            </p>

            <!-- RFQ Details Card -->
            <div class="rfq-card">
                <div class="rfq-card-header">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#ffffff" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3>Request for Quote — Item Details</h3>
                </div>
                <div class="rfq-card-body">
                    <div class="rfq-product-name">{{ $rfq->product_name }}</div>

                    <div class="rfq-grid">
                        <div class="rfq-item">
                            <div class="rfq-item-label">Tracking ID</div>
                            <div class="rfq-item-value" style="font-family: monospace; color: #1e3a8a;">ELB-{{ strtoupper(substr($rfq->tracking_token, 0, 8)) }}</div>
                        </div>
                        <div class="rfq-item">
                            <div class="rfq-item-label">Quantity</div>
                            <div class="rfq-item-value">{{ ucfirst($rfq->quantity?->value ?? $rfq->quantity ?? 'N/A') }}</div>
                        </div>
                        <div class="rfq-item">
                            <div class="rfq-item-label">Delivery Method</div>
                            <div class="rfq-item-value">{{ ucfirst(str_replace('_', ' ', $rfq->delivery_method?->value ?? $rfq->delivery_method ?? 'N/A')) }}</div>
                        </div>
                        @if($rfq->target_price)
                        <div class="rfq-item">
                            <div class="rfq-item-label">Target Price</div>
                            <div class="rfq-item-value">{{ $rfq->target_price }}</div>
                        </div>
                        @endif
                        @if($rfq->location)
                        <div class="rfq-item">
                            <div class="rfq-item-label">Delivery Location</div>
                            <div class="rfq-item-value">{{ $rfq->location }}</div>
                        </div>
                        @endif
                        @if($rfq->company_name)
                        <div class="rfq-item">
                            <div class="rfq-item-label">Company</div>
                            <div class="rfq-item-value">{{ $rfq->company_name }}</div>
                        </div>
                        @endif
                        <div class="rfq-item">
                            <div class="rfq-item-label">Date Submitted</div>
                            <div class="rfq-item-value">{{ $rfq->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="rfq-item">
                            <div class="rfq-item-label">Status Updated</div>
                            <div class="rfq-item-value">{{ now()->format('M d, Y') }}</div>
                        </div>
                    </div>

                    @if($rfq->specifications)
                    <div class="rfq-specs">
                        <div class="rfq-specs-label">Specifications</div>
                        <div class="rfq-specs-value">{{ $rfq->specifications }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Status-Specific Message -->
            <div class="status-message status-message-{{ $statusKey }}">
                <p>{{ $statusMessage }}</p>
            </div>

            <!-- Tracking Token -->
            <div class="tracking-box">
                <p>Your Tracking Token</p>
                <div class="tracking-token">ELB-{{ strtoupper(substr($rfq->tracking_token, 0, 8)) }}</div>
            </div>

            <!-- CTA -->
            <div class="btn-container">
                <a href="{{ $trackingUrl }}" class="btn">Track Your RFQ</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                Questions? Reply to this email or reach us at
                <a href="mailto:support@elbildadservices.ng">support@elbildadservices.ng</a>
            </p>
            <p style="margin-top: 10px;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                Your trusted and reliable sourcing agency.
            </p>
        </div>
    </div>
</div>
</body>
</html>
