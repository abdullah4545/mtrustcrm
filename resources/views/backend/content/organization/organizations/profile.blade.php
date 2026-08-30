<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $org->name }} - Company Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        body{
            background:#eef2f7;
            font-family: Arial, sans-serif;
            color:#111827;
            margin:0;
            padding:0;
        }

        .top-actions{
            width:210mm;
            margin:15px auto;
            text-align:right;
        }

        .page{
            width:210mm;
            min-height:297mm;
            margin:0 auto 20px auto;
            background:#fff;
            box-shadow:0 10px 30px rgba(0,0,0,.10);
            overflow:hidden;
        }

        .cover{
            background:linear-gradient(135deg,#0f172a,#1d4ed8);
            color:#fff;
            padding:24px 30px;
        }

        .cover h1{
            font-size:30px;
            font-weight:800;
            margin:0;
        }

        .cover p{
            margin:6px 0 12px;
            color:#dbeafe;
        }

        .section{
            padding:18px 30px;
            border-bottom:1px solid #e5e7eb;
            page-break-inside:avoid;
        }

        .section-title{
            font-size:18px;
            font-weight:800;
            color:#0f172a;
            margin-bottom:12px;
        }

        .info-table{
            width:100%;
            table-layout:fixed;
        }

        .info-table td{
            padding:7px 6px;
            border-bottom:1px solid #f1f5f9;
            vertical-align:top;
            word-break:break-word;
            font-size:13px;
        }

        .label{
            font-weight:700;
            color:#64748b;
            width:20%;
        }

        .status{
            display:inline-block;
            padding:5px 12px;
            border-radius:30px;
            font-weight:700;
            font-size:12px;
        }

        .active{
            background:#dcfce7;
            color:#166534;
        }

        .inactive{
            background:#fee2e2;
            color:#991b1b;
        }

        .contact-table{
            width:100%;
            table-layout:fixed;
            border-collapse:collapse;
        }

        .contact-table th,
        .contact-table td{
            border:1px solid #e5e7eb;
            padding:6px;
            font-size:11px;
            vertical-align:top;
            word-break:break-word;
        }

        .contact-table th{
            background:#f8fafc;
            font-weight:700;
        }

        .contact-img{
            width:45px;
            height:45px;
            object-fit:cover;
            border-radius:6px;
            border:1px solid #ddd;
        }

        .primary{
            display:inline-block;
            margin-top:4px;
            background:#dbeafe;
            color:#1e40af;
            padding:2px 6px;
            border-radius:20px;
            font-size:10px;
            font-weight:700;
        }

        .footer{
            padding:14px;
            text-align:center;
            color:#64748b;
            font-size:12px;
        }

        @media print{
            html, body{
                width:210mm;
                min-height:297mm;
                background:#fff;
            }

            .top-actions{
                display:none !important;
            }

            .page{
                width:100%;
                min-height:auto;
                margin:0;
                box-shadow:none;
                border-radius:0;
            }

            .section{
                page-break-inside:avoid;
                break-inside:avoid;
            }

            .cover{
                -webkit-print-color-adjust:exact;
                print-color-adjust:exact;
            }
        }
    </style>
</head>

<body>

<div class="top-actions">
    <button onclick="window.print()" class="btn btn-primary">
        Print
    </button>

    <a href="{{ route('org.company.profile.pdf.view', $org->id) }}" target="_blank" class="btn btn-dark">
        PDF View
    </a>

    <a href="{{ route('org.company.profile.download', $org->id) }}" class="btn btn-danger">
        Download PDF
    </a>
</div>

<div class="page">

    <div class="cover">
        <h1>{{ $org->name }}</h1>
        <p>Professional Company Profile</p>

        <span class="status {{ $org->status == 'active' ? 'active' : 'inactive' }}">
            {{ ucfirst($org->status) }}
        </span>
    </div>

    <div class="section">
        <div class="section-title">Company Overview</div>

        <table class="info-table">
            <tr>
                <td class="label">Company Name</td>
                <td>{{ $org->name ?? '-' }}</td>
                <td class="label">Category</td>
                <td>{{ $org->category->name ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Type</td>
                <td>{{ $org->type->name ?? '-' }}</td>
                <td class="label">Status</td>
                <td>{{ ucfirst($org->status) }}</td>
            </tr>

            <tr>
                <td class="label">Primary Phone</td>
                <td>{{ $org->phone_primary ?? '-' }}</td>
                <td class="label">Secondary Phone</td>
                <td>{{ $org->phone_secondary ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Email</td>
                <td>{{ $org->email ?? '-' }}</td>
                <td class="label">Website</td>
                <td>{{ $org->website ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Address</td>
                <td colspan="3">{{ $org->address ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Location Information</div>

        <table class="info-table">
            <tr>
                <td class="label">Division</td>
                <td>{{ $org->division->name ?? '-' }}</td>
                <td class="label">District</td>
                <td>{{ $org->district->name ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Upazila</td>
                <td>{{ $org->upazila->name ?? '-' }}</td>
                <td class="label">Union</td>
                <td>{{ $org->union->name ?? '-' }}</td>
            </tr>

            <tr>
                <td class="label">Map Location</td>
                <td colspan="3">
                    @if($org->map_location_link)
                        <a href="{{ $org->map_location_link }}" target="_blank" rel="noopener">View Location</a>
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">About Company</div>
        <p>{{ $org->about_us ?? '-' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Key Contact Persons</div>

        <table class="contact-table">
            <thead>
                <tr>
                    <th width="7%">Photo</th>
                    <th width="16%">Name</th>
                    <th width="13%">Department</th>
                    <th width="13%">Designation</th>
                    <th width="13%">Phone</th>
                    <th width="16%">Email</th>
                    <th width="13%">Address</th>
                    <th width="9%">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($org->contacts as $contact)
                    <tr>
                        <td>
                            @if($contact->image)
                                <img src="{{ asset($contact->image) }}" class="contact-img">
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            <strong>{{ $contact->title }} {{ $contact->name ?? '-' }}</strong>

                            @if($contact->is_primary)
                                <br><span class="primary">K.O.L</span>
                            @endif
                        </td>

                        <td>{{ $contact->department->title ?? '-' }}</td>
                        <td>{{ $contact->designation->title ?? '-' }}</td>

                        <td>
                            {{ $contact->phone ?? '-' }}
                            @if($contact->phone_two)
                                <br>{{ $contact->phone_two }}
                            @endif
                        </td>

                        <td>{{ $contact->email ?? '-' }}</td>
                        <td>{{ $contact->address ?? '-' }}</td>

                        <td>{{ ucfirst($contact->status) }}</td>
                    </tr>

                    @if($contact->additional_info)
                        <tr>
                            <td colspan="8">
                                <strong>Additional Info:</strong> {{ $contact->additional_info }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;">No contacts found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Notes</div>
        <p>{{ $org->notes ?? '-' }}</p>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M, Y h:i A') }} |
        {{ ($business?->business_name ?? 'Medi Trust Solution') }}
    </div>

</div>

</body>
</html>