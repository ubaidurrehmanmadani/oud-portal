<?php

return [
    'title' => 'User Role', 'can' => 'What you can do', 'cannot' => 'What you cannot do', 'close' => 'Close user role',
    'note' => 'Access follows your account role and current department or property assignments.',
    'landlord' => [
        'can' => ['View your assigned properties and their published financial reports, charts and documents.', 'Switch properties and reporting months, inspect detailed figures and download available files.', 'Approve or reject pending requests for your properties with an optional comment.'],
        'cannot' => ['Access unassigned properties, other landlords’ private records or staff departments.', 'Enter financial figures, publish reports or view unpublished reports.', 'Manage users, assignments or system settings.'],
    ],
    'employee' => [
        'can' => ['View published staff content and content assigned to your department.', 'Search documents, announcements and Oud Academy materials.', 'Open and download available documents and training files.'],
        'cannot' => ['Upload or edit workspace content.', 'Access other departments’ restricted content or landlord property records.', 'Manage users, assignments or system settings.'],
    ],
    'department_manager' => [
        'can' => ['View and search published staff content available to your department.', 'Create, edit and remove documents, training and announcements in your assigned department.', 'Publish department content and replace its attached files.', 'With explicit Admin permission, upload monthly Excel/PDF reports for assigned properties, save drafts and submit them for review.'],
        'cannot' => ['Manage content without a department assignment or outside your department.', 'Manage user accounts or property assignments, access landlord records or publish landlord reports.', 'Edit submitted reports or change system settings or role restrictions.'],
    ],
    'admin' => [
        'can' => ['Create and edit users, roles assigned to users, departments and properties.', 'Assign landlords to properties and users to departments.', 'Manage workspace content, enter monthly financial figures and publish reports.', 'Inspect stored permissions, login activity and integration status.'],
        'cannot' => ['Record landlord approval decisions on a landlord’s behalf.', 'Edit approval requests after a final decision.', 'Use notification delivery or external synchronization before those services are configured.'],
    ],
];
