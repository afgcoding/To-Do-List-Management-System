@extends('layouts.app')
@php
    $pageTitle = 'Reports';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="reports-page">
    <header class="reports-header">
        <div class="reports-intro">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
            </ol>
            <h1 class="workspace-title">Reports</h1>
            <p class="reports-lead">Export visible tasks for leadership reviews. Up to 500 rows per download.</p>
        </div>
        @if ($canExport)
            <div class="reports-actions">
                <a href="{{ route('reports.print') }}" target="_blank" rel="noopener" class="reports-btn reports-btn-dark">Export PDF</a>
                <a href="{{ route('reports.csv') }}" class="reports-btn reports-btn-brand">Export Excel</a>
            </div>
        @endif
    </header>

    <div class="reports-cards">
        @forelse ($tasks as $task)
            <article class="reports-card">
                <a href="{{ route('tasks.show', $task) }}" dir="auto" class="bidi-auto reports-card-title">{{ $task->title }}</a>
                <div class="reports-pills">
                    <x-badge :tone="$task->status->tone()">{{ $task->status->label() }}</x-badge>
                    <x-badge :tone="$task->priority->tone()">{{ $task->priority->label() }}</x-badge>
                </div>
                <dl class="reports-meta">
                    <div>
                        <dt>Due</dt>
                        <dd>{{ format_date($task->due_date) ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Department</dt>
                        <dd dir="auto" class="bidi-auto">{{ $task->department->name ?? '—' }}</dd>
                    </div>
                    <div class="reports-meta-wide">
                        <dt>Assignees</dt>
                        <dd dir="auto" class="bidi-auto">{{ $task->assignedUsers->pluck('name')->join(', ') ?: '—' }}</dd>
                    </div>
                </dl>
            </article>
        @empty
            <div class="reports-empty">No tasks are available for this report.</div>
        @endforelse
    </div>

    <div class="reports-table-wrap">
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
                        <td>
                            <a href="{{ route('tasks.show', $task) }}" dir="auto" class="bidi-auto reports-task-link">{{ $task->title }}</a>
                        </td>
                        <td class="reports-fit">{{ $task->status->label() }}</td>
                        <td>{{ $task->priority->label() }}</td>
                        <td class="reports-fit">{{ format_date($task->due_date) ?? '—' }}</td>
                        <td dir="auto" class="bidi-auto">{{ $task->department->name ?? '—' }}</td>
                        <td dir="auto" class="bidi-auto">{{ $task->assignedUsers->pluck('name')->join(', ') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="reports-empty-cell">No tasks are available for this report.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .app-page-content .reports-page {
        width: 100%;
        max-width: 80rem;
        min-width: 0;
        margin-inline: auto;
    }

    .app-page-content .reports-header {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .app-page-content .reports-intro {
        min-width: 0;
    }

    .app-page-content .reports-page h1.workspace-title {
        text-align: start !important;
        overflow-wrap: anywhere;
    }

    .app-page-content .reports-lead {
        margin: 0.35rem 0 0;
        color: #64748b;
        font-size: 0.875rem;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .app-page-content .reports-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
        width: 100%;
    }

    .app-page-content a.reports-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 44px;
        box-sizing: border-box;
        padding: 0.55rem 1rem;
        border-radius: 0.7rem;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.2;
        text-align: center;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .app-page-content a.reports-btn-dark {
        background: #1e293b;
        color: #fff;
    }

    .app-page-content a.reports-btn-dark:hover {
        background: #0f172a;
        color: #fff;
    }

    .app-page-content a.reports-btn-brand {
        background: #4f46e5;
        color: #fff;
    }

    .app-page-content a.reports-btn-brand:hover {
        background: #4338ca;
        color: #fff;
    }

    .app-page-content .reports-cards {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.75rem;
    }

    .app-page-content .reports-card,
    .app-page-content .reports-empty {
        min-width: 0;
        box-sizing: border-box;
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .app-page-content .reports-card {
        padding: 1rem;
    }

    .app-page-content a.reports-card-title,
    .app-page-content a.reports-task-link {
        display: inline !important;
        color: #1e293b;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
        text-decoration: none !important;
    }

    .app-page-content a.reports-card-title:hover,
    .app-page-content a.reports-task-link:hover {
        color: #4f46e5;
    }

    .app-page-content .reports-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.7rem;
    }

    .app-page-content .reports-meta {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.65rem;
        margin: 0.85rem 0 0;
    }

    .app-page-content .reports-meta dt {
        margin: 0;
        color: #94a3b8;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .app-page-content .reports-meta dd {
        margin: 0.15rem 0 0;
        color: #334155;
        font-size: 0.875rem;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .app-page-content .reports-empty,
    .app-page-content .reports-empty-cell {
        padding: 2.5rem 1rem;
        color: #94a3b8;
        text-align: center !important;
    }

    .app-page-content .reports-table-wrap {
        display: none;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .app-page-content .reports-table-wrap table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
        text-align: start;
        font-size: 0.875rem;
    }

    .app-page-content .reports-table-wrap thead {
        background: #f8fafc;
    }

    .app-page-content .reports-table-wrap th {
        color: #64748b;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-align: start;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .app-page-content .reports-table-wrap td {
        color: #475569;
        overflow-wrap: anywhere;
        vertical-align: top;
    }

    .app-page-content .reports-table-wrap tbody tr + tr td {
        border-top: 1px solid #f1f5f9;
    }

    @media (min-width: 420px) {
        .app-page-content .reports-actions {
            grid-template-columns: 1fr 1fr;
        }

        .app-page-content .reports-meta {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .app-page-content .reports-meta-wide {
            grid-column: 1 / -1;
        }
    }

    @media (min-width: 640px) {
        .app-page-content .reports-cards {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .app-page-content .reports-empty {
            grid-column: 1 / -1;
        }
    }

    @media (min-width: 768px) {
        .app-page-content .reports-header {
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
        }

        .app-page-content .reports-actions {
            width: auto;
            grid-template-columns: auto auto;
        }

        .app-page-content a.reports-btn {
            width: auto;
            min-width: 8.5rem;
        }
    }

    @media (min-width: 1200px) {
        .app-page-content .reports-cards {
            display: none;
        }

        .app-page-content .reports-table-wrap {
            display: block;
        }

        .app-page-content .reports-table-wrap th.reports-fit,
        .app-page-content .reports-table-wrap td.reports-fit {
            width: 1%;
            padding-inline: 0.65rem !important;
            font-size: 0.75rem;
            line-height: 1.2;
            letter-spacing: 0;
            white-space: nowrap;
            overflow-wrap: normal;
            word-break: keep-all;
        }
    }
</style>
@endsection
