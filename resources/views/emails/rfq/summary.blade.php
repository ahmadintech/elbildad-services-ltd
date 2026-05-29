<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your RFQ — We're On It!</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            color: #e0f2fe;
            margin: 8px 0 0;
            font-size: 14px;
            font-weight: 500;
        }
        .content {
            padding: 40px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 32px;
            margin-bottom: 16px;
            border-bottom: 2px solid #eff6ff;
            padding-bottom: 8px;
        }
        .request-details {
            background-color: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 20px;
            border-radius: 0 12px 12px 0;
            margin-bottom: 28px;
        }
        .request-details h3 {
            margin: 0 0 8px;
            font-size: 16px;
            color: #1e293b;
            font-weight: 600;
        }
        .request-details p {
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
        }
        .supplier-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .supplier-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .supplier-location {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .supplier-meta {
            font-size: 14px;
            margin-bottom: 8px;
            color: #334155;
        }
        .supplier-meta strong {
            color: #1e293b;
        }
        .supplier-why {
            background-color: #f0fdf4;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            color: #166534;
            margin-top: 12px;
            line-height: 1.5;
        }
        .btn-container {
            text-align: center;
            margin: 40px 0 20px;
        }
        .btn {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            border-radius: 9999px;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .footer {
            background-color: #f8fafc;
            padding: 32px 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .disclaimer {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ChinaSource</h1>
            <p>Your Premier China Sourcing Partner</p>
        </div>
        <div class="content">
            <div class="greeting">Hello {{ $customerName }},</div>
            <p style="color: #4b5563; font-size: 15px; line-height: 1.6; margin-top: 0;">
                We have received your Request for Quote (RFQ) and our AI sourcing consultant has analyzed your request. We are already hard at work finding the absolute best deals for you!
            </p>

            <div class="section-title">What You Requested</div>
            <div class="request-details">
                <h3>{{ $productName }}</h3>
                <p>{{ $aiSummary }}</p>
            </div>

            <div class="section-title">Recommended Chinese Suppliers</div>
            @if(is_array($suppliers) && count($suppliers) > 0)
                @foreach($suppliers as $supplier)
                    <div class="supplier-card">
                        <div class="supplier-name">{{ $supplier['company_name'] ?? 'Supplier' }}</div>
                        <div class="supplier-location">📍 {{ $supplier['location'] ?? 'China' }}</div>
                        <div class="supplier-meta">
                            <strong>Specialization:</strong> {{ $supplier['specialization'] ?? 'N/A' }}
                        </div>
                        <div class="supplier-meta">
                            <strong>Estimated MOQ:</strong> {{ $supplier['estimated_moq'] ?? 'N/A' }}
                        </div>
                        <div class="supplier-meta">
                            <strong>Phone:</strong> {{ $supplier['phone'] ?? 'N/A' }}
                        </div>
                        <div class="supplier-meta">
                            <strong>WhatsApp:</strong> {{ $supplier['whatsapp'] ?? 'N/A' }}
                        </div>
                        <div class="supplier-meta">
                            <strong>Sourcing Hint:</strong> <span style="font-style: italic; color: #64748b;">{{ $supplier['contact_hint'] ?? 'Search on B2B platforms' }}</span>
                        </div>
                        @if(!empty($supplier['why_recommended']))
                            <div class="supplier-why">
                                <strong>Why Recommended:</strong> {{ $supplier['why_recommended'] }}
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <p style="color: #64748b; font-style: italic;">No specific supplier recommendations generated at this stage. Our sourcing agents will identify direct matches.</p>
            @endif

            <div class="btn-container">
                <a href="{{ $trackingUrl }}" class="btn">Track Your RFQ Status</a>
            </div>
        </div>
        <div class="footer">
            <p class="disclaimer">
                <strong>Disclaimer:</strong> Suppliers are AI-recommended. Our dedicated sourcing agents will personally verify each supplier and contact them on your behalf to negotiate rates and quality requirements.
            </p>
        </div>
    </div>
</body>
</html>
