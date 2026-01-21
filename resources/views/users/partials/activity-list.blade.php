<div class="activity-feed">
    @if($activityLogs && count($activityLogs) > 0)
        @foreach($activityLogs as $log)
        <div class="activity-item mb-3 p-3 border rounded">
            <div class="d-flex justify-content-between">
                <div>
                    <i class="fas
                        @if($log->action == 'login') fa-sign-in-alt text-success
                        @elseif($log->action == 'create') fa-plus-circle text-primary
                        @elseif($log->action == 'update') fa-edit text-warning
                        @elseif($log->action == 'delete') fa-trash-alt text-danger
                        @else fa-history text-info @endif
                        mr-2"></i>
                    <strong>{{ ucfirst($log->action) }}</strong>: {{ $log->description }}
                </div>
                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
            </div>
            <div class="mt-2 text-muted small">
                <div>IP: {{ $log->ip_address }} • Browser: {{ Str::limit($log->user_agent, 50) }}</div>
                @if($log->properties && is_array($log->properties) && count($log->properties) > 0)
                <div class="mt-2">
                    <a class="text-info" data-toggle="collapse" href="#details-{{ $log->id }}" role="button">
                        <i class="fas fa-info-circle"></i> Show details
                    </a>
                    <div class="collapse mt-2" id="details-{{ $log->id }}">
                        <div class="card card-body p-3">
                            <h6 class="font-weight-bold mb-3">Change Details:</h6>
                            @if(isset($log->properties['changes']))
                                @include('users.partials.change-details', ['changes' => $log->properties['changes']])
                            @elseif(isset($log->properties['model_data']))
                                <h6 class="font-weight-bold">Created Data:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        <tbody>
                                            @foreach($log->properties['model_data'] as $key => $value)
                                                @if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                                                <tr>
                                                    <td class="text-right" style="width: 30%;"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong></td>
                                                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                @foreach($log->properties as $key => $value)
                                    @if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                                    <div class="mb-2">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                        @if(is_array($value))
                                            <pre class="bg-light p-2 small mt-1">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            <span>{{ $value }}</span>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-center mt-3">
            {{ $activityLogs->links() }}
        </div>
    @else
        <div class="text-center p-4">
            <i class="fas fa-history fa-3x text-muted mb-3"></i>
            <p class="text-muted">No activity logs found.</p>
        </div>
    @endif
</div>

<div id="noResults" class="text-center p-4">
    <i class="fas fa-search fa-3x text-muted mb-3"></i>
    <p class="text-muted">No activities found matching your search.</p>
</div>
