<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $org->name }} - Company Profile</title>

    <style>
        @page {
            margin: 18px;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            color:#111827;
            margin:0;
            padding:0;
            font-size:11px;
        }

        .page{
            width:100%;
        }

        .cover{
            background:#0f172a;
            color:#fff;
            padding:18px 22px;
        }

        .cover h1{
            font-size:24px;
            margin:0;
            font-weight:bold;
        }

        .cover p{
            margin:5px 0 8px;
            color:#dbeafe;
        }

        .status{
            display:inline-block;
            padding:4px 9px;
            border-radius:12px;
            font-size:10px;
            font-weight:bold;
        }

        .active{
            background:#dcfce7;
            color:#166534;
        }

        .inactive{
            background:#fee2e2;
            color:#991b1b;
        }

        .section{
            padding:12px 18px;
            border-bottom:1px solid #e5e7eb;
            page-break-inside:avoid;
        }

        .section-title{
            font-size:14px;
            font-weight:bold;
            color:#0f172a;
            margin-bottom:8px;
            border-bottom:1px solid #e5e7eb;
            padding-bottom:5px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        .info-table td{
            padding:6px 5px;
            border-bottom:1px solid #f1f5f9;
            vertical-align:top;
            word-wrap:break-word;
        }

        .label{
            font-weight:bold;
            color:#64748b;
            width:20%;
        }

        .contact-table th,
        .contact-table td{
            border:1px solid #e5e7eb;
            padding:5px;
            font-size:9.5px;
            vertical-align:top;
            word-wrap:break-word;
        }

        .contact-table th{
            background:#f1f5f9;
            color:#0f172a;
            font-weight:bold;
        }

        .contact-img{
            width:36px;
            height:36px;
            object-fit:cover;
            border-radius:5px;
            border:1px solid #ddd;
        }

        .primary{
            display:inline-block;
            margin-top:3px;
            background:#dbeafe;
            color:#1e40af;
            padding:2px 5px;
            border-radius:10px;
            font-size:8px;
            font-weight:bold;
        }

        .footer{
            padding:10px;
            text-align:center;
            color:#64748b;
            font-size:9px;
        }
    </style>
</head>

<body>

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
                <td class="label">Latitude</td>
                <td>{{ $org->latitude ?? '-' }}</td>
                <td class="label">Longitude</td>
                <td>{{ $org->longitude ?? '-' }}</td>
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
                            @if($contact->image && file_exists(public_path($contact->image)))
                                <img src="{{ public_path($contact->image) }}" class="contact-img">
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