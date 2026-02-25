@php
    $user = auth()->user();
    $userNotifications = collect();
    $userNotificationCount = 0;
    if ($user) {
        try {
            $userNotifications = $user->unreadNotifications()->latest()->take(10)->get();
            $userNotificationCount = $user->unreadNotifications()->count();
        } catch (\Exception $e) {
            $userNotifications = collect();
            $userNotificationCount = 0;
        }
    }
@endphp

<!-- navbar-wrapper start -->
<nav class="navbar-wrapper bg--dark d-flex flex-wrap">
    <div class="navbar__left">
        <button type="button" class="res-sidebar-open-btn me-3"><i class="las la-bars"></i></button>
        <form class="navbar-search">
            <input type="search" name="#0" class="navbar-search-field" id="searchInput" autocomplete="off"
                placeholder="@lang('Search here...')">
            <i class="las la-search"></i>
            <ul class="search-list"></ul>
        </form>
    </div>
    <div class="navbar__right">
        <ul class="navbar__action-list">
            <li>
                <button type="button" class="primary--layer" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Visit Website')">
                    <a href="{{ route('home') }}" target="_blank"><i class="las la-globe"></i></a>
                </button>
            </li>
            <li>
                <button type="button" class="primary--layer" data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Marketplace')">
                    <a href="{{ route('marketplace.index') }}" target="_blank"><i class="las la-store"></i></a>
                </button>
            </li>
            @if($user)
            <li class="dropdown">
                <button type="button" class="primary--layer notification-bell" data-bs-toggle="dropdown" data-display="static"
                    aria-haspopup="true" aria-expanded="false">
                    <span data-bs-toggle="tooltip" data-bs-placement="bottom" title="@lang('Unread Notifications')">
                        <i class="las la-bell @if($userNotificationCount > 0) icon-left-right @endif"></i>
                    </span>
                    @if($userNotificationCount > 0)
                    <span class="notification-count">{{ $userNotificationCount <= 9 ? $userNotificationCount : '9+'}}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu--md p-0 border-0 box--shadow1 dropdown-menu-right">
                    <div class="dropdown-menu__header">
                        <span class="caption">@lang('Notification')</span>
                        @if($userNotificationCount > 0)
                            <p>@lang('You have') {{ $userNotificationCount }} @lang('unread notification')</p>
                        @endif
                    </div>
                    <div class="dropdown-menu__body @if(blank($userNotifications)) d-flex justify-content-center align-items-center @endif">
                        @forelse($userNotifications as $notification)
                            <a href="{{ $notification->data['click_url'] ?? '#' }}"
                                class="dropdown-menu__item"
                                id="notification-{{ $notification->id }}"
                                onclick="markNotificationAsRead('{{ $notification->id }}', event)">
                                <div class="navbar-notifi">
                                    <div class="navbar-notifi__right">
                                        <h6 class="notifi__title">{{ __($notification->data['title'] ?? 'Notification') }}</h6>
                                        <p class="notifi__message mb-1">{{ __($notification->data['message'] ?? '') }}</p>
                                        <span class="time"><i class="far fa-clock"></i>
                                            {{ diffForHumans($notification->created_at) }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                        <div class="empty-notification text-center">
                            <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty">
                            <p class="mt-3">@lang('No unread notification found')</p>
                        </div>
                        @endforelse
                    </div>
                    <div class="dropdown-menu__footer">
                        @if($userNotificationCount > 0)
                            <button type="button" class="btn btn-sm btn-outline--primary w-100 mb-2" onclick="markAllNotificationsAsRead()">
                                <i class="las la-check-double"></i> @lang('Mark All as Read')
                            </button>
                        @endif
                        <a href="{{ route('user.transactions') }}" class="view-all-message">@lang('View all notifications')</a>
                    </div>
                </div>
            </li>
            <li class="dropdown d-flex profile-dropdown">
                <button type="button" data-bs-toggle="dropdown" data-display="static" aria-haspopup="true"
                    aria-expanded="false">
                    <span class="navbar-user">
                        <span class="navbar-user__thumb" style="width: 35px; height: 35px; flex-shrink: 0; display: inline-block; overflow: hidden;">
                            @if($user->image && file_exists(getFilePath('userProfile').'/'. $user->image))
                                <img src="{{ getImage(getFilePath('userProfile').'/'. $user->image,getFileSize('userProfile'))}}" alt="image">
                            @else
                                @php
                                    $fullname = trim($user->firstname . ' ' . $user->lastname);
                                    if(empty($fullname) || $fullname == ' ') {
                                        $fullname = $user->username;
                                    }
                                    $words = array_filter(explode(' ', $fullname));
                                    $initials = '';
                                    if(count($words) >= 2) {
                                        $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
                                    } else {
                                        $name = trim($fullname);
                                        if(strlen($name) >= 2) {
                                            $initials = strtoupper(substr($name, 0, 2));
                                        } else {
                                            $initials = strtoupper(str_pad($name, 2, $name));
                                        }
                                    }
                                    $colors = ['#4bea76', '#4634ff', '#28c76f', '#ff9f43', '#ea5455', '#00cfe8', '#7367f0', '#ff6b6b', '#51cf66', '#339af0', '#f59f00', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899'];
                                    $hash = crc32($user->username . $user->id);
                                    $colorIndex = abs($hash) % count($colors);
                                    $bgColor = $colors[$colorIndex];
                                @endphp
                                <span class="user-initials" style="background-color: {{ $bgColor }}; color: #fff; width: 35px; height: 35px; min-width: 35px; min-height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: 600; font-size: 14px; text-transform: uppercase; flex-shrink: 0;">
                                    {{ $initials }}
                                </span>
                            @endif
                        </span>
                        <span class="navbar-user__info">
                            <span class="navbar-user__name">{{ $user->username }}</span>
                        </span>
                        <span class="icon"><i class="las la-chevron-circle-down"></i></span>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu--sm p-0 border-0 box--shadow1 dropdown-menu-right">
                    <a href="{{ route('user.profile.setting') }}"
                        class="dropdown-menu__item d-flex align-items-center px-3 py-2">
                        <i class="dropdown-menu__icon las la-user-circle"></i>
                        <span class="dropdown-menu__caption">@lang('Profile')</span>
                    </a>

                    <a href="{{ route('user.change.password') }}"
                        class="dropdown-menu__item d-flex align-items-center px-3 py-2">
                        <i class="dropdown-menu__icon las la-key"></i>
                        <span class="dropdown-menu__caption">@lang('Password')</span>
                    </a>

                    <a href="{{ route('user.logout') }}" class="dropdown-menu__item d-flex align-items-center px-3 py-2">
                        <i class="dropdown-menu__icon las la-sign-out-alt"></i>
                        <span class="dropdown-menu__caption">@lang('Logout')</span>
                    </a>
                </div>
                <button type="button" class="breadcrumb-nav-open ms-2 d-none">
                    <i class="las la-sliders-h"></i>
                </button>
            </li>
            @endif
        </ul>
    </div>
</nav>
<!-- navbar-wrapper end -->

@push('script')
<script>
    function markNotificationAsRead(notificationId, event) {
        if (event) {
            event.preventDefault();
        }
        
        fetch('{{ route("user.notification.read", ":id") }}'.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationElement = document.getElementById('notification-' + notificationId);
                if (notificationElement) {
                    notificationElement.remove();
                }
                updateNotificationCount();
                checkEmptyState();
                
                if (event && event.target.closest('a').href && event.target.closest('a').href !== '#') {
                    window.location.href = event.target.closest('a').href;
                }
            }
        })
        .catch(err => console.error('Error marking notification as read:', err));
    }
    
    function markAllNotificationsAsRead() {
        fetch('{{ route("user.notification.read.all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const notificationItems = document.querySelectorAll('.dropdown-menu__item[id^="notification-"]');
                notificationItems.forEach(item => item.remove());
                updateNotificationCount();
                checkEmptyState();
            } else {
                console.error('Failed to mark all as read:', data);
            }
        })
        .catch(err => {
            console.error('Error marking all notifications as read:', err);
        });
    }
    
    function updateNotificationCount() {
        const remainingNotifications = document.querySelectorAll('.dropdown-menu__item[id^="notification-"]').length;
        const countElement = document.querySelector('.notification-count');
        const bellIcon = document.querySelector('.notification-bell i');
        const headerText = document.querySelector('.dropdown-menu__header p');
        
        if (remainingNotifications === 0) {
            if (countElement) countElement.remove();
            if (bellIcon) bellIcon.classList.remove('icon-left-right');
            if (headerText) {
                headerText.textContent = '@lang("You have 0 unread notification")';
            }
        } else {
            if (countElement) {
                countElement.textContent = remainingNotifications <= 9 ? remainingNotifications : '9+';
            } else {
                const bell = document.querySelector('.notification-bell');
                if (bell) {
                    const span = document.createElement('span');
                    span.className = 'notification-count';
                    span.textContent = remainingNotifications <= 9 ? remainingNotifications : '9+';
                    bell.appendChild(span);
                }
            }
            if (bellIcon) bellIcon.classList.add('icon-left-right');
            if (headerText) {
                headerText.textContent = `@lang("You have") ${remainingNotifications} @lang("unread notification")`;
            }
        }
    }
    
    function checkEmptyState() {
        const body = document.querySelector('.dropdown-menu__body');
        const hasNotifications = document.querySelectorAll('.dropdown-menu__item[id^="notification-"]').length > 0;
        
        if (!hasNotifications && body) {
            if (!body.querySelector('.empty-notification')) {
                body.innerHTML = `
                    <div class="empty-notification text-center">
                        <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty">
                        <p class="mt-3">@lang('No unread notification found')</p>
                    </div>
                `;
            }
        }
    }
</script>
@endpush

