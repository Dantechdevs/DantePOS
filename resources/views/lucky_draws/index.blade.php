@extends('layouts.layout')
@section('title', '| Lucky Draws')
@section('content')

@section('custom_styles')
<link rel="stylesheet" href="{{ asset('css/lucky-draws.css') }}">
@endsection

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Lucky Draws</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Lucky Draws</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Flash Messages -->
            @include('flash_messages')

            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">Manage Lucky Draws</h5>
                                    <p class="text-muted mb-0">Create and manage your lucky draw campaigns</p>
                                </div>
                                <button type="button" class="btn btn-primary" id="createLuckyDraw">
                                    <i class="fas fa-plus-circle mr-2"></i> Create New Lucky Draw
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading lucky draws...</p>
            </div>

            <!-- Lucky Draws Grid -->
            <div id="luckyDrawsGrid" class="row">
                <!-- Dynamic content will be loaded here -->
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <div class="empty-state-icon">
                    <i class="fas fa-gift fa-4x text-muted"></i>
                </div>
                <h3 class="mt-3 text-muted">No Lucky Draws Available</h3>
                <p class="text-muted">There are no lucky draws at the moment. Create your first one to get started!</p>
                <button type="button" class="btn btn-primary mt-3" id="createLuckyDrawEmpty">
                    <i class="fas fa-plus-circle mr-2"></i> Create First Lucky Draw
                </button>
            </div>
        </div>
    </section>
</div>

<!-- Create Lucky Draw Modal -->
<div class="modal fade" id="createLuckyDrawModal" tabindex="-1" role="dialog" aria-labelledby="createLuckyDrawModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createLuckyDrawModalLabel">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create New Lucky Draw
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createLuckyDrawForm" action="{{ route('lucky-draws.store') }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                                <small class="form-text text-muted">Enter a descriptive title for your lucky draw</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <small class="form-text text-muted">Only one lucky draw can be active at a time</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe your lucky draw..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_date">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="end_date">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="draw_date">Draw Date & Time</label>
                                <input type="datetime-local" class="form-control" id="draw_date" name="draw_date">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="max_entries_per_customer">Max Entries Per Customer *</label>
                        <input type="number" class="form-control" id="max_entries_per_customer" name="max_entries_per_customer" value="1" min="1" max="10" required>
                        <small class="form-text text-muted">Maximum number of entries allowed per customer (1-10)</small>
                    </div>

                    <div class="form-group">
                        <label>Prizes *</label>
                        <div id="prizesContainer">
                            <div class="prize-item row mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="prizes[0][type]" placeholder="Prize Type (e.g., First Prize)" required>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="prizes[0][name]" placeholder="Prize Name (e.g., Honda Bike)" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-prize" disabled>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success mt-2" id="addPrize">
                            <i class="fas fa-plus mr-1"></i> Add Another Prize
                        </button>
                        <small class="form-text text-muted">Add at least one prize for your lucky draw</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="createLuckyDrawBtn">
                        <i class="fas fa-save mr-1"></i> Create Lucky Draw
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Participants Modal -->
<div class="modal fade" id="participantsModal" tabindex="-1" role="dialog" aria-labelledby="participantsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="participantsModalLabel">
                    <i class="fas fa-users mr-2"></i>
                    Participants for <span id="modalDrawTitle"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="participants-summary mb-3">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="summary-card bg-light p-3 rounded">
                                <h4 id="totalParticipants" class="text-primary mb-1">0</h4>
                                <small class="text-muted">Total Participants</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card bg-light p-3 rounded">
                                <h4 id="totalEntries" class="text-success mb-1">0</h4>
                                <small class="text-muted">Total Entries</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-card bg-light p-3 rounded">
                                <h4 id="avgEntries" class="text-info mb-1">0</h4>
                                <small class="text-muted">Avg. Entries</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Each entry increases the customer's chance of winning proportionally.
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="participantsTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Entries</th>
                                <th>Win Probability</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Participants will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Spinner Modal -->
<div class="modal fade" id="spinnerModal" tabindex="-1" role="dialog" aria-labelledby="spinnerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="spinnerModalLabel">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Spinning for <span id="spinnerDrawTitle"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Customers with more entries have higher chances of winning!
                </div>

                <div class="spinner-container mb-4">
                    <div class="spinner-pointer"></div>
                    <div class="spinner-wheel" id="spinnerWheel"></div>
                </div>

                <div class="spin-stats mb-3">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Participants:</small>
                            <div id="spinParticipantsCount" class="font-weight-bold">0</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Total Entries:</small>
                            <div id="spinEntriesCount" class="font-weight-bold">0</div>
                        </div>
                    </div>
                </div>

                <div id="winnerResult" class="mt-4" style="display: none;">
                    <h4 class="text-success mb-3">🎉 Congratulations! 🎉</h4>
                    <div class="winner-card p-4 bg-light rounded mt-3 border">
                        <div class="winner-avatar mb-3">
                            <i class="fas fa-crown fa-2x text-warning"></i>
                        </div>
                        <h5 id="winnerName" class="mb-2 font-weight-bold"></h5>
                        <p id="winnerEmail" class="mb-2 text-muted"></p>
                        <p class="mb-3">
                            <span class="badge badge-info">
                                <i class="fas fa-ticket-alt mr-1"></i>
                                <span id="winnerEntries">0</span> Entries
                            </span>
                        </p>
                        <p id="winnerPrize" class="mb-2 font-weight-bold text-success"></p>
                        <span class="winner-badge">WINNER</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="startSpin">
                    <i class="fas fa-play mr-1"></i> Start Spin
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('custom-script')
<script src="{{ asset('js/scheme/lucky_draw.js') }}"></script>
@endpush
