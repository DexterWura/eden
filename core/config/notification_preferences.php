<?php

return [
    'always_send' => [
        'PASS_RESET_CODE',
        'PASS_RESET_DONE',
        'EVER_CODE',
        'SVER_CODE',
    ],

    'types' => [
        'STARTUP_APPROVED' => ['label' => 'App approval and publishing updates', 'category' => 'My apps'],
        'STARTUP_AWARD' => ['label' => 'Product of the day, month, and year awards', 'category' => 'My apps'],
        'STARTUP_COMMENT' => ['label' => 'New comments on apps I manage', 'category' => 'Engagement'],
        'HERO_UPDATES' => ['label' => 'Hero feature request decisions', 'category' => 'My apps'],
        'COFOUNDER_UPDATES' => ['label' => 'Co-founder invitations and access changes', 'category' => 'My apps'],
        'FUNDRAISING_OPPORTUNITIES' => ['label' => 'Investment opportunities from other founders', 'category' => 'Discovery'],
        'LAUNCH_UPDATES' => ['label' => 'Launch reminders I subscribed to', 'category' => 'Discovery'],
    ],
];
