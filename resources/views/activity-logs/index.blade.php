@extends('layouts.app')
@php
    $pageTitle = 'Activity Logs';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="logs-page">
    <header class="logs-header">
        <div class="logs-intro">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
            </ol>
            <h1 class="workspace-title">Activity Logs</h1>
            <p class="logs-lead">Audit trail of task changes, comments, attachments, and assignments.</p>
        </div>
        <div class="logs-actions">
            <x-back-link class="logs-back" :href="route('tasks.index')">Back to tasks</x-back-link>
        </div>
    </header>

    <div class="logs-cards">
        @forelse ($activityLogs as $log)
            <article class="logs-card">
                <div class="logs-person">
                    <x-user-avatar :user="$log->user" :name="$log->user->name ?? 'System'" size="sm" />
                    <span dir="auto" class="bidi-auto logs-person-name">{{ $log->user->name ?? 'System' }}</span>
                </div>
                <p dir="auto" class="bidi-auto logs-desc">{{ $log->description }}</p>
                <p class="logs-action">{{ str_replace('_', ' ', $log->action) }}</p>
                <div class="logs-task-row">
                    @if ($log->task)
                        <a href="{{ route('tasks.show', $log->task) }}" dir="auto" class="bidi-auto logs-task">{{ $log->task->title }}</a>
                    @else
                        <span class="logs-muted">—</span>
                    @endif
                </div>
                <p class="logs-when">
                    @if ($log->created_at)
                        <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ format_date($log->created_at) }}">
                            {{ $log->created_at->diffForHumans() }} · {{ format_date($log->created_at) }}
                        </time>
                    @else
                        —
                    @endif
                </p>
            </article>
        @empty
            <div class="logs-empty">No activity recorded yet.</div>
        @endforelse
    </div>

    <div class="logs-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Actor</th>
                    <th>Activity</th>
                    <th>Task</th>
                    <th class="logs-fit">When</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($activityLogs as $log)
                    <tr>
                        <td>
                            <div class="logs-person">
                                <x-user-avatar :user="$log->user" :name="$log->user->name ?? 'System'" size="sm" />
                                <span dir="auto" class="bidi-auto logs-person-name">{{ $log->user->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td>
                            <p dir="auto" class="bidi-auto logs-desc">{{ $log->description }}</p>
                            <p class="logs-action">{{ str_replace('_', ' ', $log->action) }}</p>
                        </td>
                        <td>
                            @if ($log->task)
                                <a href="{{ route('tasks.show', $log->task) }}" dir="auto" class="bidi-auto logs-task">{{ $log->task->title }}</a>
                            @else
                                <span class="logs-muted">—</span>
                            @endif
                        </td>
                        <td class="logs-fit">
                            @if ($log->created_at)
                                <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ format_date($log->created_at) }}">
                                    {{ $log->created_at->diffForHumans() }} · {{ format_date($log->created_at) }}
                                </time>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="logs-empty-cell">No activity recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="logs-pager">{{ $activityLogs->links() }}</div>
</div>

<style>
    .app-page-content .logs-page {
        width: 100%;
        max-width: 80rem;
        min-width: 0;
        margin-inline: auto;
    }

    .app-page-content .logs-header {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .app-page-content .logs-intro {
        min-width: 0;
    }

    .app-page-content .logs-page h1.workspace-title {
        text-align: start !important;
        overflow-wrap: anywhere;
    }

    .app-page-content .logs-lead {
        display: block !important;
        margin: 0.35rem 0 0;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .app-page-content .logs-actions {
        width: 100%;
        min-width: 0;
    }

    .app-page-content a.logs-back {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 100%;
        box-sizing: border-box;
        min-height: 44px;
    }

    .app-page-content .logs-cards {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.75rem;
    }

    .app-page-content .logs-card,
    .app-page-content .logs-empty {
        min-width: 0;
        box-sizing: border-box;
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }

    .app-page-content .logs-card {
        padding: 1rem;
    }

    .app-page-content .logs-person {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .app-page-content .logs-person-name {
        min-width: 0;
        color: #1e293b;
        font-weight: 600;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .app-page-content .logs-page p.logs-desc,
    .app-page-content .logs-page p.logs-action,
    .app-page-content .logs-page p.logs-when {
        display: block !important;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .app-page-content .logs-desc {
        margin-top: 0.55rem !important;
        color: #334155;
        font-size: 0.875rem;
        line-height: 1.45;
    }

    .app-page-content .logs-action {
        margin-top: 0.2rem !important;
        color: #94a3b8;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        line-height: 1.3;
        text-transform: uppercase;
    }

    .app-page-content .logs-task-row {
        min-width: 0;
        margin-top: 0.55rem;
    }

    .app-page-content a.logs-task {
        display: inline !important;
        color: #4f46e5;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
        text-decoration: none !important;
    }

    .app-page-content a.logs-task:hover {
        color: #3730a3;
    }

    .app-page-content .logs-muted,
    .app-page-content .logs-when {
        color: #64748b;
        font-size: 0.75rem;
        line-height: 1.4;
    }

    .app-page-content .logs-when {
        margin-top: 0.55rem !important;
    }

    .app-page-content .logs-empty,
    .app-page-content .logs-empty-cell {
        padding: 2.5rem 1rem;
        color: #94a3b8;
        text-align: center !important;
    }

    .app-page-content .logs-table-wrap {
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

    .app-page-content .logs-table-wrap table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
        text-align: start;
        font-size: 0.875rem;
    }

    .app-page-content .logs-table-wrap thead {
        background: #f8fafc;
    }

    .app-page-content .logs-table-wrap th {
        color: #64748b;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-align: start;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .app-page-content .logs-table-wrap td {
        color: #475569;
        vertical-align: top;
    }

    .app-page-content .logs-table-wrap tbody tr + tr td {
        border-top: 1px solid #f1f5f9;
    }

    .app-page-content .logs-table-wrap tbody tr:hover {
        background: rgb(248 250 252 / 0.7);
    }

    .app-page-content .logs-table-wrap .logs-desc,
    .app-page-content .logs-table-wrap .logs-when {
        margin-top: 0 !important;
    }

    .app-page-content .logs-pager {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin-top: 1rem;
        overflow-x: auto;
    }

    @media (min-width: 640px) {
        .app-page-content .logs-cards {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .app-page-content .logs-empty {
            grid-column: 1 / -1;
        }

        .app-page-content a.logs-back {
            width: auto;
        }
    }

    @media (min-width: 768px) {
        .app-page-content .logs-header {
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
        }

        .app-page-content .logs-actions {
            width: auto;
        }
    }

    @media (min-width: 1200px) {
        .app-page-content .logs-cards {
            display: none;
        }

        .app-page-content .logs-table-wrap {
            display: block;
        }

        .app-page-content .logs-table-wrap th.logs-fit,
        .app-page-content .logs-table-wrap td.logs-fit {
            width: 1%;
            font-size: 0.75rem;
            line-height: 1.3;
            white-space: nowrap;
            overflow-wrap: normal;
            word-break: keep-all;
        }
    }
</style>
@endsection
