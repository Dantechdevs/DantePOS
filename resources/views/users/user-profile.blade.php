@extends('layouts.layout')
@section('title', '| User Profile')
@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left col - Profile Info -->
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                    src="{{asset('images/avatar5.png')}}"
                                    alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center">{{ $user['name'] }}</h3>
                            <p class="text-muted text-center">{{ ucfirst($user['role'] ?? 'User') }}</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Email</b> <a class="float-right">{{ $user['email'] }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Mobile</b> <a class="float-right">{{ $user['mobile'] ?? 'N/A' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Gender</b> <a class="float-right">{{ $user['gender'] ?? 'N/A' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Role</b> <a class="float-right">{{ $user['group']['name'] ?? 'N/A' }}</a>
                                </li>
                            </ul>

                            <a href="{{ route('edit.user.profile',$user['id']) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-edit mr-2"></i><b>Edit Profile</b>
                            </a>
                        </div>
                        <!-- /.card-body -->
                    </div>

                    <!-- About Me Box -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">About Me</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                            <p class="text-muted">{{ $user['address'] ?? 'No address provided' }}</p>
                            <hr>

                            <strong><i class="far fa-calendar-alt mr-1"></i> Member Since</strong>
                            <p class="text-muted">{{ \Carbon\Carbon::parse($user['created_at'])->format('M d, Y') }}</p>
                            <hr>

                            <strong><i class="far fa-clock mr-1"></i> Last Updated</strong>
                            <p class="text-muted">{{ \Carbon\Carbon::parse($user['updated_at'])->format('M d, Y h:i') }}</p>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->

                <!-- Right col - Activity Logs -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity Log</a></li>
                                <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Login History</a></li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Activity Tab -->
                                <div class="tab-pane active" id="activity">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <input type="text" id="activitySearch" class="form-control" placeholder="Search activities...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-default" type="button" id="searchButton">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                    <button class="btn btn-default" type="button" id="clearSearch" title="Clear search">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default btn-sm" id="sortButton">
                                                    <i class="fas fa-sort-amount-down"></i> Sort
                                                </button>
                                                <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon" data-toggle="dropdown">
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <div class="dropdown-menu" role="menu">
                                                    <a class="dropdown-item sort-option" href="#" data-sort="newest">Newest first</a>
                                                    <a class="dropdown-item sort-option" href="#" data-sort="oldest">Oldest first</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="activityResults">
                                        @include('users.partials.activity-list', ['activityLogs' => $activityLogs])
                                    </div>
                                </div>
                                <!-- /.tab-pane -->

                                <!-- Timeline Tab -->
                                <div class="tab-pane" id="timeline">
                                    <div class="timeline timeline-inverse">
                                        @if($loginActivities && count($loginActivities) > 0)
                                            @foreach($loginActivities as $login)
                                            <div class="time-label">
                                                <span class="bg-success">{{ $login->created_at->format('M d, Y') }}</span>
                                            </div>

                                            <div>
                                                <i class="fas fa-sign-in-alt bg-primary"></i>
                                                <div class="timeline-item">
                                                    <span class="time"><i class="far fa-clock"></i> {{ $login->created_at->format('h:i') }}</span>
                                                    <h3 class="timeline-header no-border">Logged in successfully</h3>
                                                    <div class="timeline-body">
                                                        IP Address: {{ $login->ip_address }}<br>
                                                        Browser: {{ Str::limit($login->user_agent, 80) }}
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="text-center p-4">
                                                <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No login history available.</p>
                                            </div>
                                        @endif

                                        <div>
                                            <i class="far fa-clock bg-gray"></i>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<style>
    .profile-user-img {
        height: 100px;
        width: 100px;
        object-fit: cover;
    }
    .activity-item {
        transition: all 0.3s;
    }
    .activity-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    .timeline::before {
        background-color: #dee2e6;
    }
    .change-details table tr td {
        padding: 0.25rem 0.5rem;
    }
    .change-details .old-value {
        color: #dc3545;
        text-decoration: line-through;
    }
    .change-details .new-value {
        color: #28a745;
        font-weight: 500;
    }
    .highlight {
        background-color: #ffeb3b;
        padding: 2px 4px;
        border-radius: 3px;
    }
    #noResults {
        display: none;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentSort = 'newest';
    let currentSearch = '';

    // Search functionality
    $('#activitySearch').on('keyup', function() {
        currentSearch = $(this).val().toLowerCase();
        filterActivities();
    });

    $('#searchButton').on('click', function() {
        currentSearch = $('#activitySearch').val().toLowerCase();
        filterActivities();
    });

    $('#clearSearch').on('click', function() {
        $('#activitySearch').val('');
        currentSearch = '';
        filterActivities();
    });

    // Sort functionality
    $('.sort-option').on('click', function(e) {
        e.preventDefault();
        currentSort = $(this).data('sort');
        $('#sortButton').html('<i class="fas fa-sort-amount-' +
            (currentSort === 'newest' ? 'down' : 'up') + '"></i> ' +
            (currentSort === 'newest' ? 'Newest first' : 'Oldest first'));
        filterActivities();
    });

    function filterActivities() {
        const searchTerm = currentSearch.trim();
        const $activities = $('.activity-item');
        let hasResults = false;

        $activities.each(function() {
            const $activity = $(this);
            const text = $activity.text().toLowerCase();

            if (searchTerm === '' || text.includes(searchTerm)) {
                $activity.show();
                hasResults = true;

                // Highlight search terms
                if (searchTerm !== '') {
                    highlightText($activity, searchTerm);
                } else {
                    removeHighlights($activity);
                }
            } else {
                $activity.hide();
                removeHighlights($activity);
            }
        });

        // Sort activities
        const $container = $('.activity-feed');
        const $visibleActivities = $activities.filter(':visible');

        if (currentSort === 'newest') {
            $visibleActivities.sort(function(a, b) {
                return new Date($(b).find('small.text-muted').data('timestamp')) -
                       new Date($(a).find('small.text-muted').data('timestamp'));
            });
        } else {
            $visibleActivities.sort(function(a, b) {
                return new Date($(a).find('small.text-muted').data('timestamp')) -
                       new Date($(b).find('small.text-muted').data('timestamp'));
            });
        }

        $visibleActivities.detach().appendTo($container);

        // Show/hide no results message
        if (!hasResults && searchTerm !== '') {
            $('#noResults').show();
        } else {
            $('#noResults').hide();
        }
    }

    function highlightText($element, searchTerm) {
        removeHighlights($element);

        $element.contents().each(function() {
            if (this.nodeType === 3) { // Text node
                const text = $(this).text();
                const regex = new RegExp('(' + searchTerm + ')', 'gi');
                const newText = text.replace(regex, '<span class="highlight">$1</span>');

                if (newText !== text) {
                    $(this).replaceWith(newText);
                }
            } else if (this.nodeType === 1 && !$(this).hasClass('highlight')) { // Element node
                highlightText($(this), searchTerm);
            }
        });
    }

    function removeHighlights($element) {
        $element.find('.highlight').each(function() {
            $(this).replaceWith($(this).text());
        });
    }

    // Initialize timestamp data
    $('.activity-item small.text-muted').each(function() {
        const timeText = $(this).text();
        const timestamp = convertRelativeTimeToDate(timeText);
        $(this).data('timestamp', timestamp);
    });

    function convertRelativeTimeToDate(relativeTime) {
        const now = new Date();
        const matches = relativeTime.match(/(\d+)\s+(second|minute|hour|day|week|month|year)s? ago/);

        if (matches) {
            const amount = parseInt(matches[1]);
            const unit = matches[2];

            switch(unit) {
                case 'second': now.setSeconds(now.getSeconds() - amount); break;
                case 'minute': now.setMinutes(now.getMinutes() - amount); break;
                case 'hour': now.setHours(now.getHours() - amount); break;
                case 'day': now.setDate(now.getDate() - amount); break;
                case 'week': now.setDate(now.getDate() - (amount * 7)); break;
                case 'month': now.setMonth(now.getMonth() - amount); break;
                case 'year': now.setFullYear(now.getFullYear() - amount); break;
            }

            return now.toISOString();
        }

        // If it's not a relative time string, return current time
        return now.toISOString();
    }
});
</script>

@endsection
