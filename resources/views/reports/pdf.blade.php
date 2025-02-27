<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SEO Activity Report - {{ $project->name }}</title>
    <style>
        /* Reset default styles */
        * {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 2cm;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .header {
            position: relative;
            text-align: center;
            margin-bottom: 2cm;
        }

        .project-logo {
            display: block;
            margin: 0 auto;
            max-height: 240px;
            margin-bottom: 1.5cm;
        }

        .customer-logo {
            position: absolute;
            top: 0;
            right: 0;
            max-height: 40px;
        }

        h1 {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 0.5cm;
            color: #000;
            text-decoration: underline;
        }

        h2 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 1cm;
            color: #000;
        }

        h3 {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 1cm;
            margin-bottom: 0.5cm;
            color: #000;
        }

        .overview {
            margin-bottom: 1.5cm;
            padding: 0.5cm;
            background-color: #f8f8f8;
        }

        .section {
            margin-bottom: 1cm;
        }

        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 0.3cm;
            padding-bottom: 0.2cm;
            border-bottom: 1px solid #ddd;
            text-decoration: underline;
        }

        .section-image {
            margin-bottom: 0.5cm;
        }

        .log-entry {
            margin-bottom: 1cm;
            padding: 0.5cm;
            background-color: #fff;
            border: 1px solid #ddd;
        }

        .log-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 0.3cm;
        }

        .log-meta {
            font-size: 10pt;
            color: #666;
            margin-bottom: 0.3cm;
        }

        .log-content {
            font-size: 11pt;
            margin-bottom: 0.3cm;
        }

        .log-attachments {
            font-size: 10pt;
            color: #666;
            font-style: italic;
        }

        .footer {
            position: fixed;
            bottom: 1cm;
            left: 2cm;
            right: 2cm;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 0.3cm;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 1cm 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $project->logo_url }}" alt="Project Logo" class="project-logo">
        <img src="{{ $project->customer->logo_url }}" alt="Customer Logo" class="customer-logo">
        <h1>SEO Activity Report</h1>
        <h2>{{ $project->name }}</h2>
    </div>

    <div class="overview">
        <h3>Overview</h3>
        {!! nl2br(e($overview)) !!}
    </div>

    @foreach($sections as $section)
        <div class="section">
            <div class="section-title">{{ $section['title'] }}</div>
            @if(!empty($section['image_path']))
                <div class="section-image">
                    <img src="{{ $section['image_path'] }}" 
                         alt="{{ $section['title'] }} Screenshot" 
                         style="width: 100%; max-width: 100%; margin: 0.5cm 0; display: block;">
                </div>
            @endif
            <div class="section-content">
                {!! nl2br(e($section['content'])) !!}
            </div>
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <hr style="border: none; border-top: 2px solid #000; margin: 2cm 0;">
    <h3>SEO Activities Log</h3>
    @foreach($seoLogs as $log)
        <div class="log-entry">
            <div class="log-title">{{ $log->title }}</div>
            <div class="log-meta">
                Type: {{ $log->type_label }} | Date: {{ $log->created_at->format('M d, Y') }}
            </div>
            <div class="log-content">
                {!! nl2br(e($log->content)) !!}
            </div>

            @if($log->media->count() > 0)
                <div class="log-attachments">
                    <p><strong>Attachments:</strong> {{ $log->media->count() }} file(s)</p>
                </div>
            @endif
        </div>

        @if(!$loop->last)
            <hr>
        @endif
    @endforeach

    <div class="footer">
        <p>Generated by {{ \App\Models\Setting::get('app_name', config('app.name')) }} | {{ $generatedAt->format('d/M/Y') }}</p>
    </div>
</body>
</html> 