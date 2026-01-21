@if(isset($changes['old']) && isset($changes['new']))
<div class="change-details">
    <h6 class="font-weight-bold mb-3">Changes Made:</h6>
    <div class="table-responsive">
        <table class="table table-sm table-borderless">
            <thead>
                <tr class="border-bottom">
                    <th>Field</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($changes['new'] as $field => $newValue)
                    @if(!in_array($field, ['updated_at', 'created_at']) && (!isset($changes['old'][$field]) || $changes['old'][$field] != $newValue))
                    <tr>
                        <td><strong>{{ ucfirst(str_replace('_', ' ', $field)) }}</strong></td>
                        <td class="old-value">
                            @if(isset($changes['old'][$field]))
                                {{ is_array($changes['old'][$field]) ? json_encode($changes['old'][$field]) : $changes['old'][$field] }}
                            @else
                                <em>Empty</em>
                            @endif
                        </td>
                        <td class="new-value">
                            {{ is_array($newValue) ? json_encode($newValue) : $newValue }}
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No detailed change information available.
</div>
@endif
