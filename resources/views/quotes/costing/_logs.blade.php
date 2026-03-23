<style>
    .costing-log-shell {
        display: grid;
        gap: 14px;
    }

    .costing-log-group {
        border: 1px solid #dbe7f5;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }

    .costing-log-group-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
        border-bottom: 1px solid #dbe7f5;
    }

    .costing-log-group-title {
        font-size: 0.92rem;
        font-weight: 800;
        color: #111827;
    }

    .costing-log-group-meta {
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        white-space: nowrap;
    }

    .costing-log-group-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-size: 0.9rem;
        font-weight: 800;
        cursor: pointer;
    }

    .costing-log-group-toggle:hover {
        background: #f8fbff;
    }

    .costing-log-sections {
        display: grid;
        gap: 12px;
        padding: 12px;
        background: #f8fbff;
    }

    .costing-log-section {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .costing-log-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: #f8fbff;
        border-bottom: 1px solid #e2e8f0;
    }

    .costing-log-section-title {
        font-size: 0.82rem;
        font-weight: 800;
        color: #1e293b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .costing-log-section-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 30px;
        border: 1px solid #dbe7f5;
        border-radius: 7px;
        background: #fff;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
    }

    .costing-log-section-toggle:hover {
        background: #f8fbff;
    }

    .costing-log-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        padding: 4px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border: 1px solid transparent;
    }

    .costing-log-badge-created {
        background: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .costing-log-badge-updated {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .costing-log-badge-deleted {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .costing-log-table {
        width: 100%;
        border-collapse: collapse;
    }

    .costing-log-table th,
    .costing-log-table td {
        border-bottom: 1px solid #edf2f7;
        padding: 10px 12px;
        font-size: 0.88rem;
        color: #0f172a;
        text-align: left;
        vertical-align: top;
    }

    .costing-log-table thead th {
        background: #fcfdff;
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
    }

    .costing-log-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .costing-log-field {
        font-family: Consolas, monospace;
        font-size: 0.82rem;
        color: #1d4ed8;
        white-space: nowrap;
    }

    .costing-log-old,
    .costing-log-new {
        font-family: Consolas, monospace;
        font-size: 0.82rem;
    }

    .costing-log-old {
        background: #fff1f2;
        color: #9f1239;
    }

    .costing-log-new {
        background: #ecfdf5;
        color: #166534;
    }

    .costing-log-pill {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        white-space: pre-wrap;
    }

    .costing-log-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #fff;
        padding: 36px 16px;
        text-align: center;
        color: #64748b;
        font-size: 0.92rem;
    }
</style>

@php
    $groupedLogs = $logs
        ->groupBy(fn ($log) => optional($log->created_at)->format('Y-m-d H:i:s') . '|' . ($log->user_id ?? '0'))
        ->map(function ($group) {
            return [
                'user' => $group->first()->user?->name ?? ('User #' . ($group->first()->user_id ?? 'N/A')),
                'date' => optional($group->first()->created_at)->format('Y-m-d H:i:s'),
                'sections' => $group->groupBy(function ($log) {
                    if ($log->costing_operation_id && $log->costingOperation) {
                        return 'Operation: ' . ($log->costingOperation->name_operation ?: ('#' . $log->costing_operation_id));
                    }

                    return 'Header';
                }),
            ];
        })
        ->values();
@endphp

@if($groupedLogs->isEmpty())
    <div class="costing-log-empty">No history available.</div>
@else
    <div class="costing-log-shell">
        @foreach($groupedLogs as $index => $group)
            <div class="costing-log-group">
                <div class="costing-log-group-head">
                    <div>
                        <div class="costing-log-group-title">{{ $group['user'] }}</div>
                        <div class="costing-log-group-meta">{{ $group['date'] }}</div>
                    </div>
                    <button
                        type="button"
                        class="costing-log-group-toggle"
                        data-toggle="collapse"
                        data-target="#costingLogGroup{{ $index }}"
                        aria-expanded="false"
                        aria-controls="costingLogGroup{{ $index }}"
                    >
                        +
                    </button>
                </div>

                <div id="costingLogGroup{{ $index }}" class="collapse">
                <div class="costing-log-sections">
                    @foreach($group['sections'] as $sectionIndex => $sectionLogs)
                        <div class="costing-log-section">
                            <div class="costing-log-section-head">
                                <div class="costing-log-section-title">{{ $sectionIndex }}</div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span class="costing-log-badge costing-log-badge-{{ strtolower($sectionLogs->first()->action) }}">
                                        {{ strtoupper($sectionLogs->first()->action) }}
                                    </span>
                                    <button
                                        type="button"
                                        class="costing-log-section-toggle"
                                        data-toggle="collapse"
                                        data-target="#costingLogSection{{ $index }}{{ \Illuminate\Support\Str::slug($sectionIndex) }}"
                                        aria-expanded="false"
                                        aria-controls="costingLogSection{{ $index }}{{ \Illuminate\Support\Str::slug($sectionIndex) }}"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <div id="costingLogSection{{ $index }}{{ \Illuminate\Support\Str::slug($sectionIndex) }}" class="collapse">
                                <table class="costing-log-table">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Old</th>
                                            <th>New</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sectionLogs as $log)
                                            <tr>
                                                <td class="costing-log-field">{{ $log->field_changed ?: '---' }}</td>
                                                <td class="costing-log-old">
                                                    <span class="costing-log-pill">{{ $log->old_value ?: '---' }}</span>
                                                </td>
                                                <td class="costing-log-new">
                                                    <span class="costing-log-pill">{{ $log->new_value ?: '---' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<script>
    $(function () {
        $('[id^="costingLogGroup"]').on('shown.bs.collapse hidden.bs.collapse', function () {
            const $toggle = $(this).closest('.costing-log-group').find('.costing-log-group-toggle');
            $toggle.text($(this).hasClass('show') ? '-' : '+');
        });

        $('[id^="costingLogSection"]').on('shown.bs.collapse hidden.bs.collapse', function () {
            const $toggle = $(this).closest('.costing-log-section').find('.costing-log-section-toggle');
            $toggle.text($(this).hasClass('show') ? '-' : '+');
        });
    });
</script>
