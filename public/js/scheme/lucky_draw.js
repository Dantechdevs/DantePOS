class LuckyDrawManager {
    constructor() {
        this.baseUrl = '/lucky-draws';
        this.currentDrawId = null;
        this.currentPrizeType = null;
        this.init();
    }

    init() {
        this.loadLuckyDraws();
        this.bindEvents();
        this.initCreateForm();
    }

    bindEvents() {
        // Delegate events for dynamic content
        $(document).on('click', '.view-participants', (e) => this.viewParticipants(e));
        $(document).on('click', '.spin-prize-draw', (e) => this.spinPrizeDraw(e));
        $(document).on('click', '.toggle-status', (e) => this.toggleStatus(e));
        $(document).on('click', '.deactivateLuckyDraw', (e) => this.deactivateLuckyDraw(e));
        $('#startSpin').on('click', () => this.startSpin());
        $('#createLuckyDraw').on('click', () => this.openCreateModal());
        $('#createLuckyDrawEmpty').on('click', () => this.openCreateModal());

        // Modal hidden events
        $('#participantsModal').on('hidden.bs.modal', () => this.clearParticipantsModal());
        $('#spinnerModal').on('hidden.bs.modal', () => this.clearSpinnerModal());
        $('#createLuckyDrawModal').on('hidden.bs.modal', () => this.clearCreateModal());
    }

    initCreateForm() {
        let prizeCount = 1;

        // Add prize field
        $('#addPrize').on('click', function () {
            prizeCount++;
            const newPrize = `
                <div class="prize-item row mb-2">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="prizes[${prizeCount}][type]"
                               placeholder="Prize Type (e.g., Second Prize)" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="prizes[${prizeCount}][name]"
                               placeholder="Prize Name (e.g., LED TV)" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-prize">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#prizesContainer').append(newPrize);
        });

        // Remove prize field
        $(document).on('click', '.remove-prize', function () {
            if ($('.prize-item').length > 1) {
                $(this).closest('.prize-item').remove();
                prizeCount--;
            }
        });
    }

    openCreateModal() {
        $('#createLuckyDrawModal').modal('show');
    }

    async createLuckyDraw() {
        const form = $('#createLuckyDrawForm');
        const submitBtn = $('#createLuckyDrawBtn');

        try {
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');

            const formData = new FormData(form[0]);

            // Convert prizes to proper format
            const prizes = [];
            $('.prize-item').each(function (index) {
                const type = $(this).find('input[name*="[type]"]').val();
                const name = $(this).find('input[name*="[name]"]').val();
                if (type && name) {
                    prizes.push({ type, name });
                }
            });

            // Add prizes to form data
            formData.delete('prizes');
            formData.append('prizes', JSON.stringify(prizes));

            const response = await $.ajax({
                url: `${this.baseUrl}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            });

            this.showSuccess('Lucky draw created successfully!');
            $('#createLuckyDrawModal').modal('hide');
            this.loadLuckyDraws(); // Refresh the list

        } catch (error) {
            console.error('Error creating lucky draw:', error);
            let errorMessage = 'Failed to create lucky draw.';

            if (error.responseJSON && error.responseJSON.errors) {
                const errors = error.responseJSON.errors;
                errorMessage = Object.values(errors).flat().join('<br>');
            } else if (error.responseJSON && error.responseJSON.message) {
                errorMessage = error.responseJSON.message;
            }

            this.showError(errorMessage);
        } finally {
            submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Lucky Draw');
        }
    }

    async loadLuckyDraws() {
        try {
            this.showLoading();

            const response = await $.ajax({
                url: `${this.baseUrl}/list`,
                type: 'GET',
                dataType: 'json'
            });

            // Check if response has data property (AJAX response structure)
            const draws = response.data || response;
            // console.log('Lucky Draws Loaded:', draws);

            if (!Array.isArray(draws)) {
                throw new Error('Invalid response format from server');
            }

            this.renderLuckyDraws(draws);

        } catch (error) {
            console.error('Error loading lucky draws:', error);
            this.showError('Failed to load lucky draws. Please try again.');

            // Show empty state on error
            $('#luckyDrawsGrid').hide();
            $('#emptyState').show();
            $('#loadingSpinner').hide();
        } finally {
            this.hideLoading();
        }
    }

    renderLuckyDraws(draws) {
        const grid = $('#luckyDrawsGrid');
        const emptyState = $('#emptyState');

        // Ensure draws is an array
        if (!Array.isArray(draws) || draws.length === 0) {
            grid.hide();
            emptyState.show();
            return;
        }

        emptyState.hide();

        try {
            const drawsHtml = draws.map(draw => this.createDrawCard(draw)).join('');
            grid.html(drawsHtml).show();
        } catch (error) {
            console.error('Error rendering lucky draws:', error);
            this.showError('Error displaying lucky draws. Please refresh the page.');
            grid.hide();
            emptyState.show();
        }
    }

    createDrawCard(draw) {
    // Add safety checks for draw properties
    if (!draw || typeof draw !== 'object') {
        console.error('Invalid draw object:', draw);
        return '';
    }

    const isActive = draw.status === 'active';
    const isCurrentlyActive = isActive &&
        new Date(draw.start_date) <= new Date() &&
        new Date(draw.end_date) >= new Date();

    // const drawDate = draw.draw_date ? new Date(draw.draw_date).toLocaleDateString('en-US', {
    //     year: 'numeric',
    //     month: 'short',
    //     day: 'numeric',
    //     hour: '2-digit',
    //     minute: '2-digit'
    // }) : 'Not set';
    console.log('Draw Date Raw:', draw.draw_date);
    const drawDate = draw.draw_date ? moment(draw.draw_date * 1000).format('MMM DD, YYYY hh:mm A') : 'Not set';

    // Safe prize handling
    let prizesHtml = '<div class="prize-item text-muted">No prizes defined</div>';
    if (draw.prizes && Array.isArray(draw.prizes)) {
        prizesHtml = draw.prizes.map((prize, index) => {
            const prizeType = prize.type || 'Prize';
            const prizeName = prize.name || 'Unknown';
            const prizeNumber = index + 1;
            const prizeIcons = ['🥇', '🥈', '🥉', '🎁', '🏆'];
            const prizeIcon = prizeIcons[index] || prizeIcons[prizeIcons.length - 1];

            return `
            <div class="prize-item d-flex align-items-center">
                <span class="prize-icon mr-2">${prizeIcon}</span>
                <div class="flex-grow-1">
                    <strong class="d-block">${prizeType}</strong>
                    <small class="text-muted">${prizeName}</small>
                </div>
            </div>
        `;
        }).join('');
    }

    // Safe property access with fallbacks
    const title = draw.title || 'Untitled Draw';
    const description = draw.description || 'No description available.';
    const startDate = draw.start_date ? new Date(draw.start_date).toLocaleDateString() : 'Not set';
    const endDate = draw.end_date ? new Date(draw.end_date).toLocaleDateString() : 'Not set';
    const maxEntries = draw.max_entries_per_customer || 1;
    const entriesCount = draw.entries_count || 0;
    const winnersCount = draw.winners_count || 0;

    // Calculate completion percentage for progress bar
    const totalPrizes = draw.prizes && Array.isArray(draw.prizes) ? draw.prizes.length : 0;
    const completionPercentage = totalPrizes > 0 ? Math.round((winnersCount / totalPrizes) * 100) : 0;

    // Create prize buttons for spinning
    const prizeButtonsHtml = draw.prizes && Array.isArray(draw.prizes)
        ? draw.prizes.map((prize, index) => {
            const prizeType = prize.type || 'Prize';
            const prizeIcons = ['🥇', '🥈', '🥉', '🎁', '🏆'];
            const prizeIcon = prizeIcons[index] || prizeIcons[prizeIcons.length - 1];
            const isPrizeWon = draw.winners && draw.winners.some(winner => winner.prize_won === prizeType);

            return `
            <button type="button" class="btn ${isPrizeWon ? 'btn-secondary' : 'btn-success'} btn-sm spin-prize-draw mr-1 mb-1"
                    data-draw-id="${draw.id}"
                    data-draw-title="${title}"
                    data-prize-type="${prizeType}"
                    ${isPrizeWon ? 'disabled title="This prize has already been won"' : ''}>
                <span class="mr-1">${prizeIcon}</span>
                <i class="fas fa-sync-alt"></i> ${prizeType}
                ${isPrizeWon ? '<i class="fas fa-check ml-1"></i>' : ''}
            </button>
        `;
        }).join('')
        : '';

    // Status badge with additional info
    let statusBadgeHtml = '';
    if (isCurrentlyActive) {
        statusBadgeHtml = `<span class="status-badge status-active"><i class="fas fa-play-circle mr-1"></i> Live</span>`;
    } else if (isActive && new Date(draw.start_date) > new Date()) {
        statusBadgeHtml = `<span class="status-badge status-upcoming"><i class="fas fa-clock mr-1"></i> Upcoming</span>`;
    } else if (isActive && new Date(draw.end_date) < new Date()) {
        statusBadgeHtml = `<span class="status-badge status-expired"><i class="fas fa-calendar-times mr-1"></i> Expired</span>`;
    } else {
        statusBadgeHtml = `<span class="status-badge status-inactive"><i class="fas fa-pause-circle mr-1"></i> Inactive</span>`;
    }

    return `
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card lucky-draw-card h-100">
            <div class="card-header card-header-custom position-relative">
                <h5 class="card-title mb-0 text-truncate" title="${title}">${title}</h5>
                ${statusBadgeHtml}
            </div>

            <div class="card-body">
                <!-- Description -->
                <p class="card-text description-text">${description}</p>

                <!-- Progress Bar for Prize Completion -->
                ${totalPrizes > 0 ? `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Progress</small>
                        <small class="text-muted">${winnersCount}/${totalPrizes} prizes won</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar ${completionPercentage === 100 ? 'bg-success' : 'bg-info'}"
                             role="progressbar"
                             style="width: ${completionPercentage}%"
                             aria-valuenow="${completionPercentage}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- Key Information Grid -->
                <div class="info-grid mb-3">
                    <div class="info-item">
                        <i class="fas fa-calendar-alt text-primary"></i>
                        <div>
                            <small class="text-muted d-block">Duration</small>
                            <div class="font-weight-bold">${startDate} - ${endDate}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-ticket-alt text-success"></i>
                        <div>
                            <small class="text-muted d-block">Max Entries</small>
                            <div class="font-weight-bold">${maxEntries} per customer</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-users text-info"></i>
                        <div>
                            <small class="text-muted d-block">Participants</small>
                            <div>
                                <span class="badge badge-primary badge-pill">${entriesCount} entries</span>
                                <span class="badge badge-success badge-pill ml-1">${winnersCount} winners</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fas fa-trophy text-warning"></i>
                        <div>
                            <small class="text-muted d-block">Draw Date</small>
                            <div class="font-weight-bold">${drawDate}</div>
                        </div>
                    </div>
                </div>

                <!-- Prizes Section -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted font-weight-bold">PRIZES</small>
                        <span class="badge badge-light">${totalPrizes} total</span>
                    </div>
                    <div class="prizes-container">
                        ${prizesHtml}
                    </div>
                </div>
            </div>

            <!-- Card Footer with Actions -->
            <div class="card-footer card-footer-custom">
                <!-- Primary Actions -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button type="button" class="btn btn-info btn-sm view-participants"
                            data-draw-id="${draw.id}"
                            data-draw-title="${title}">
                        <i class="fas fa-users mr-1"></i> Participants
                    </button>

                    <form action="${this.baseUrl}/toggle-status/${draw.id}" method="POST" class="d-inline toggle-status-form">
                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                        <button type="submit" class="btn ${isActive ? 'btn-warning' : 'btn-success'} btn-sm"
                                title="${isActive ? 'Deactivate this draw' : 'Activate this draw'}">
                            <i class="fas ${isActive ? 'fa-pause' : 'fa-play'} mr-1"></i>
                            ${isActive ? 'Deactivate' : 'Activate'}
                        </button>
                    </form>
                </div>

                <!-- Spin Buttons (Only for active draws) -->
                ${isActive && prizeButtonsHtml ? `
                <div class="prize-buttons-section">
                    <hr class="my-2">
                    <small class="text-muted font-weight-bold d-block mb-2">SPIN FOR PRIZES</small>
                    <div class="d-flex flex-wrap justify-content-center">
                        ${prizeButtonsHtml}
                    </div>
                </div>
                ` : ''}

                <!-- Additional Info for Inactive Draws -->
                ${!isActive ? `
                <div class="text-center mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Activate this draw to start spinning for prizes
                    </small>
                </div>
                ` : ''}
            </div>
        </div>
    </div>
    `;
}

    async viewParticipants(event) {
        const button = $(event.currentTarget);
        const drawId = button.data('draw-id');
        const drawTitle = button.data('draw-title');

        this.currentDrawId = drawId;
        $('#modalDrawTitle').text(drawTitle);

        try {
            this.showParticipantsLoading();
            $('#participantsModal').modal('show');

            const response = await $.ajax({
                url: `${this.baseUrl}/participants/${drawId}`,
                type: 'GET',
                dataType: 'json'
            });

            this.renderParticipants(response);

        } catch (error) {
            console.error('Error loading participants:', error);
            this.showParticipantsError('Failed to load participants. Please try again.');
        }
    }

    renderParticipants(data) {
        const { participants, total_entries, lucky_draw } = data;
        const totalParticipants = participants.length;
        const avgEntries = totalParticipants > 0 ? (total_entries / totalParticipants).toFixed(1) : 0;

        // Update summary
        $('#totalParticipants').text(totalParticipants);
        $('#totalEntries').text(total_entries);
        $('#avgEntries').text(avgEntries);

        // Render participants table
        const tbody = $('#participantsTable tbody');

        if (participants.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-users-slash fa-2x mb-2"></i><br>
                        No participants found for this lucky draw.
                    </td>
                </tr>
            `);
            return;
        }

        const rows = participants.map((participant, index) => {
            const wonPrizes = participant.won_prizes && participant.won_prizes.length > 0
                ? participant.won_prizes.join(', ')
                : 'None';

            const statusBadge = participant.is_winner
                ? '<span class="badge badge-warning"><i class="fas fa-trophy mr-1"></i> Winner</span>'
                : '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Active</span>';

            return `
                <tr>
                    <td>${index + 1}</td>
                    <td>
                        <i class="fas fa-user-circle mr-2 text-muted"></i>
                        ${participant.name}
                    </td>
                    <td>${participant.email}</td>
                    <td>
                        ${participant.entries}
                        <span class="entries-badge">${participant.entries} ticket${participant.entries > 1 ? 's' : ''}</span>
                    </td>
                    <td>
                        ${participant.probability}%
                        <span class="probability-badge">${participant.entries}/${total_entries}</span>
                    </td>
                    <td>
                        ${statusBadge}
                        ${wonPrizes !== 'None' ? `<br><small class="text-success">Won: ${wonPrizes}</small>` : ''}
                    </td>
                </tr>
            `;
        }).join('');

        tbody.html(rows);
    }

    async spinPrizeDraw(event) {
        const button = $(event.currentTarget);
        const drawId = button.data('draw-id');
        const drawTitle = button.data('draw-title');
        const prizeType = button.data('prize-type');

        this.currentDrawId = drawId;
        this.currentPrizeType = prizeType;

        $('#spinnerDrawTitle').text(`${drawTitle} - ${prizeType}`);

        try {
            // Load participants data for stats
            const response = await $.ajax({
                url: `${this.baseUrl}/participants/${drawId}`,
                type: 'GET',
                dataType: 'json'
            });

            $('#spinParticipantsCount').text(response.participants.length);
            $('#spinEntriesCount').text(response.total_entries);

            // Update prize selection UI
            this.updatePrizeSelection(response.available_prizes, prizeType);

            $('#spinnerModal').modal('show');
            $('#winnerResult').hide();
            $('#startSpin').show().prop('disabled', false);
            $('#spinnerWheel').css('transform', 'rotate(0deg)');

        } catch (error) {
            console.error('Error preparing spin:', error);
            this.showError('Failed to prepare spin. Please try again.');
        }
    }

    updatePrizeSelection(availablePrizes, selectedPrize) {
        const prizeSelection = $('#prizeSelection');

        if (!prizeSelection.length) {
            // Create prize selector if it doesn't exist
            $('.modal-body').prepend(`
                <div class="prize-selection mb-3">
                    <label class="font-weight-bold">Select Prize to Spin:</label>
                    <div class="btn-group btn-group-toggle d-flex flex-wrap" id="prizeSelection" data-toggle="buttons">
                        ${availablePrizes.map(prize => `
                            <label class="btn btn-outline-primary ${prize.type === selectedPrize ? 'active' : ''}
                                    mb-1 mr-1">
                                <input type="radio" name="prizeType" value="${prize.type}"
                                       ${prize.type === selectedPrize ? 'checked' : ''}

                                       >
                                ${prize.type}
                                ${!prize.is_available ? '<br><small class="text-danger">Already Won</small>' : ''}
                            </label>
                        `).join('')}
                    </div>
                </div>
            `);
        } else {
            // Update existing prize selector
            prizeSelection.html(availablePrizes.map(prize => `
                <label class="btn btn-outline-primary ${prize.type === selectedPrize ? 'active' : ''}
                        mb-1 mr-1">
                    <input type="radio" name="prizeType" value="${prize.type}"
                           ${prize.type === selectedPrize ? 'checked' : ''}
                           >
                    ${prize.type}
                    ${!prize.is_available ? '<br><small class="text-danger">Already Won</small>' : ''}
                </label>
            `).join(''));
        }
    }

    async startSpin() {
        const startSpinBtn = $('#startSpin');
        const spinnerWheel = $('#spinnerWheel');
        const winnerResult = $('#winnerResult');

        try {
            const prizeType = $('input[name="prizeType"]:checked').val();
            if (!prizeType) {
                throw new Error('Please select a prize to spin for.');
            }

            startSpinBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Spinning...');

            // Start spinner animation
            const rotations = 5;
            const segments = 8;
            const segmentAngle = 360 / segments;
            const randomSegment = Math.floor(Math.random() * segments);
            const finalRotation = (rotations * 360) + (randomSegment * segmentAngle);

            spinnerWheel.css('transform', `rotate(${finalRotation}deg)`);

            // Make API call to get winner for specific prize
            const response = await $.ajax({
                url: `${this.baseUrl}/spin/${this.currentDrawId}`,
                type: 'POST',
                dataType: 'json',
                data: {
                    prize_type: prizeType
                }
            });

            if (!response.success) {
                throw new Error(response.message);
            }

            // Show winner after spin completes
            setTimeout(() => {
                this.showWinner(response.winner, response.available_prizes);
                startSpinBtn.html('<i class="fas fa-redo mr-1"></i> Spin Again').prop('disabled', false);
            }, 4000);

        } catch (error) {
            console.error('Error spinning draw:', error);
            this.showSpinnerError(error.message || 'Failed to spin draw. Please try again.');
            startSpinBtn.html('<i class="fas fa-play mr-1"></i> Start Spin').prop('disabled', false);
        }
    }

    showWinner(winner, availablePrizes) {
        $('#winnerName').text(winner.name);
        $('#winnerEmail').text(winner.email);
        $('#winnerEntries').text(winner.entries);
        $('#winnerPrize').html(`<strong>${winner.prize_won}:</strong> ${winner.prize_name}`);
        $('#winnerResult').show();

        // Update prize selection with new availability
        this.updatePrizeSelection(availablePrizes, winner.prize_won);

        // Reload lucky draws to update winner counts
        this.loadLuckyDraws();
    }

    // Add this method to your LuckyDrawManager class
    async toggleStatus(event) {
        event.preventDefault();

        const form = $(event.target).closest('form');
        const button = form.find('button');
        const originalText = button.html();
        const drawId = form.attr('action').split('/').pop();

        if (!confirm('Are you sure you want to change the status of this lucky draw?')) {
            return;
        }

        try {
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            const response = await $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json'
            });

            if (response.success) {
                this.showSuccess(response.message);
                this.loadLuckyDraws(); // Refresh the list
            } else {
                throw new Error(response.message);
            }

        } catch (error) {
            console.error('Error toggling status:', error);
            this.showError(error.message || 'Failed to update status. Please try again.');
            button.html(originalText).prop('disabled', false);
        }
    }

    // Or if you prefer a separate deactivate method:
    async deactivateLuckyDraw(event) {
        event.preventDefault();

        const button = $(event.currentTarget);
        const drawId = button.data('draw-id');
        const drawTitle = button.data('draw-title');

        if (!confirm(`Are you sure you want to deactivate "${drawTitle}"?`)) {
            return;
        }

        try {
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deactivating...');

            const response = await $.ajax({
                url: `${this.baseUrl}/deactivate/${drawId}`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json'
            });

            if (response.success) {
                this.showSuccess(response.message);
                this.loadLuckyDraws(); // Refresh the list
            } else {
                throw new Error(response.message);
            }

        } catch (error) {
            console.error('Error deactivating lucky draw:', error);
            this.showError(error.message || 'Failed to deactivate lucky draw. Please try again.');
            button.prop('disabled', false).html('<i class="fas fa-power-off"></i> Deactivate');
        }
    }

    // Utility Methods
    showLoading() {
        $('#loadingSpinner').show();
        $('#luckyDrawsGrid').hide();
        $('#emptyState').hide();
    }

    hideLoading() {
        $('#loadingSpinner').hide();
    }

    showParticipantsLoading() {
        $('#participantsTable tbody').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary mr-2"></div>
                    Loading participants...
                </td>
            </tr>
        `);
    }

    showParticipantsError(message) {
        $('#participantsTable tbody').html(`
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${message}
                </td>
            </tr>
        `);
    }

    showSpinnerError(message) {
        $('#winnerResult').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${message}
            </div>
        `).show();
    }

    showError(message) {
        // Using Toastr or similar notification library would be better
        alert('Error: ' + message);
    }

    showSuccess(message) {
        // Using Toastr or similar notification library would be better
        alert('Success: ' + message);
    }

    clearParticipantsModal() {
        $('#participantsTable tbody').empty();
        $('#totalParticipants').text('0');
        $('#totalEntries').text('0');
        $('#avgEntries').text('0');
    }

    clearSpinnerModal() {
        $('#winnerResult').hide();
        $('#spinnerWheel').css('transform', 'rotate(0deg)');
        $('#prizeSelection').remove();
        this.currentDrawId = null;
        this.currentPrizeType = null;
    }

    clearCreateModal() {
        $('#createLuckyDrawForm')[0].reset();
        $('#prizesContainer').html(`
            <div class="prize-item row mb-2">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="prizes[0][type]"
                           placeholder="Prize Type (e.g., First Prize)" required>
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="prizes[0][name]"
                           placeholder="Prize Name (e.g., Honda Bike)" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-prize" disabled>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);
    }
}

// Initialize when document is ready
$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize Lucky Draw Manager
    window.luckyDrawManager = new LuckyDrawManager();

    // Handle create form submission
    $('#createLuckyDrawForm').on('submit', function (e) {
        e.preventDefault();
        window.luckyDrawManager.createLuckyDraw();
    });
});
