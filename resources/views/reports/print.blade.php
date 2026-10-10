<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} · {{ setting('company_name', config('app.name')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .reports-print {
            margin: 0;
            background: #fff;
            color: #1e293b;
            padding: 1rem;
        }

        .reports-print-header {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .reports-print-intro {
            min-width: 0;
        }

        .reports-print-company {
            margin: 0;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            overflow-wrap: anywhere;
        }

        .reports-print h1 {
            margin: 0.2rem 0 0;
            font-size: 1.35rem;
            font-weight: 600;
            line-height: 1.3;
            text-align: start;
        }

        .reports-print-when {
            margin: 0.35rem 0 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .reports-print-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 44px;
            box-sizing: border-box;
            border: 0;
            border-radius: 0.7rem;
            background: #4f46e5;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        .reports-print-cards {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.75rem;
        }

        .reports-print-card,
        .reports-print-empty {
            min-width: 0;
            box-sizing: border-box;
            border: 1px solid #e2e8f0;
            border-radius: 0.9rem;
            background: #fff;
        }

        .reports-print-card {
            padding: 0.9rem 1rem;
        }

        .reports-print-title {
            margin: 0;
            font-weight: 600;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .reports-print-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.55rem;
        }

        .reports-print-pill {
            display: inline-flex;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            background: #f8fafc;
            color: #475569;
            padding: 0.2rem 0.6rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .reports-print-meta {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.55rem;
            margin: 0.75rem 0 0;
        }

        .reports-print-meta dt {
            margin: 0;
            color: #94a3b8;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .reports-print-meta dd {
            margin: 0.12rem 0 0;
            color: #334155;
            font-size: 0.875rem;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .reports-print-empty {
            padding: 2.5rem 1rem;
            color: #94a3b8;
            text-align: center;
        }

        .reports-print-table {
            display: none;
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }

        .reports-print-table table {
            width: 100%;
            min-width: 640px;
            border-collapse: collapse;
            text-align: start;
            font-size: 0.875rem;
        }

        .reports-print-table th {
            padding: 0.55rem 0.65rem 0.55rem 0;
            border-bottom: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-align: start;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .reports-print-table td {
            padding: 0.55rem 0.65rem 0.55rem 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        @media (min-width: 420px) {
            .reports-print-meta {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            }

            .reports-print-meta-wide {
                grid-column: 1 / -1;
            }
        }

        @media (min-width: 640px) {
            .reports-print {
                padding: 1.5rem;
            }

            .reports-print-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }

            .reports-print-button {
                width: auto;
                min-width: 9.5rem;
            }

            .reports-print-cards {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            }

            .reports-print-empty {
                grid-column: 1 / -1;
            }

            .reports-print h1 {
                font-size: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .reports-print {
                padding: 2rem;
            }

            .reports-print-cards {
                display: none;
            }

            .reports-print-table {
                display: block;
            }

            .reports-print-table th.reports-fit,
            .reports-print-table td.reports-fit {
                width: 1%;
                padding-right: 0.5rem;
                font-size: 0.75rem;
                line-height: 1.2;
                letter-spacing: 0;
                white-space: nowrap;
                overflow-wrap: normal;
                word-break: keep-all;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .reports-print {
                padding: 0;
                background: #fff;
            }

            .reports-print-cards {
                display: none !important;
            }

            .reports-print-table {
                display: block !important;
                overflow: visible;
            }

            .reports-print-table table {
                min-width: 0;
                font-size: 10px;
            }

            .reports-print-table th,
            .reports-print-table td {
                padding: 4px 6px 4px 0;
            }

            .reports-print-table th.reports-fit,
            .reports-print-table td.reports-fit {
                white-space: nowrap;
                overflow-wrap: normal;
                word-break: keep-all;
            }
        }
    </style>
</head>
<body class="reports-print">
    <header class="reports-print-header">
        <div class="reports-print-intro">
            <p class="reports-print-company">{{ setting('company_name', config('app.name')) }}</p>
            <h1>Task report</h1>
            <p class="reports-print-when">Generated {{ $generatedAt }}</p>
        </div>
        <button type="button" onclick="window.print()" class="no-print reports-print-button">Print / Save PDF</button>
    </header>

    <div class="reports-print-cards">
        @forelse ($tasks as $task)
            <article class="reports-print-card">
                <p class="reports-print-title" dir="auto">{{ $task->title }}</p>
                <div class="reports-print-pills">
                    <span class="reports-print-pill">{{ $task->status->label() }}</span>
                    <span class="reports-print-pill">{{ $task->priority->label() }}</span>
                </div>
                <dl class="reports-print-meta">
                    <div>
                        <dt>Due</dt>
                        <dd>{{ format_date($task->due_date) ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Department</dt>
                        <dd dir="auto">{{ $task->department->name ?? '—' }}</dd>
                    </div>
                    <div class="reports-print-meta-wide">
                        <dt>Assignees</dt>
                        <dd dir="auto">{{ $task->assignedUsers->pluck('name')->join(', ') ?: '—' }}</dd>
                    </div>
                </dl>
            </article>
        @empty
            <div class="reports-print-empty">No tasks are available for this report.</div>
        @endforelse
    </div>

    <div class="reports-print-table">
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th class="reports-fit">Status</th>
                    <th>Priority</th>
                    <th class="reports-fit">Due</th>
                    <th>Department</th>
                    <th>Assignees</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $task)
                    <tr>
                        <td dir="auto">{{ $task->title }}</td>
                        <td class="reports-fit">{{ $task->status->label() }}</td>
                        <td>{{ $task->priority->label() }}</td>
                        <td class="reports-fit">{{ format_date($task->due_date) ?? '—' }}</td>
                        <td dir="auto">{{ $task->department->name ?? '—' }}</td>
                        <td dir="auto">{{ $task->assignedUsers->pluck('name')->join(', ') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="reports-print-empty">No tasks are available for this report.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <script>window.addEventListener('load', () => { if (new URLSearchParams(window.location.search).has('autoprint')) { window.print(); } });</script>
</body>
</html>
